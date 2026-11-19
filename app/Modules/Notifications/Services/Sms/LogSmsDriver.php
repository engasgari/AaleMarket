<?php

namespace App\Modules\Notifications\Services\Sms;

use App\Modules\Notifications\Contracts\SmsDriverInterface;
use Illuminate\Support\Facades\Log;

class LogSmsDriver implements SmsDriverInterface
{
    public function send(string $mobile, string $message, array $data = []): bool
    {
        Log::channel('single')->info('SMS [log driver]', [
            'mobile' => $this->maskMobile($mobile),
            'message_length' => mb_strlen($message),
            'data_keys' => array_keys($data),
        ]);

        return true;
    }

    public function sendPattern(string $mobile, string $patternCode, array $tokens = []): bool
    {
        $safeTokens = collect($tokens)
            ->map(fn ($value, $key) => str_contains(strtolower((string) $key), 'code') || str_contains(strtolower((string) $key), 'otp')
                ? '[redacted]'
                : $value)
            ->all();

        Log::channel('single')->info('SMS pattern [log driver]', [
            'mobile' => $this->maskMobile($mobile),
            'pattern' => $patternCode,
            'tokens' => $safeTokens,
        ]);

        return true;
    }

    private function maskMobile(string $mobile): string
    {
        if (strlen($mobile) < 7) {
            return '***';
        }

        return substr($mobile, 0, 4).'***'.substr($mobile, -2);
    }
}
