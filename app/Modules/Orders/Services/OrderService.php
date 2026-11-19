<?php

namespace App\Modules\Orders\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Services\CartService;
use App\Modules\Customers\Models\CustomerAddress;
use App\Modules\Discounts\Models\CouponUsage;
use App\Modules\Discounts\Services\CouponService;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Orders\Enums\OrderStatus;
use App\Modules\Orders\Enums\PaymentStatus as OrderPaymentStatus;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderAddress;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Modules\Shipping\Services\ShippingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private CouponService $couponService,
        private ShippingService $shippingService,
        private InventoryService $inventory,
    ) {}

    /**
     * @param  array{
     *     customer_address_id?: int|null,
     *     recipient_name?: string,
     *     recipient_mobile?: string,
     *     province?: string,
     *     city?: string,
     *     postal_code?: string,
     *     address?: string,
     *     shipping_method_id: int,
     *     coupon_code?: string|null,
     *     notes?: string|null,
     *     client_token?: string|null,
     * }  $input
     */
    public function createFromCart(User $user, array $input): Order
    {
        $lock = Cache::lock('order:create:user:'.$user->id, 10);

        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'order' => 'لطفاً چند ثانیه صبر کنید و دوباره تلاش کنید.',
            ]);
        }

        try {
            if (! empty($input['client_token']) && Schema::hasColumn('orders', 'client_token')) {
                $existing = Order::query()
                    ->where('user_id', $user->id)
                    ->where('client_token', $input['client_token'])
                    ->first();

                if ($existing) {
                    return $existing->load(['items', 'address']);
                }
            }

            return DB::transaction(function () use ($user, $input) {
                $cart = $this->cartService->getOrCreateForUser($user);
                $cart->load(['items.product', 'items.productVariant']);

                if ($this->cartService->isEmpty($cart)) {
                    throw ValidationException::withMessages([
                        'cart' => 'سبد خرید خالی است.',
                    ]);
                }

                $this->cartService->refreshLinePrices($cart);
                $cart->refresh()->load(['items.product', 'items.productVariant']);
                $this->cartService->assertHasStock($cart);

                $subtotal = $this->cartService->subtotal($cart);
                $minOrder = (int) config('aale.min_order_amount', 0);

                if ($minOrder > 0 && $subtotal < $minOrder) {
                    throw ValidationException::withMessages([
                        'cart' => 'حداقل مبلغ سفارش '.number_format($minOrder).' '.config('aale.currency_label').' است.',
                    ]);
                }

                $discountAmount = 0;
                $coupon = null;
                $couponCode = isset($input['coupon_code']) ? trim((string) $input['coupon_code']) : null;

                if ($couponCode !== null && $couponCode !== '') {
                    $couponResult = $this->couponService->validateAndCalculate(
                        $couponCode,
                        $user,
                        $subtotal,
                        $cart->items,
                    );
                    $coupon = $couponResult['coupon'];
                    $discountAmount = $couponResult['discount_amount'];
                }

                $afterDiscount = max(0, $subtotal - $discountAmount);
                $shippingQuote = $this->shippingService->quote((int) $input['shipping_method_id'], $afterDiscount);
                $shippingAmount = $shippingQuote['price'];
                $totalAmount = $afterDiscount + $shippingAmount;

                $addressData = $this->resolveAddress($user, $input);

                $orderData = [
                    'order_number' => $this->generateOrderNumber(),
                    'user_id' => $user->id,
                    'status' => OrderStatus::PendingPayment,
                    'payment_status' => OrderPaymentStatus::Pending,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'shipping_amount' => $shippingAmount,
                    'total_amount' => $totalAmount,
                    'currency' => config('aale.currency', 'IRR'),
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'shipping_method_id' => $shippingQuote['method']->id,
                    'shipping_method_name' => $shippingQuote['method']->name,
                    'notes' => $input['notes'] ?? null,
                ];

                if (! empty($input['client_token']) && Schema::hasColumn('orders', 'client_token')) {
                    $orderData['client_token'] = $input['client_token'];
                }

                $order = Order::query()->create($orderData);

                foreach ($cart->items as $cartItem) {
                    $product = $cartItem->product;
                    $variant = $cartItem->productVariant;

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_variant_id' => $cartItem->product_variant_id,
                        'product_name' => $product?->name ?? 'محصول',
                        'product_sku' => $variant?->sku ?? $product?->sku ?? '',
                        'variant_name' => $variant?->name,
                        'quantity' => (int) $cartItem->quantity,
                        'unit_price' => (int) $cartItem->unit_price,
                        'discount_amount' => 0,
                        'total_price' => $cartItem->lineTotal(),
                        'product_snapshot' => [
                            'product_id' => $product?->id,
                            'name' => $product?->name,
                            'sku' => $product?->sku,
                            'variant' => $variant?->name,
                        ],
                    ]);
                }

                OrderAddress::query()->create([
                    'order_id' => $order->id,
                    ...$addressData,
                ]);

                $warehouse = $this->inventory->defaultWarehouse();

                foreach ($cart->items as $cartItem) {
                    $this->inventory->reserve(
                        (int) $warehouse->id,
                        (int) $cartItem->product_id,
                        (int) $cartItem->quantity,
                        $cartItem->product_variant_id ? (int) $cartItem->product_variant_id : null,
                        null,
                        (int) $order->id,
                    );
                }

                if ($coupon) {
                    CouponUsage::query()->create([
                        'coupon_id' => $coupon->id,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'discount_amount' => $discountAmount,
                        'created_at' => now(),
                    ]);
                }

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => OrderStatus::PendingPayment->value,
                    'note' => 'ثبت سفارش',
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]);

                $this->cartService->clearItems($cart);

                return $order->load(['items', 'address']);
            });
        } finally {
            $lock->release();
        }
    }

    public function markPaid(Order $order, ?int $userId = null, ?string $note = null): void
    {
        if ($order->status === OrderStatus::Paid) {
            return;
        }

        $fromStatus = $order->status;
        $order->forceFill([
            'status' => OrderStatus::Paid,
            'payment_status' => OrderPaymentStatus::Paid,
            'paid_at' => now(),
        ])->save();

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => $fromStatus?->value,
            'to_status' => OrderStatus::Paid->value,
            'note' => $note ?? 'پرداخت موفق',
            'user_id' => $userId,
            'created_at' => now(),
        ]);

        $order->load('stockReservations');
        foreach ($order->stockReservations()->where('status', 'active')->get() as $reservation) {
            $this->inventory->consumeReservation($reservation);
        }
    }

    public function markPaymentFailed(Order $order, ?int $userId = null, ?string $note = null): void
    {
        if ($order->payment_status === OrderPaymentStatus::Paid) {
            return;
        }

        $fromStatus = $order->status;
        $order->forceFill([
            'payment_status' => OrderPaymentStatus::Failed,
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => $fromStatus?->value,
            'to_status' => OrderStatus::Cancelled->value,
            'note' => $note ?? 'پرداخت ناموفق',
            'user_id' => $userId,
            'created_at' => now(),
        ]);

        $this->releaseOrderReservations($order);
    }

    public function releaseOrderReservations(Order $order): void
    {
        foreach ($order->stockReservations()->where('status', 'active')->get() as $reservation) {
            $this->inventory->releaseReservation($reservation);
        }
    }

    public function deleteUnpaidOrder(Order $order, User $user): void
    {
        if ($order->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => 'دسترسی به این سفارش مجاز نیست.',
            ]);
        }

        if (! $order->isAwaitingPayment()) {
            throw ValidationException::withMessages([
                'order' => 'فقط سفارش‌های در انتظار پرداخت قابل حذف هستند.',
            ]);
        }

        DB::transaction(function () use ($order, $user): void {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $order->isAwaitingPayment() || $order->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'order' => 'فقط سفارش‌های در انتظار پرداخت قابل حذف هستند.',
                ]);
            }

            $this->releaseOrderReservations($order);
            $order->delete();
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'AALE-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    /**
     * @return array{recipient_name: string, recipient_mobile: string, province: string, city: string, postal_code: string, address: string}
     */
    private function resolveAddress(User $user, array $input): array
    {
        if (! empty($input['customer_address_id'])) {
            $address = CustomerAddress::query()
                ->where('user_id', $user->id)
                ->whereKey($input['customer_address_id'])
                ->first();

            if (! $address) {
                throw ValidationException::withMessages([
                    'customer_address_id' => 'آدرس انتخاب‌شده معتبر نیست.',
                ]);
            }

            return [
                'recipient_name' => $address->recipient_name,
                'recipient_mobile' => $address->recipient_mobile,
                'province' => $address->province,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'address' => $address->address,
            ];
        }

        $required = ['recipient_name', 'recipient_mobile', 'province', 'city', 'postal_code', 'address'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                $errors[$field] = 'این فیلد الزامی است.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        if (! preg_match('/^09\d{9}$/', (string) $input['recipient_mobile'])) {
            throw ValidationException::withMessages([
                'recipient_mobile' => 'فرمت شماره موبایل صحیح نیست.',
            ]);
        }

        if (! preg_match('/^\d{10}$/', (string) $input['postal_code'])) {
            throw ValidationException::withMessages([
                'postal_code' => 'کد پستی باید ۱۰ رقم باشد.',
            ]);
        }

        return [
            'recipient_name' => (string) $input['recipient_name'],
            'recipient_mobile' => (string) $input['recipient_mobile'],
            'province' => (string) $input['province'],
            'city' => (string) $input['city'],
            'postal_code' => (string) $input['postal_code'],
            'address' => (string) $input['address'],
        ];
    }
}