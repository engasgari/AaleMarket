<?php

namespace App\Modules\Admin\Livewire\Coupons;

use App\Modules\Discounts\Models\Coupon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('کدهای تخفیف')]
class CouponIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('coupons.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $coupons = Coupon::query()
            ->when($this->search !== '', fn ($q) => $q->where('code', 'like', '%'.$this->search.'%'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.coupons.coupon-index', [
            'coupons' => $coupons,
        ]);
    }
}