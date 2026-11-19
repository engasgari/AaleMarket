<?php

namespace App\Modules\Admin\Livewire\Shipping;

use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingRate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ShippingMethodForm extends Component
{
    public ?ShippingMethod $shippingMethod = null;

    public string $name = '';

    public string $code = '';

    public ?string $description = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    /** @var array<int, array{id: ?int, name: string, min_order_amount: int, price: int, is_active: bool}> */
    public array $rates = [];

    public function mount(?ShippingMethod $shippingMethod = null): void
    {
        $this->shippingMethod = $shippingMethod;

        if ($shippingMethod) {
            $this->authorize('shipping.update');
            $shippingMethod->load('rates');
            $this->name = $shippingMethod->name;
            $this->code = $shippingMethod->code;
            $this->description = $shippingMethod->description;
            $this->is_active = (bool) $shippingMethod->is_active;
            $this->sort_order = (int) $shippingMethod->sort_order;
            $this->rates = $shippingMethod->rates->map(fn (ShippingRate $rate) => [
                'id' => $rate->id,
                'name' => $rate->name,
                'min_order_amount' => (int) $rate->min_order_amount,
                'price' => (int) $rate->price,
                'is_active' => (bool) $rate->is_active,
            ])->all();
        } else {
            $this->authorize('shipping.create');
            $this->rates = [
                ['id' => null, 'name' => 'نرخ پیش‌فرض', 'min_order_amount' => 0, 'price' => 0, 'is_active' => true],
            ];
        }
    }

    public function addRate(): void
    {
        $this->rates[] = [
            'id' => null,
            'name' => 'نرخ جدید',
            'min_order_amount' => 0,
            'price' => 0,
            'is_active' => true,
        ];
    }

    public function removeRate(int $index): void
    {
        unset($this->rates[$index]);
        $this->rates = array_values($this->rates);
    }

    public function save(): void
    {
        $this->shippingMethod ? $this->authorize('shipping.update') : $this->authorize('shipping.create');

        $methodId = $this->shippingMethod?->id;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('shipping_methods', 'code')->ignore($methodId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'rates' => ['array', 'min:1'],
            'rates.*.name' => ['required', 'string', 'max:255'],
            'rates.*.min_order_amount' => ['integer', 'min:0'],
            'rates.*.price' => ['integer', 'min:0'],
            'rates.*.is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($methodId) {
            $payload = [
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ];

            if ($this->shippingMethod) {
                $this->shippingMethod->update($payload);
                $method = $this->shippingMethod;
            } else {
                $method = ShippingMethod::query()->create($payload);
                $this->shippingMethod = $method;
            }

            $keptIds = [];

            foreach ($this->rates as $rateData) {
                $ratePayload = [
                    'name' => $rateData['name'],
                    'min_order_amount' => (int) $rateData['min_order_amount'],
                    'price' => (int) $rateData['price'],
                    'is_active' => (bool) $rateData['is_active'],
                ];

                if (! empty($rateData['id'])) {
                    $rate = ShippingRate::query()
                        ->where('shipping_method_id', $method->id)
                        ->whereKey($rateData['id'])
                        ->first();
                    if ($rate) {
                        $rate->update($ratePayload);
                        $keptIds[] = $rate->id;
                    }
                } else {
                    $rate = $method->rates()->create($ratePayload);
                    $keptIds[] = $rate->id;
                }
            }

            $method->rates()->whereNotIn('id', $keptIds)->delete();
        });

        session()->flash('status', 'روش ارسال ذخیره شد.');
        $this->redirect(route('admin.shipping.edit', $this->shippingMethod), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.shipping.shipping-method-form')
            ->title($this->shippingMethod ? 'ویرایش روش ارسال' : 'روش ارسال جدید');
    }
}