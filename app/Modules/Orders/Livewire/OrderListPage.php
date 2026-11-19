<?php

namespace App\Modules\Orders\Livewire;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Payments\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.storefront')]
#[Title('سفارش‌های من | آله مارکت')]
class OrderListPage extends Component
{
    use WithPagination;

    public function payOnline(int $orderId, PaymentService $payments)
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->whereKey($orderId)
            ->firstOrFail();

        if (! $order->isAwaitingPayment()) {
            $this->dispatch('toast', message: 'این سفارش قابل پرداخت نیست.', type: 'error');

            return null;
        }

        try {
            $payment = $payments->createPaymentForOrder($order, Auth::user());
            $redirectUrl = $payments->redirectToGateway($payment);

            return redirect()->away($redirectUrl);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?: 'خطا در اتصال به درگاه.', type: 'error');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage() ?: 'خطا در اتصال به درگاه.', type: 'error');
        }

        return null;
    }

    public function deleteUnpaid(int $orderId, OrderService $orders): void
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->whereKey($orderId)
            ->firstOrFail();

        try {
            $orders->deleteUnpaidOrder($order, Auth::user());
            $this->dispatch('toast', message: 'سفارش پرداخت‌نشده حذف شد.', type: 'success');
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?: 'حذف سفارش ممکن نیست.', type: 'error');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage() ?: 'حذف سفارش ممکن نیست.', type: 'error');
        }
    }

    public function render()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->with(['items'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.orders.order-list-page', [
            'orders' => $orders,
            'currencyLabel' => config('aale.currency_label', 'ریال'),
        ]);
    }
}
