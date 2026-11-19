<?php

namespace App\Modules\Admin\Livewire\Orders;

use App\Modules\Orders\Enums\OrderStatus;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderStatusHistory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrderShow extends Component
{
    public Order $order;

    public string $newStatus = '';

    public ?string $statusNote = null;

    public function mount(Order $order): void
    {
        $this->authorize('orders.view');
        $this->order = $order->load(['user', 'address', 'items', 'payments', 'statusHistories.user']);
        $this->newStatus = $order->status->value;
    }

    public function updateStatus(): void
    {
        $this->authorize('orders.update');

        $this->validate([
            'newStatus' => ['required', 'string'],
            'statusNote' => ['nullable', 'string', 'max:500'],
        ]);

        $toStatus = OrderStatus::from($this->newStatus);
        $fromStatus = $this->order->status;

        if ($fromStatus === $toStatus) {
            return;
        }

        $this->order->update(['status' => $toStatus]);

        OrderStatusHistory::query()->create([
            'order_id' => $this->order->id,
            'from_status' => $fromStatus->value,
            'to_status' => $toStatus->value,
            'note' => $this->statusNote ?: 'تغییر وضعیت توسط مدیر',
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        $this->order->refresh()->load(['user', 'address', 'items', 'payments', 'statusHistories.user']);
        session()->flash('status', 'وضعیت سفارش به‌روزرسانی شد.');
    }

    public function render(): View
    {
        return view('livewire.admin.orders.order-show', [
            'statuses' => OrderStatus::cases(),
        ])->title('سفارش '.$this->order->order_number);
    }
}