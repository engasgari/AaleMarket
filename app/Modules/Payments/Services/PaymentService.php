<?php

namespace App\Modules\Payments\Services;

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Enums\PaymentStatus;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\PaymentLog;
use App\Modules\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private OrderService $orderService,
    ) {}

    public function createPaymentForOrder(Order $order, User $user): Payment
    {
        if ($order->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => 'دسترسی به این سفارش مجاز نیست.',
            ]);
        }

        if ($order->total_amount <= 0) {
            throw ValidationException::withMessages([
                'order' => 'مبلغ سفارش نامعتبر است.',
            ]);
        }

        return Payment::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => config('aale.payment.driver', 'zarinpal'),
            'amount' => (int) $order->total_amount,
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function redirectToGateway(Payment $payment): string
    {
        $payment->load('order');

        if ($payment->status === PaymentStatus::Paid) {
            throw new RuntimeException('این پرداخت قبلاً انجام شده است.');
        }

        $payment->forceFill(['status' => PaymentStatus::Processing])->save();

        $result = $this->gateway->requestPayment($payment);

        $this->recordTransaction($payment, 'request', $result->success ? 'success' : 'failed', [
            'amount' => $payment->amount,
        ], $result->rawResponse);

        if (! $result->success || ! $result->redirectUrl) {
            $payment->forceFill(['status' => PaymentStatus::Failed])->save();
            $this->log($payment, 'error', $result->message ?? 'درخواست پرداخت ناموفق', $result->rawResponse);

            throw ValidationException::withMessages([
                'payment' => $result->message ?? 'امکان اتصال به درگاه پرداخت نیست.',
            ]);
        }

        $payment->forceFill([
            'authority' => $result->authority,
            'status' => PaymentStatus::Pending,
            'meta' => array_merge($payment->meta ?? [], [
                'redirect_url' => $result->redirectUrl,
            ]),
        ])->save();

        $this->log($payment, 'info', 'درخواست پرداخت ایجاد شد', [
            'authority' => $result->authority,
        ]);

        return $result->redirectUrl;
    }

    /**
     * @return array{success: bool, payment: Payment, message?: string}
     */
    public function handleCallback(array $callbackData): array
    {
        $authority = $callbackData['Authority'] ?? $callbackData['authority'] ?? null;
        $status = $callbackData['Status'] ?? $callbackData['status'] ?? null;

        if (! $authority) {
            throw ValidationException::withMessages([
                'payment' => 'پارامترهای بازگشت از درگاه ناقص است.',
            ]);
        }

        return DB::transaction(function () use ($authority, $status, $callbackData) {
            /** @var Payment|null $payment */
            $payment = Payment::query()
                ->where('authority', $authority)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                throw ValidationException::withMessages([
                    'payment' => 'پرداخت یافت نشد.',
                ]);
            }

            $payment->load('order');

            if ($payment->status === PaymentStatus::Paid) {
                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'پرداخت قبلاً تأیید شده است.',
                ];
            }

            $userCancelled = strtoupper((string) $status) === 'NOK';

            if ($userCancelled) {
                $this->recordTransaction($payment, 'callback', 'cancelled', $callbackData, null);
                $payment->forceFill(['status' => PaymentStatus::Cancelled])->save();
                $this->log($payment, 'warning', 'کاربر پرداخت را لغو کرد', $callbackData);
                $this->orderService->markPaymentFailed($payment->order, $payment->user_id, 'لغو پرداخت توسط کاربر');

                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => 'پرداخت توسط شما لغو شد.',
                ];
            }

            $verify = $this->gateway->verify($payment, $callbackData);

            $this->recordTransaction($payment, 'verify', $verify->success ? 'success' : 'failed', $callbackData, $verify->rawResponse);

            if (! $verify->success) {
                $payment->forceFill(['status' => PaymentStatus::Failed])->save();
                $this->log($payment, 'error', $verify->message ?? 'تأیید پرداخت ناموفق', $verify->rawResponse);
                $this->orderService->markPaymentFailed($payment->order, $payment->user_id, $verify->message);

                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => $verify->message ?? 'تأیید پرداخت ناموفق بود.',
                ];
            }

            $payment->forceFill([
                'status' => PaymentStatus::Paid,
                'reference_id' => $verify->referenceId,
                'tracking_code' => $verify->trackingCode,
                'paid_at' => now(),
                'meta' => array_merge($payment->meta ?? [], [
                    'verify_response' => $verify->rawResponse,
                ]),
            ])->save();

            $this->log($payment, 'info', 'پرداخت با موفقیت تأیید شد', [
                'reference_id' => $verify->referenceId,
            ]);

            $this->orderService->markPaid($payment->order, $payment->user_id, 'پرداخت آنلاین زرین‌پال');

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'message' => $verify->message,
            ];
        });
    }

    private function recordTransaction(Payment $payment, string $type, string $status, ?array $payload, ?array $response): void
    {
        PaymentTransaction::query()->create([
            'payment_id' => $payment->id,
            'type' => $type,
            'status' => $status,
            'payload' => $payload,
            'response' => $response,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    private function log(Payment $payment, string $level, string $message, ?array $context = null): void
    {
        PaymentLog::query()->create([
            'payment_id' => $payment->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'created_at' => now(),
        ]);
    }
}