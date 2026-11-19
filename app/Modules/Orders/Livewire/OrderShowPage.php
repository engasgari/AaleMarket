<?php

namespace App\Modules\Orders\Livewire;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Payments\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.storefront')]
class OrderShowPage extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $this->order = $order->load(['items', 'address', 'statusHistories', 'payments']);
    }

    public function payOnline(PaymentService $payments)
    {
        $this->order->refresh();

        if ($this->order->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $this->order->isAwaitingPayment()) {
            $this->dispatch('toast', message: 'این سفارش قابل پرداخت نیست.', type: 'error');

            return null;
        }

        try {
            $payment = $payments->createPaymentForOrder($this->order, Auth::user());
            $redirectUrl = $payments->redirectToGateway($payment);

            return redirect()->away($redirectUrl);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?: 'خطا در اتصال به درگاه.', type: 'error');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage() ?: 'خطا در اتصال به درگاه.', type: 'error');
        }

        return null;
    }

    public function deleteUnpaid(OrderService $orders)
    {
        $this->order->refresh();

        if ($this->order->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $orders->deleteUnpaidOrder($this->order, Auth::user());
            $this->dispatch('toast', message: 'سفارش پرداخت‌نشده حذف شد.', type: 'success');

            return $this->redirect(route('account.orders.index'), navigate: true);
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: collect($e->errors())->flatten()->first() ?: 'حذف سفارش ممکن نیست.', type: 'error');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage() ?: 'حذف سفارش ممکن نیست.', type: 'error');
        }

        return null;
    }

    public function render()
    {
        $statusLabel = match ($this->order->status->value) {
            'pending_payment' => 'در انتظار پرداخت',
            'paid' => 'پرداخت‌شده',
            'processing' => 'در حال پردازش',
            'preparing_shipment' => 'آماده‌سازی ارسال',
            'shipped' => 'ارسال‌شده',
            'delivered' => 'تحویل‌شده',
            'cancelled' => 'لغو شده',
            'refunded' => 'مرجوعی',
            default => $this->order->status->value,
        };

        return view('livewire.orders.order-show-page', [
            'statusLabel' => $statusLabel,
            'currencyLabel' => config('aale.currency_label', 'ریال'),
        ])->title('سفارش '.$this->order->order_number.' | آله مارکت');
    }
}
