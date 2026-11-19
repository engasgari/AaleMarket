<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MeliPayamak SMS
    |--------------------------------------------------------------------------
    |
    | API Key is passed as the password argument to the official SDK.
    | Multi-variable patterns require the PHP SOAP extension and the
    | SOAP transport: $api->sms('soap')->sendByBaseNumber([$otp, $second], ...).
    |
    */
    'melipayamak' => [
        'enabled' => (bool) env('MELIPAYAMAK_ENABLED', false),
        'username' => env('MELIPAYAMAK_USERNAME'),
        'api_key' => env('MELIPAYAMAK_API_KEY', env('MELIPAYAMAK_PASSWORD')),
        'pattern_code' => env('MELIPAYAMAK_PATTERN_CODE', env('MELIPAYAMAK_OTP_PATTERN', '156526')),
        'pattern_approved' => (bool) env('MELIPAYAMAK_PATTERN_APPROVED', false),
        'pattern_variable_count' => (int) env('MELIPAYAMAK_PATTERN_VARIABLE_COUNT', 2),
        'pattern_second_value' => env('MELIPAYAMAK_PATTERN_SECOND_VALUE', 'ورود امن'),
        'driver' => env('MELIPAYAMAK_DRIVER', 'soap'),
        'test_mode' => (bool) env('MELIPAYAMAK_TEST_MODE', true),
        'otp_length' => (int) env('MELIPAYAMAK_OTP_LENGTH', 5),
        'otp_expire' => (int) env('MELIPAYAMAK_OTP_EXPIRE', 120),
        'otp_max_attempts' => (int) env('MELIPAYAMAK_OTP_MAX_ATTEMPTS', 5),
        'otp_resend_wait' => (int) env('MELIPAYAMAK_OTP_RESEND_WAIT', 60),
        'otp_daily_limit' => (int) env('MELIPAYAMAK_OTP_DAILY_LIMIT', 20),
        'otp_ip_daily_limit' => (int) env('MELIPAYAMAK_OTP_IP_DAILY_LIMIT', 40),
    ],

];
