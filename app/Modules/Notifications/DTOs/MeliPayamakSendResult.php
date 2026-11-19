<?php

namespace App\Modules\Notifications\DTOs;

final class MeliPayamakSendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $providerResponse = null,
        public readonly bool $simulated = false,
        public readonly ?string $providerMessageId = null,
    ) {}
}
