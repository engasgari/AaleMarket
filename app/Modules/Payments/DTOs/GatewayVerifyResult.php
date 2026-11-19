<?php

namespace App\Modules\Payments\DTOs;

readonly class GatewayVerifyResult
{
    public function __construct(
        public bool $success,
        public ?string $referenceId = null,
        public ?string $trackingCode = null,
        public ?string $message = null,
        public ?array $rawResponse = null,
    ) {}
}