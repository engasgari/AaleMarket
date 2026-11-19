<?php

namespace App\Modules\Payments\DTOs;

readonly class GatewayRequestResult
{
    public function __construct(
        public bool $success,
        public ?string $redirectUrl = null,
        public ?string $authority = null,
        public ?string $message = null,
        public ?array $rawResponse = null,
    ) {}
}