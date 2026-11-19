<?php

namespace App\Modules\Notifications\Services;

use App\Modules\Notifications\DTOs\MeliPayamakSendResult;
use Illuminate\Support\Facades\Log;
use Melipayamak\MelipayamakApi;
use RuntimeException;
use Throwable;

class MeliPayamakService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.melipayamak.enabled', false);
    }

    public function isPatternApproved(): bool
    {
        return (bool) config('services.melipayamak.pattern_approved', false);
    }

    public function isTestMode(): bool
    {
        return (bool) config('services.melipayamak.test_mode', true);
    }

    public function credentialsConfigured(): bool
    {
        return filled(config('services.melipayamak.username'))
            && filled(config('services.melipayamak.api_key'));
    }

    public function soapAvailable(): bool
    {
        return extension_loaded('soap');
    }

    public function patternVariableCount(): int
    {
        return max(1, (int) config('services.melipayamak.pattern_variable_count', 2));
    }

    public function bodyId(): string
    {
        return (string) config('services.melipayamak.pattern_code', '');
    }

    /**
     * Build pattern token list for the configured variable count.
     *
     * @return list<string>
     */
    public function buildPatternTokens(string $otp): array
    {
        $count = $this->patternVariableCount();

        if ($count === 1) {
            return [$otp];
        }

        if ($count === 2) {
            return [
                $otp,
                (string) config('services.melipayamak.pattern_second_value', 'ورود امن'),
            ];
        }

        throw new RuntimeException('تعداد متغیرهای الگوی پیامک پشتیبانی نمی‌شود. فقط ۱ یا ۲ متغیر مجاز است.');
    }

    public function assertReadyToSend(bool $requireApproval = true): void
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('سرویس ملی‌پیامک غیرفعال است.');
        }

        if (! $this->credentialsConfigured()) {
            throw new RuntimeException('نام کاربری یا API Key ملی‌پیامک تنظیم نشده است.');
        }

        if ($this->bodyId() === '') {
            throw new RuntimeException('کد الگوی (Body ID) ملی‌پیامک تنظیم نشده است.');
        }

        if ($requireApproval && ! $this->isPatternApproved()) {
            throw new RuntimeException('الگوی پیامک هنوز تأیید نشده است. ارسال واقعی مسدود است.');
        }

        if ($this->patternVariableCount() > 1 && ! $this->soapAvailable()) {
            throw new RuntimeException('برای الگوی چندمتغیره، افزونه PHP SOAP باید فعال باشد.');
        }
    }

    /**
     * Send OTP via patterned SMS. Uses SOAP when multiple variables are configured.
     */
    public function sendOtpPattern(string $mobile, string $otp): MeliPayamakSendResult
    {
        $tokens = $this->buildPatternTokens($otp);

        if (count($tokens) !== $this->patternVariableCount()) {
            throw new RuntimeException('تعداد متغیرهای الگو با تنظیمات سازگار نیست.');
        }

        if (! $this->isPatternApproved()) {
            Log::info('MeliPayamak OTP blocked: pattern not approved', [
                'mobile' => $this->maskMobile($mobile),
                'body_id' => $this->bodyId(),
                'variable_count' => count($tokens),
            ]);

            if ($this->isTestMode()) {
                return new MeliPayamakSendResult(
                    success: true,
                    message: 'ارسال واقعی انجام نشد؛ الگو تأیید نشده است (حالت آزمایشی).',
                    providerResponse: null,
                    simulated: true,
                );
            }

            throw new RuntimeException('الگوی پیامک هنوز تأیید نشده است. ارسال واقعی مسدود است.');
        }

        $this->assertReadyToSend(requireApproval: true);

        try {
            $response = $this->dispatchPattern($mobile, $tokens);

            $success = $this->isSuccessfulProviderResponse($response);

            Log::info('MeliPayamak pattern send completed', [
                'mobile' => $this->maskMobile($mobile),
                'body_id' => $this->bodyId(),
                'success' => $success,
                'transport' => $this->patternVariableCount() > 1 ? 'soap' : 'rest',
            ]);

            return new MeliPayamakSendResult(
                success: $success,
                message: $success
                    ? 'پیامک با موفقیت به ارائه‌دهنده ارسال شد.'
                    : 'پاسخ ارائه‌دهنده ناموفق بود: '.(string) $response,
                providerResponse: $response,
                simulated: false,
                providerMessageId: is_scalar($response) ? (string) $response : null,
            );
        } catch (Throwable $e) {
            Log::error('MeliPayamak pattern send failed', [
                'mobile' => $this->maskMobile($mobile),
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('ارسال پیامک از طریق ملی‌پیامک ناموفق بود.', 0, $e);
        }
    }

    /**
     * @param  list<string>  $tokens
     */
    protected function dispatchPattern(string $mobile, array $tokens): mixed
    {
        $username = (string) config('services.melipayamak.username');
        $apiKey = (string) config('services.melipayamak.api_key');
        $bodyId = $this->bodyId();

        $api = new MelipayamakApi($username, $apiKey);

        // Multi-variable patterns require SOAP SendByBaseNumber (array $text).
        // REST BaseServiceNumber does not reliably accept PHP arrays.
        if (count($tokens) > 1) {
            if (! $this->soapAvailable()) {
                throw new RuntimeException('افزونه SOAP برای ارسال الگوی چندمتغیره لازم است.');
            }

            $sms = $api->sms('soap');

            return $sms->sendByBaseNumber($tokens, $mobile, $bodyId);
        }

        $driver = (string) config('services.melipayamak.driver', 'soap');
        $sms = $driver === 'rest' ? $api->sms() : $api->sms('soap');

        return $sms->sendByBaseNumber($tokens[0], $mobile, $bodyId);
    }

    public function diagnose(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'credentials_configured' => $this->credentialsConfigured(),
            'pattern_code' => $this->bodyId() !== '' ? $this->bodyId() : null,
            'pattern_approved' => $this->isPatternApproved(),
            'pattern_variable_count' => $this->patternVariableCount(),
            'pattern_second_value_set' => filled(config('services.melipayamak.pattern_second_value')),
            'driver' => config('services.melipayamak.driver'),
            'test_mode' => $this->isTestMode(),
            'soap_loaded' => $this->soapAvailable(),
            'sdk_installed' => class_exists(MelipayamakApi::class),
        ];
    }

    public function maskMobile(string $mobile): string
    {
        if (strlen($mobile) < 7) {
            return '***';
        }

        return substr($mobile, 0, 4).'***'.substr($mobile, -2);
    }

    /**
     * Melipayamak success returns a positive numeric recId.
     * Zero/negative values are documented provider error codes and must not be treated as delivery success.
     */
    public function isSuccessfulProviderResponse(mixed $response): bool
    {
        if ($response === null || $response === false || $response === '') {
            return false;
        }

        if (is_bool($response)) {
            return false;
        }

        if (is_numeric($response)) {
            return (float) $response > 0;
        }

        if (is_string($response)) {
            $trimmed = trim($response);

            if ($trimmed === '' || str_contains(mb_strtolower($trimmed), 'error')) {
                return false;
            }

            // Accept only strictly numeric positive recId strings.
            if (preg_match('/^-?\d+(\.\d+)?$/', $trimmed) === 1) {
                return (float) $trimmed > 0;
            }

            return false;
        }

        return false;
    }
}
