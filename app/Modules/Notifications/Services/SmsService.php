<?php

namespace App\Modules\Notifications\Services;

use App\Modules\Notifications\Contracts\SmsDriverInterface;
use App\Modules\Notifications\Models\SmsLog;
use App\Modules\Notifications\Services\Sms\LogSmsDriver;
use App\Modules\Notifications\Services\Sms\MeliPayamakSmsDriver;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    public function __construct(
        private readonly MeliPayamakService $meliPayamak,
    ) {}

    public function driver(?string $name = null): SmsDriverInterface
    {
        $driver = $name ?: config('aale.sms.driver', 'melipayamak');

        return match ($driver) {
            'melipayamak' => app(MeliPayamakSmsDriver::class),
            default => app(LogSmsDriver::class),
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function send(string $mobile, string $message, array $meta = []): bool
    {
        $mobile = $this->normalizeMobile($mobile);

        try {
            $ok = $this->driver()->send($mobile, $message, $meta);
            $this->logSms($mobile, $ok ? 'sent' : 'failed', $meta);

            return $ok;
        } catch (Throwable $e) {
            Log::error('SMS send exception', [
                'mobile' => $this->meliPayamak->maskMobile($mobile),
                'error' => $e->getMessage(),
            ]);
            $this->logSms($mobile, 'failed', array_merge($meta, ['error' => $e->getMessage()]));

            return false;
        }
    }

    public function sendOtp(string $mobile, string $otpCode): bool
    {
        $mobile = $this->normalizeMobile($mobile);

        try {
            $result = $this->meliPayamak->sendOtpPattern($mobile, $otpCode);

            $this->logSms($mobile, $result->simulated ? 'simulated' : ($result->success ? 'sent' : 'failed'), [
                'template' => 'otp',
                'provider_message_id' => $result->providerMessageId,
                'simulated' => $result->simulated,
                'error' => $result->success ? null : $result->message,
            ]);

            return $result->success;
        } catch (Throwable $e) {
            Log::error('OTP SMS exception', [
                'mobile' => $this->meliPayamak->maskMobile($mobile),
                'error' => $e->getMessage(),
            ]);
            $this->logSms($mobile, 'failed', [
                'template' => 'otp',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', $mobile) ?: '';

        if (str_starts_with($mobile, '98') && strlen($mobile) === 12) {
            $mobile = '0'.substr($mobile, 2);
        }

        if (str_starts_with($mobile, '9') && strlen($mobile) === 10) {
            $mobile = '0'.$mobile;
        }

        return $mobile;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function logSms(string $mobile, string $status, array $meta = []): void
    {
        try {
            SmsLog::query()->create([
                'mobile' => $mobile,
                'driver' => config('aale.sms.driver', 'melipayamak'),
                'template' => $meta['template'] ?? null,
                'status' => $status,
                'provider_message_id' => $meta['provider_message_id'] ?? null,
                'error_message' => $meta['error'] ?? null,
                'meta' => collect($meta)->except(['code', 'otp', 'password', 'token', 'api_key'])->all(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Logging must never break the main flow.
        }
    }
}
