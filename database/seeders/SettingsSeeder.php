<?php

namespace Database\Seeders;

use App\Modules\Admin\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store' => [
                'store_name' => ['value' => 'آله مارکت', 'type' => 'string', 'is_public' => true],
                'store_tagline' => ['value' => 'فروشگاه اینترنتی', 'type' => 'string', 'is_public' => true],
                'store_phone' => ['value' => '', 'type' => 'string', 'is_public' => true],
                'store_email' => ['value' => 'info@aale.ir', 'type' => 'string', 'is_public' => true],
                'store_address' => ['value' => '', 'type' => 'string', 'is_public' => true],
            ],
            'currency' => [
                'currency' => ['value' => env('CURRENCY', 'IRR'), 'type' => 'string', 'is_public' => true],
                'currency_label' => ['value' => env('CURRENCY_LABEL', 'ریال'), 'type' => 'string', 'is_public' => true],
                'min_order_amount' => ['value' => (string) env('MIN_ORDER_AMOUNT', 0), 'type' => 'integer', 'is_public' => true],
            ],
            'locale' => [
                'locale' => ['value' => 'fa', 'type' => 'string', 'is_public' => true],
                'timezone' => ['value' => 'Asia/Tehran', 'type' => 'string', 'is_public' => false],
                'direction' => ['value' => 'rtl', 'type' => 'string', 'is_public' => true],
            ],
            'checkout' => [
                'low_stock_threshold' => ['value' => (string) env('LOW_STOCK_THRESHOLD', 5), 'type' => 'integer', 'is_public' => false],
                'guest_checkout' => ['value' => '0', 'type' => 'boolean', 'is_public' => false],
            ],
            'sms' => [
                'otp_enabled' => ['value' => '1', 'type' => 'boolean', 'is_public' => false],
                'otp_expiry_minutes' => ['value' => '5', 'type' => 'integer', 'is_public' => false],
            ],
            'payment' => [
                'zarinpal_merchant_id' => [
                    'value' => (string) env('ZARINPAL_MERCHANT_ID', ''),
                    'type' => 'string',
                    'is_public' => false,
                ],
                'zarinpal_sandbox' => [
                    'value' => env('ZARINPAL_SANDBOX', false) ? '1' : '0',
                    'type' => 'boolean',
                    'is_public' => false,
                ],
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $meta) {
                Setting::query()->updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    [
                        'value' => $meta['value'],
                        'type' => $meta['type'],
                        'is_public' => $meta['is_public'],
                    ]
                );
            }
        }
    }
}