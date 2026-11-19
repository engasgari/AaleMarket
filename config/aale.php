<?php

return [

    'currency' => env('CURRENCY', 'IRR'),

    'currency_label' => (static function () {
        $label = (string) env('CURRENCY_LABEL', 'ریال');

        return ($label === '' || str_contains($label, '?')) ? 'ریال' : $label;
    })(),

    'min_order_amount' => (int) env('MIN_ORDER_AMOUNT', 0),

    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

    'stock_reservation_minutes' => (int) env('STOCK_RESERVATION_MINUTES', 30),

    'otp' => [
        'length' => (int) env('MELIPAYAMAK_OTP_LENGTH', env('OTP_LENGTH', 5)),
        'expiration_seconds' => (int) env('MELIPAYAMAK_OTP_EXPIRE', 120),
        'max_attempts' => (int) env('MELIPAYAMAK_OTP_MAX_ATTEMPTS', env('OTP_MAX_ATTEMPTS', 5)),
        'resend_cooldown_seconds' => (int) env('MELIPAYAMAK_OTP_RESEND_WAIT', env('OTP_RESEND_COOLDOWN_SECONDS', 60)),
        'daily_limit' => (int) env('MELIPAYAMAK_OTP_DAILY_LIMIT', env('OTP_DAILY_LIMIT', 20)),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'melipayamak'),
        'melipayamak' => [
            'username' => env('MELIPAYAMAK_USERNAME'),
            'password' => env('MELIPAYAMAK_API_KEY', env('MELIPAYAMAK_PASSWORD')),
            'from' => env('MELIPAYAMAK_FROM'),
            'otp_pattern' => env('MELIPAYAMAK_PATTERN_CODE', env('MELIPAYAMAK_OTP_PATTERN')),
        ],
    ],

    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'zarinpal'),
        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'sandbox' => (bool) env('ZARINPAL_SANDBOX', true),
            'callback_url' => env('ZARINPAL_CALLBACK_URL'),
        ],
    ],

];
