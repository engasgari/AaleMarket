<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductImage;
use App\Modules\Categories\Models\Category;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::query()->firstOrCreate(
            ['slug' => 'aale'],
            ['name' => 'آله', 'is_active' => true, 'sort_order' => 1]
        );

        $smartHome = Category::query()->firstOrCreate(
            ['slug' => 'houshmand'],
            ['name' => 'خانه و کسب‌وکار هوشمند', 'is_active' => true, 'sort_order' => 1]
        );

        $network = Category::query()->firstOrCreate(
            ['slug' => 'shabake'],
            ['name' => 'تجهیزات شبکه', 'is_active' => true, 'sort_order' => 2]
        );

        $cctv = Category::query()->firstOrCreate(
            ['slug' => 'cctv'],
            ['name' => 'دوربین مداربسته', 'is_active' => true, 'sort_order' => 3]
        );

        $products = [
            [
                'name' => 'پریز هوشمند',
                'slug' => 'priz-houshmand',
                'sku' => 'SMART-OUTLET-01',
                'selling_price' => 1890000,
                'discount_price' => 1590000,
                'short_description' => 'کنترل از راه دور مصرف برق با اپلیکیشن آله',
                'description' => 'پریز هوشمند آله امکان روشن و خاموش کردن از راه دور، زمان‌بندی مصرف و پایش انرژی را فراهم می‌کند.',
                'image' => 'products/priz-houshmand.jpg',
                'category' => $smartHome,
                'featured' => true,
                'stock' => 40,
            ],
            [
                'name' => 'قلاده هوشمند',
                'slug' => 'ghalade-houshmand',
                'sku' => 'SMART-COLLAR-01',
                'selling_price' => 2450000,
                'discount_price' => null,
                'short_description' => 'ردیابی موقعیت و سلامت حیوان خانگی',
                'description' => 'قلاده هوشمند آله با ردیابی موقعیت، هشدار محدوده امن و پایش فعالیت، مراقبت از حیوان خانگی را آسان می‌کند.',
                'image' => 'products/ghalade-houshmand.jpg',
                'category' => $smartHome,
                'featured' => true,
                'stock' => 28,
            ],
            [
                'name' => 'UPS هوشمند',
                'slug' => 'ups-houshmand',
                'sku' => 'SMART-UPS-01',
                'selling_price' => 8750000,
                'discount_price' => 7990000,
                'short_description' => 'تأمین برق پایدار با پایش وضعیت آنلاین',
                'description' => 'UPS هوشمند آله برای حفاظت از تجهیزات حساس طراحی شده و وضعیت باتری، بار و قطعی برق را گزارش می‌دهد.',
                'image' => 'products/ups-houshmand.jpg',
                'category' => $smartHome,
                'featured' => true,
                'stock' => 15,
            ],
            [
                'name' => 'سوییچ شبکه ۸ پورت گیگابیتی',
                'slug' => 'switch-8port-gigabit',
                'sku' => 'NET-SW-8G',
                'selling_price' => 18500000,
                'discount_price' => 16900000,
                'short_description' => 'محصول نمونه فروشگاه آله برای تست نمایش و خرید.',
                'description' => 'این یک محصول نمونه است. مشخصات فنی کامل از پنل مدیریت قابل ویرایش است.',
                'image' => 'products/switch-8port-gigabit.jpg',
                'category' => $network,
                'featured' => false,
                'stock' => 25,
            ],
            [
                'name' => 'دوربین مداربسته دام ۲ مگاپیکسل',
                'slug' => 'cctv-dome-2mp',
                'sku' => 'CCTV-DOME-2MP',
                'selling_price' => 24500000,
                'discount_price' => null,
                'short_description' => 'محصول نمونه فروشگاه آله برای تست نمایش و خرید.',
                'description' => 'این یک محصول نمونه است. مشخصات فنی کامل از پنل مدیریت قابل ویرایش است.',
                'image' => 'products/cctv-dome-2mp.jpg',
                'category' => $cctv,
                'featured' => false,
                'stock' => 12,
            ],
            [
                'name' => 'کابل شبکه Cat6 روکش‌دار',
                'slug' => 'cable-cat6',
                'sku' => 'NET-CABLE-CAT6',
                'selling_price' => 3200000,
                'discount_price' => null,
                'short_description' => 'محصول نمونه فروشگاه آله برای تست نمایش و خرید.',
                'description' => 'این یک محصول نمونه است. مشخصات فنی کامل از پنل مدیریت قابل ویرایش است.',
                'image' => 'products/cable-cat6.jpg',
                'category' => $network,
                'featured' => false,
                'stock' => 100,
            ],
        ];

        $inventory = app(InventoryService::class);
        $warehouse = Warehouse::query()->where('is_default', true)->first()
            ?? Warehouse::query()->firstOrCreate(
                ['code' => 'MAIN'],
                ['name' => 'انبار مرکزی', 'is_active' => true, 'is_default' => true]
            );

        foreach ($products as $data) {
            $imagePath = $data['image'] ?? null;

            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'selling_price' => $data['selling_price'],
                    'discount_price' => $data['discount_price'],
                    'main_image_path' => $imagePath,
                    'status' => 'published',
                    'is_featured' => $data['featured'],
                    'stock_status' => 'in_stock',
                    'published_at' => now(),
                    'min_order_qty' => 1,
                ]
            );

            $product->categories()->syncWithoutDetaching([
                $data['category']->id => ['is_primary' => true],
            ]);

            if ($imagePath) {
                ProductImage::query()->updateOrCreate(
                    ['product_id' => $product->id, 'is_main' => true],
                    [
                        'path' => $imagePath,
                        'alt' => $product->name,
                        'sort_order' => 0,
                    ]
                );
            }

            $available = $inventory->availableQuantity($product->id, null, $warehouse->id);
            if ($available < $data['stock']) {
                $inventory->stockIn($warehouse->id, $product->id, $data['stock'] - $available, null, 'Demo seed');
            }
        }
    }
}
