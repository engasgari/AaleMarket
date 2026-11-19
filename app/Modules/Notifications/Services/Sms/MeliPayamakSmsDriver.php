<?php

namespace App\Modules\Notifications\Services\Sms;

use App\Modules\Notifications\Contracts\SmsDriverInterface;
use App\Modules\Notifications\Services\MeliPayamakService;
use RuntimeException;

/**
 * Thin adapter kept for SmsService compatibility.
 * OTP patterned sends should go through MeliPayamakService / OtpService.
 */
class MeliPayamakSmsDriver implements SmsDriverInterface
{
    public function __construct(
        private readonly MeliPayamakService $meliPayamak,
    ) {}

    public function send(string $mobile, string $message, array $data = []): bool
    {
        throw new RuntimeException(
            'ارسال متن آزاد برای OTP توصیه نمی‌شود. از الگوی تأییدشده ملی‌پیامک استفاده کنید.'
        );
    }

    public function sendPattern(string $mobile, string $patternCode, array $tokens = []): bool
    {
        $otp = (string) ($tokens['code'] ?? $tokens[0] ?? '');

        if ($otp === '') {
            throw new RuntimeException('کد OTP برای ارسال الگو موجود نیست.');
        }

        // Allow temporary override of body id from caller.
        if ($patternCode !== '' && $patternCode !== $this->meliPayamak->bodyId()) {
            config(['services.melipayamak.pattern_code' => $patternCode]);
        }

        $result = $this->meliPayamak->sendOtpPattern($mobile, $otp);

        return $result->success;
    }
}
