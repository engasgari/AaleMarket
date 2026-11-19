<?php

namespace Database\Seeders;

use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $pickup = ShippingMethod::query()->firstOrCreate(
            ['code' => 'store_pickup'],
            [
                'name' => 'تحویل حضوری از فروشگاه',
                'description' => 'مراجعه حضوری و دریافت کالا از انبار مرکزی',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        ShippingRate::query()->firstOrCreate(
            ['shipping_method_id' => $pickup->id, 'name' => 'رایگان'],
            [
                'min_order_amount' => 0,
                'price' => 0,
                'is_active' => true,
            ]
        );

        $post = ShippingMethod::query()->firstOrCreate(
            ['code' => 'express_post'],
            [
                'name' => 'پست پیشتاز',
                'description' => 'ارسال از طریق پست پیشتاز',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        ShippingRate::query()->firstOrCreate(
            ['shipping_method_id' => $post->id, 'name' => 'نرخ ثابت'],
            [
                'min_order_amount' => 0,
                'price' => 750000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 5,
                'is_active' => true,
            ]
        );
    }
}
