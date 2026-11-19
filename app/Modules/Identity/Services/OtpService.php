<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\OtpCode;
use App\Modules\Notifications\Models\SmsLog;
use App\Modules\Notifications\Services\MeliPayamakService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class OtpService
{
    public function __construct(
        private readonly MeliPayamakService $meliPayamak,
    ) {}

    /**
     * @return array{mobile: string, expires_in: int, simulated: bool, debug_code: ?string}
     */
    public function send(string $mobile, string $purpose = 'login'): array
    {
        $mobile = $this->normalizeMobile($mobile);
        $this->assertValidMobile($mobile);
        $this->assertSendRateLimits($mobile, $purpose);

        $length = (int) config('services.melipayamak.otp_length', 5);
        $expireSeconds = (int) config('services.melipayamak.otp_expire', 120);
        $maxAttempts = (int) config('services.melipayamak.otp_max_attempts', 5);

        $code = $this->generateSecureOtp($length);

        DB::transaction(function () use ($mobile, $purpose, $code, $expireSeconds, $maxAttempts) {
            OtpCode::query()
                ->where('mobile', $mobile)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            OtpCode::query()->create([
                'mobile' => $mobile,
                'code_hash' => Hash::make($code),
                'purpose' => $purpose,
                'attempts' => 0,
                'max_attempts' => $maxAttempts,
                'expires_at' => now()->addSeconds($expireSeconds),
                'ip_address' => request()?->ip(),
            ]);
        });

        try {
            $result = $this->meliPayamak->sendOtpPattern($mobile, $code);
        } catch (Throwable $e) {
            $this->logSms($mobile, 'failed', [
                'template' => 'otp',
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'mobile' => $e->getMessage() ?: 'ارسال پیامک کد تأیید ناموفق بود.',
            ]);
        }

        if (! $result->success) {
            $this->logSms($mobile, 'failed', [
                'template' => 'otp',
                'purpose' => $purpose,
                'error' => $result->message,
            ]);

            throw ValidationException::withMessages([
                'mobile' => 'ارسال پیامک کد تأیید ناموفق بود.',
            ]);
        }

        $this->logSms($mobile, $result->simulated ? 'simulated' : 'sent', [
            'template' => 'otp',
            'purpose' => $purpose,
            'provider_message_id' => $result->providerMessageId,
            'simulated' => $result->simulated,
        ]);

        $resendWait = (int) config('services.melipayamak.otp_resend_wait', 60);
        RateLimiter::hit($this->cooldownKey($mobile, $purpose), $resendWait);
        RateLimiter::hit($this->dailyKey($mobile), 86400);
        RateLimiter::hit($this->ipKey(), 86400);

        $debugCode = null;
        if ($result->simulated && config('app.debug')) {
            $debugCode = $code;
        }

        return [
            'mobile' => $mobile,
            'expires_in' => $expireSeconds,
            'simulated' => $result->simulated,
            'debug_code' => $debugCode,
        ];
    }

    public function verify(string $mobile, string $code, string $purpose = 'login'): bool
    {
        $mobile = $this->normalizeMobile($mobile);
        $this->assertValidMobile($mobile);

        $verifyKey = 'otp-verify:'.$mobile.':'.$purpose.'|'.(request()?->ip() ?? 'cli');
        if (RateLimiter::tooManyAttempts($verifyKey, 20)) {
            throw ValidationException::withMessages([
                'otpCode' => 'تلاش‌های تأیید بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
            ]);
        }

        /** @var OtpCode|null $otp */
        $otp = OtpCode::query()
            ->where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            RateLimiter::hit($verifyKey, 300);
            throw ValidationException::withMessages([
                'otpCode' => 'کد تأیید معتبر یافت نشد. لطفاً دوباره درخواست دهید.',
            ]);
        }

        if ($otp->expires_at->isPast()) {
            $otp->forceFill(['consumed_at' => now()])->save();
            throw ValidationException::withMessages([
                'otpCode' => 'کد تأیید منقضی شده است. لطفاً کد جدید دریافت کنید.',
            ]);
        }

        if ($otp->attempts >= $otp->max_attempts) {
            $otp->forceFill(['consumed_at' => now()])->save();
            throw ValidationException::withMessages([
                'otpCode' => 'تعداد تلاش‌های مجاز برای این کد به پایان رسیده است.',
            ]);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            RateLimiter::hit($verifyKey, 300);

            $remaining = max(0, (int) $otp->max_attempts - (int) $otp->fresh()->attempts);

            throw ValidationException::withMessages([
                'otpCode' => $remaining > 0
                    ? "کد تأیید نادرست است. {$remaining} تلاش باقی مانده است."
                    : 'تعداد تلاش‌های مجاز برای این کد به پایان رسیده است.',
            ]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();
        RateLimiter::clear($verifyKey);

        return true;
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

    public function generateSecureOtp(int $length = 5): string
    {
        $length = max(4, min(8, $length));
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function assertValidMobile(string $mobile): void
    {
        if (! preg_match('/^09\d{9}$/', $mobile)) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل باید با فرمت ۰۹xxxxxxxxx باشد.',
            ]);
        }
    }

    private function assertSendRateLimits(string $mobile, string $purpose): void
    {
        $cooldownKey = $this->cooldownKey($mobile, $purpose);
        $resendWait = (int) config('services.melipayamak.otp_resend_wait', 60);

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);
            throw ValidationException::withMessages([
                'mobile' => "لطفاً {$seconds} ثانیه دیگر برای ارسال مجدد صبر کنید.",
            ]);
        }

        $dailyLimit = (int) config('services.melipayamak.otp_daily_limit', 20);
        if (RateLimiter::tooManyAttempts($this->dailyKey($mobile), $dailyLimit)) {
            throw ValidationException::withMessages([
                'mobile' => 'سقف ارسال کد تأیید برای امروز تکمیل شده است.',
            ]);
        }

        $ipDailyLimit = (int) config('services.melipayamak.otp_ip_daily_limit', 40);
        if (RateLimiter::tooManyAttempts($this->ipKey(), $ipDailyLimit)) {
            throw ValidationException::withMessages([
                'mobile' => 'تعداد درخواست‌های این آدرس بیش از حد مجاز است.',
            ]);
        }
    }

    private function cooldownKey(string $mobile, string $purpose): string
    {
        return 'otp-cooldown:'.$mobile.':'.$purpose;
    }

    private function dailyKey(string $mobile): string
    {
        return 'otp-daily:'.$mobile;
    }

    private function ipKey(): string
    {
        return 'otp-ip:'.(request()?->ip() ?? 'cli');
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function logSms(string $mobile, string $status, array $meta = []): void
    {
        try {
            SmsLog::query()->create([
                'mobile' => $mobile,
                'driver' => 'melipayamak',
                'template' => $meta['template'] ?? 'otp',
                'status' => $status,
                'provider_message_id' => $meta['provider_message_id'] ?? null,
                'error_message' => $meta['error'] ?? null,
                'meta' => collect($meta)->except(['code', 'otp', 'password', 'token', 'api_key'])->all(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to persist SMS log', ['error' => $e->getMessage()]);
        }
    }
}
