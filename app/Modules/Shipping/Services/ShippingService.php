<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingRate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShippingService
{
    /** @return Collection<int, ShippingMethod> */
    public function listActiveMethods(): Collection
    {
        return ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['activeRates'])
            ->get();
    }

    /**
     * @return array{method: ShippingMethod, rate: ShippingRate, price: int}
     */
    public function quote(int $shippingMethodId, int $orderAmount): array
    {
        $method = ShippingMethod::query()
            ->whereKey($shippingMethodId)
            ->where('is_active', true)
            ->first();

        if (! $method) {
            throw ValidationException::withMessages([
                'shipping_method_id' => 'روش ارسال انتخاب‌شده معتبر نیست.',
            ]);
        }

        $rate = $this->resolveRate($method, $orderAmount);

        if (! $rate) {
            throw ValidationException::withMessages([
                'shipping_method_id' => 'نرخ ارسال برای مبلغ سفارش یافت نشد.',
            ]);
        }

        return [
            'method' => $method,
            'rate' => $rate,
            'price' => (int) $rate->price,
        ];
    }

    private function resolveRate(ShippingMethod $method, int $orderAmount): ?ShippingRate
    {
        $rates = $method->activeRates()
            ->where('min_order_amount', '<=', $orderAmount)
            ->where(function ($query) use ($orderAmount) {
                $query->whereNull('max_order_amount')
                    ->orWhere('max_order_amount', '>=', $orderAmount);
            })
            ->orderByDesc('min_order_amount')
            ->get();

        if ($rates->isEmpty()) {
            return null;
        }

        return $rates->first();
    }
}