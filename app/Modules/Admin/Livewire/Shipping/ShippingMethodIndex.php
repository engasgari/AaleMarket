<?php

namespace App\Modules\Admin\Livewire\Shipping;

use App\Modules\Shipping\Models\ShippingMethod;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('روش‌های ارسال')]
class ShippingMethodIndex extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('shipping.view');
    }

    public function render(): View
    {
        $methods = ShippingMethod::query()
            ->withCount('rates')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.shipping.shipping-method-index', [
            'methods' => $methods,
        ]);
    }
}