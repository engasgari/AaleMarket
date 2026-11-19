<?php

namespace App\Modules\Admin\Livewire\Orders;

use App\Modules\Orders\Enums\OrderStatus;
use App\Modules\Orders\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('سفارش‌ها')]
class OrderIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('orders.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public static function statusLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PendingPayment => 'در انتظار پرداخت',
            OrderStatus::Paid => 'پرداخت‌شده',
            OrderStatus::Processing => 'در حال پردازش',
            OrderStatus::PreparingShipment => 'آماده‌سازی ارسال',
            OrderStatus::Shipped => 'ارسال‌شده',
            OrderStatus::Delivered => 'تحویل‌شده',
            OrderStatus::Cancelled => 'لغو‌شده',
            OrderStatus::Refunded => 'مرجوع‌شده',
        };
    }

    public function render(): View
    {
        $orders = Order::query()
            ->with(['user', 'address'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $term = $this->search;
                $q->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', '%'.$term.'%')
                        ->orWhereHas('address', fn ($a) => $a->where('recipient_mobile', 'like', '%'.$term.'%'))
                        ->orWhereHas('user', fn ($u) => $u->where('mobile', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.orders.order-index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }
}