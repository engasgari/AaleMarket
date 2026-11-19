<?php

namespace App\Modules\Notifications\Contracts;

interface SmsDriverInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function send(string $mobile, string $message, array $data = []): bool;

    /**
     * Send a patterned OTP SMS when the provider supports patterns.
     *
     * @param  array<string, mixed>  $tokens
     */
    public function sendPattern(string $mobile, string $patternCode, array $tokens = []): bool;
}
