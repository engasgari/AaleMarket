<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Admin\Models\Setting;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\DTOs\GatewayRequestResult;
use App\Modules\Payments\DTOs\GatewayVerifyResult;
use App\Modules\Payments\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZarinpalGateway implements PaymentGatewayInterface
{
    private bool $sandbox;

    private string $merchantId;

    private string $callbackUrl;

    public function __construct()
    {
        $config = config('aale.payment.zarinpal', []);

        $this->merchantId = (string) (
            Setting::getValue('payment', 'zarinpal_merchant_id')
            ?: ($config['merchant_id'] ?? '')
        );

        $sandboxSetting = Setting::getValue('payment', 'zarinpal_sandbox', null);
        $this->sandbox = $sandboxSetting !== null
            ? (bool) $sandboxSetting
            : (bool) ($config['sandbox'] ?? true);

        if ($this->merchantId === '') {
            throw new RuntimeException('شناسه پذیرنده زرین‌پال تنظیم نشده است.');
        }

        $this->callbackUrl = (string) ($config['callback_url'] ?? route('payments.zarinpal.callback'));
    }

    public function requestPayment(Payment $payment): GatewayRequestResult
    {
        $payment->loadMissing('order');

        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => (int) $payment->amount,
            'callback_url' => $this->callbackUrl,
            'description' => 'پرداخت سفارش '.($payment->order?->order_number ?? $payment->order_id),
            'metadata' => [
                'order_id' => $payment->order_id,
                'payment_id' => $payment->id,
            ],
        ];

        $response = Http::timeout(30)
            ->acceptJson()
            ->post($this->requestUrl(), $payload);

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            return new GatewayRequestResult(
                success: false,
                message: 'خطا در ارتباط با درگاه پرداخت.',
                rawResponse: $body,
            );
        }

        $data = $body['data'] ?? null;
        $errors = $body['errors'] ?? null;

        if (! is_array($data) || empty($data['authority']) || (int) ($data['code'] ?? 0) !== 100) {
            $message = is_array($errors)
                ? collect($errors)->pluck('message')->filter()->first()
                : null;

            return new GatewayRequestResult(
                success: false,
                message: $message ?: 'درخواست پرداخت توسط زرین‌پال رد شد.',
                rawResponse: $body,
            );
        }

        $authority = (string) $data['authority'];
        $startPayBase = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://payment.zarinpal.com/pg/StartPay/';

        return new GatewayRequestResult(
            success: true,
            redirectUrl: $startPayBase.$authority,
            authority: $authority,
            rawResponse: $body,
        );
    }

    public function verify(Payment $payment, array $callbackData): GatewayVerifyResult
    {
        $authority = $callbackData['Authority'] ?? $callbackData['authority'] ?? $payment->authority;

        if (! $authority) {
            return new GatewayVerifyResult(
                success: false,
                message: 'کد authority یافت نشد.',
            );
        }

        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => (int) $payment->amount,
            'authority' => (string) $authority,
        ];

        $response = Http::timeout(30)
            ->acceptJson()
            ->post($this->verifyUrl(), $payload);

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            return new GatewayVerifyResult(
                success: false,
                message: 'خطا در تأیید پرداخت با درگاه.',
                rawResponse: $body,
            );
        }

        $data = $body['data'] ?? null;
        $code = is_array($data) ? ($data['code'] ?? null) : null;

        if ($code !== 100 && $code !== '100') {
            $errors = $body['errors'] ?? null;
            $message = is_array($errors)
                ? collect($errors)->pluck('message')->filter()->first()
                : null;

            return new GatewayVerifyResult(
                success: false,
                message: $message ?: 'تأیید پرداخت ناموفق بود.',
                rawResponse: $body,
            );
        }

        $refId = isset($data['ref_id']) ? (string) $data['ref_id'] : null;
        $cardPan = isset($data['card_pan']) ? (string) $data['card_pan'] : null;

        return new GatewayVerifyResult(
            success: true,
            referenceId: $refId,
            trackingCode: $cardPan,
            message: 'پرداخت با موفقیت تأیید شد.',
            rawResponse: $body,
        );
    }

    private function requestUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://payment.zarinpal.com/pg/v4/payment/request.json';
    }

    private function verifyUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://payment.zarinpal.com/pg/v4/payment/verify.json';
    }
}