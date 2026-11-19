<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Cart\Services\CartService;
use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Services\InventoryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ProductShow extends Component
{
    public Product $product;

    public ?int $variantId = null;

    public int $quantity = 1;

    public function mount(Product $product, InventoryService $inventory): void
    {
        if (! $product->isPublished()) {
            abort(404);
        }

        $this->product = $product->load(['brand', 'images', 'variants' => fn ($q) => $q->where('is_active', true)]);
        $this->quantity = max(1, (int) $product->min_order_qty);

        $activeVariants = $this->product->variants->where('is_active', true);
        if ($activeVariants->isNotEmpty()) {
            $this->variantId = (int) $activeVariants->first()->id;
        }
    }

    public function updatedVariantId(InventoryService $inventory): void
    {
        $this->syncQuantityToStock($inventory);
    }

    public function addToCart(CartService $cart, InventoryService $inventory): void
    {
        $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'quantity.min' => 'حداقل تعداد ۱ است.',
        ]);

        try {
            $cart->addItem((int) $this->product->id, $this->quantity, $this->variantId);
            $this->dispatch('toast', message: 'محصول به سبد خرید اضافه شد.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function getDisplayPriceProperty(): int
    {
        if ($this->variantId) {
            $variant = $this->product->variants->firstWhere('id', $this->variantId);
            if ($variant) {
                return $variant->finalPrice();
            }
        }

        return $this->product->finalPrice();
    }

    public function getListPriceProperty(): int
    {
        if ($this->variantId) {
            $variant = $this->product->variants->firstWhere('id', $this->variantId);
            if ($variant && $variant->discount_price !== null) {
                return (int) $variant->selling_price;
            }
        }

        if ($this->product->hasDiscount()) {
            return (int) $this->product->selling_price;
        }

        return $this->displayPrice;
    }

    public function render(InventoryService $inventory)
    {
        $title = ($this->product->seo_title ?: $this->product->name).' | آله مارکت';
        $description = $this->product->seo_description
            ?: ($this->product->short_description ?: mb_substr(strip_tags((string) $this->product->description), 0, 160));

        $available = $inventory->availableQuantity((int) $this->product->id, $this->variantId);

        return view('livewire.catalog.product-show', [
            'available' => $available,
        ])->layout('layouts.storefront', [
            'title' => $title,
            'metaDescription' => $description,
        ]);
    }

    private function syncQuantityToStock(InventoryService $inventory): void
    {
        $available = $inventory->availableQuantity((int) $this->product->id, $this->variantId);
        if ($available > 0 && $this->quantity > $available) {
            $this->quantity = $available;
        }
        $this->quantity = max((int) $this->product->min_order_qty, $this->quantity);
    }
}