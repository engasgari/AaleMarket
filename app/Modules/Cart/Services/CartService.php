<?php

namespace App\Modules\Cart\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CartService
{
    public function __construct(
        private InventoryService $inventory,
    ) {}

    public function resolveCart(): Cart
    {
        if (Auth::check()) {
            return $this->getOrCreateForUser(Auth::user());
        }

        $sessionId = session()->getId();

        return Cart::query()->firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null],
            []
        );
    }

    public function getOrCreateForUser(User $user): Cart
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null],
        );

        $this->mergeSessionCartIntoUserCart($cart);

        return $cart;
    }

    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null, ?Cart $cart = null): CartItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'تعداد باید حداقل ۱ باشد.',
            ]);
        }

        $cart ??= $this->resolveCart();

        /** @var Product $product */
        $product = Product::query()->whereKey($productId)->firstOrFail();

        if (! $product->isPublished()) {
            throw ValidationException::withMessages([
                'product' => 'این محصول قابل خرید نیست.',
            ]);
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey($variantId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $minQty = max(1, (int) $product->min_order_qty);
        if ($quantity < $minQty) {
            throw ValidationException::withMessages([
                'quantity' => "حداقل سفارش این محصول {$minQty} عدد است.",
            ]);
        }

        $available = $this->inventory->availableQuantity(
            (int) $product->id,
            $variant?->id,
            (int) $this->inventory->defaultWarehouse()->id,
        );

        $itemKey = CartItem::buildItemKey((int) $cart->id, (int) $product->id, $variant?->id);
        $existing = CartItem::query()->where('cart_id', $cart->id)->where('item_key', $itemKey)->first();
        $newQty = ($existing?->quantity ?? 0) + $quantity;

        if ($available < $newQty) {
            throw ValidationException::withMessages([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        $unitPrice = $variant ? $variant->finalPrice() : $product->finalPrice();

        if ($existing) {
            $existing->forceFill([
                'quantity' => $newQty,
                'unit_price' => $unitPrice,
            ])->save();

            return $existing->fresh(['product', 'productVariant']);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'item_key' => $itemKey,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function updateItem(int $cartItemId, int $quantity, ?Cart $cart = null): void
    {
        $cart ??= $this->resolveCart();
        $item = CartItem::query()->where('cart_id', $cart->id)->whereKey($cartItemId)->firstOrFail();

        if ($quantity < 1) {
            $item->delete();

            return;
        }

        $product = $item->product;
        $minQty = max(1, (int) ($product?->min_order_qty ?? 1));
        if ($quantity < $minQty) {
            throw ValidationException::withMessages([
                'quantity' => "حداقل سفارش این محصول {$minQty} عدد است.",
            ]);
        }

        $available = $this->inventory->availableQuantity(
            (int) $item->product_id,
            $item->product_variant_id ? (int) $item->product_variant_id : null,
            (int) $this->inventory->defaultWarehouse()->id,
        );

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        $item->forceFill([
            'quantity' => $quantity,
            'unit_price' => $this->resolveUnitPrice($item),
        ])->save();
    }

    public function removeItem(int $cartItemId, ?Cart $cart = null): void
    {
        $cart ??= $this->resolveCart();
        CartItem::query()->where('cart_id', $cart->id)->whereKey($cartItemId)->delete();
    }

    /** @return Collection<int, CartItem> */
    public function items(?Cart $cart = null): Collection
    {
        $cart ??= $this->resolveCart();

        return $cart->items()->with(['product.brand', 'productVariant'])->get();
    }

    public function subtotal(?Cart $cart = null): int
    {
        $cart ??= $this->resolveCart();

        return (int) $this->items($cart)->sum(fn (CartItem $item) => $item->lineTotal());
    }

    public function isEmpty(?Cart $cart = null): bool
    {
        $cart ??= $this->resolveCart();

        return $this->items($cart)->isEmpty();
    }

    public function clearItems(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->forceFill(['coupon_code' => null])->save();
    }

    /** @return array<int, string> */
    public function validateStock(?Cart $cart = null): array
    {
        $cart ??= $this->resolveCart();
        $errors = [];
        $warehouseId = (int) $this->inventory->defaultWarehouse()->id;

        foreach ($this->items($cart) as $item) {
            $product = $item->product;
            if (! $product || ! $product->isPublished()) {
                $errors[$item->id] = 'این محصول دیگر در دسترس نیست.';

                continue;
            }

            $available = $this->inventory->availableQuantity(
                (int) $item->product_id,
                $item->product_variant_id ? (int) $item->product_variant_id : null,
                $warehouseId,
            );

            if ($available < (int) $item->quantity) {
                $errors[$item->id] = 'موجودی کافی نیست (موجود: '.$available.').';
            }
        }

        return $errors;
    }

    public function assertHasStock(Cart $cart): void
    {
        $errors = $this->validateStock($cart);
        if ($errors !== []) {
            throw ValidationException::withMessages([
                'cart' => reset($errors),
            ]);
        }
    }

    public function refreshLinePrices(Cart $cart): void
    {
        foreach ($this->items($cart) as $item) {
            $price = $this->resolveUnitPrice($item);
            if ($price !== (int) $item->unit_price) {
                $item->forceFill(['unit_price' => $price])->save();
            }
        }
    }

    public function mergeGuestCartBySessionId(User $user, string $guestSessionId): Cart
    {
        $userCart = Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null],
        );

        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $guestSessionId)
            ->where('id', '!=', $userCart->id)
            ->first();

        if ($guestCart) {
            $this->mergeCartItems($guestCart, $userCart);
        }

        return $userCart;
    }

    private function mergeSessionCartIntoUserCart(Cart $userCart): void
    {
        $sessionId = session()->getId();
        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $sessionId)
            ->where('id', '!=', $userCart->id)
            ->first();

        if (! $guestCart) {
            return;
        }

        $this->mergeCartItems($guestCart, $userCart);
    }

    private function mergeCartItems(Cart $guestCart, Cart $userCart): void
    {
        foreach ($guestCart->items as $guestItem) {
            try {
                $this->addItem(
                    (int) $guestItem->product_id,
                    (int) $guestItem->quantity,
                    $guestItem->product_variant_id ? (int) $guestItem->product_variant_id : null,
                    $userCart
                );
            } catch (\Throwable) {
                // Skip unavailable guest items during merge.
            }
        }

        $guestCart->items()->delete();
        $guestCart->delete();
    }

    private function resolveUnitPrice(CartItem $item): int
    {
        if ($item->product_variant_id) {
            $variant = $item->productVariant ?? ProductVariant::query()->find($item->product_variant_id);
            if ($variant) {
                return $variant->finalPrice();
            }
        }

        $product = $item->product ?? Product::query()->find($item->product_id);
        if (! $product) {
            throw new RuntimeException('محصول یافت نشد.');
        }

        return $product->finalPrice();
    }
}
