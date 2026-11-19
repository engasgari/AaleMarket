<?php

namespace App\Modules\Discounts\Services;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Discounts\Models\Coupon;
use App\Modules\Discounts\Models\CouponUsage;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return array{coupon: Coupon, discount_amount: int, eligible_subtotal: int}
     */
    public function validateAndCalculate(string $code, User $user, int $subtotal, Collection $cartItems): array
    {
        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'کد تخفیف معتبر نیست.',
            ]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'کد تخفیف هنوز فعال نشده است.',
            ]);
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'کد تخفیف منقضی شده است.',
            ]);
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cartItems);

        if ($eligibleSubtotal < (int) $coupon->min_purchase) {
            throw ValidationException::withMessages([
                'coupon_code' => 'حداقل مبلغ خرید برای این کد تخفیف رعایت نشده است.',
            ]);
        }

        if ($coupon->usage_limit !== null) {
            $totalUses = $coupon->usages()->count();
            if ($totalUses >= (int) $coupon->usage_limit) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'سقف استفاده از این کد تخفیف پر شده است.',
                ]);
            }
        }

        if ($coupon->usage_per_customer !== null) {
            $customerUses = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();

            if ($customerUses >= (int) $coupon->usage_per_customer) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'شما قبلاً از این کد تخفیف استفاده کرده‌اید.',
                ]);
            }
        }

        $discount = match ($coupon->type) {
            'percentage' => (int) floor($eligibleSubtotal * ((int) $coupon->value) / 100),
            'fixed' => (int) $coupon->value,
            default => throw ValidationException::withMessages([
                'coupon_code' => 'نوع کد تخفیف پشتیبانی نمی‌شود.',
            ]),
        };

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (int) $coupon->max_discount);
        }

        $discount = min($discount, $eligibleSubtotal);
        $discount = max(0, $discount);

        if ($discount <= 0) {
            throw ValidationException::withMessages([
                'coupon_code' => 'این کد تخفیف برای سبد خرید شما قابل اعمال نیست.',
            ]);
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => $discount,
            'eligible_subtotal' => $eligibleSubtotal,
        ];
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     */
    private function eligibleSubtotal(Coupon $coupon, Collection $cartItems): int
    {
        $appliesTo = $coupon->applies_to ?? 'all';

        if ($appliesTo === 'all') {
            return (int) $cartItems->sum(fn (CartItem $item) => $item->lineTotal());
        }

        $coupon->loadMissing(['products', 'categories']);

        $productIds = $coupon->products->pluck('id')->all();
        $categoryIds = $coupon->categories->pluck('id')->all();

        $total = 0;

        foreach ($cartItems as $item) {
            $item->loadMissing('product.categories');
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $matches = match ($appliesTo) {
                'products' => in_array((int) $product->id, $productIds, true),
                'categories' => $product->categories->pluck('id')->intersect($categoryIds)->isNotEmpty(),
                default => false,
            };

            if ($matches) {
                $total += $item->lineTotal();
            }
        }

        return $total;
    }
}