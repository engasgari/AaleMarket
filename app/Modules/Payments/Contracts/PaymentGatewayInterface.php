<?php

namespace App\Modules\Payments\Contracts;

use App\Modules\Payments\DTOs\GatewayRequestResult;
use App\Modules\Payments\DTOs\GatewayVerifyResult;
use App\Modules\Payments\Models\Payment;

interface PaymentGatewayInterface
{
    public function requestPayment(Payment $payment): GatewayRequestResult;

    public function verify(Payment $payment, array $callbackData): GatewayVerifyResult;
}