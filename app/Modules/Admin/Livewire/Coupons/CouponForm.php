<?php

namespace App\Modules\Admin\Livewire\Coupons;

use App\Modules\Discounts\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CouponForm extends Component
{
    public ?Coupon $coupon = null;

    public string $code = '';

    public string $type = 'percentage';

    public int $value = 0;

    public int $min_purchase = 0;

    public ?int $max_discount = null;

    public ?int $usage_limit = null;

    public ?int $usage_per_customer = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public bool $is_active = true;

    public string $applies_to = 'all';

    public function mount(?Coupon $coupon = null): void
    {
        $this->coupon = $coupon;

        if ($coupon) {
            $this->authorize('coupons.update');
            $this->code = $coupon->code;
            $this->type = $coupon->type;
            $this->value = (int) $coupon->value;
            $this->min_purchase = (int) $coupon->min_purchase;
            $this->max_discount = $coupon->max_discount ? (int) $coupon->max_discount : null;
            $this->usage_limit = $coupon->usage_limit;
            $this->usage_per_customer = $coupon->usage_per_customer;
            $this->starts_at = $coupon->starts_at?->format('Y-m-d\TH:i');
            $this->ends_at = $coupon->ends_at?->format('Y-m-d\TH:i');
            $this->is_active = (bool) $coupon->is_active;
            $this->applies_to = $coupon->applies_to;
        } else {
            $this->authorize('coupons.create');
        }
    }

    public function save(): void
    {
        $this->coupon ? $this->authorize('coupons.update') : $this->authorize('coupons.create');

        $couponId = $this->coupon?->id;

        $this->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'integer', 'min:0'],
            'min_purchase' => ['integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
            'applies_to' => ['required', Rule::in(['all'])],
        ]);

        $payload = [
            'code' => strtoupper(trim($this->code)),
            'type' => $this->type,
            'value' => $this->value,
            'min_purchase' => $this->min_purchase,
            'max_discount' => $this->max_discount,
            'usage_limit' => $this->usage_limit,
            'usage_per_customer' => $this->usage_per_customer,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'is_active' => $this->is_active,
            'applies_to' => $this->applies_to,
        ];

        if ($this->coupon) {
            $this->coupon->update($payload);
            $coupon = $this->coupon;
        } else {
            $coupon = Coupon::query()->create($payload);
        }

        session()->flash('status', 'کد تخفیف ذخیره شد.');
        $this->redirect(route('admin.coupons.edit', $coupon), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.coupons.coupon-form')
            ->title($this->coupon ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید');
    }
}