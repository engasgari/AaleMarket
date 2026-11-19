<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string> */
    protected array $modules = [
        'products' => 'محصولات',
        'categories' => 'دسته‌بندی‌ها',
        'brands' => 'برندها',
        'inventory' => 'انبار',
        'orders' => 'سفارش‌ها',
        'customers' => 'مشتریان',
        'payments' => 'پرداخت‌ها',
        'coupons' => 'کوپن‌ها',
        'shipping' => 'ارسال',
        'settings' => 'تنظیمات',
        'users' => 'کاربران',
        'sms' => 'پیامک',
        'activity_logs' => 'گزارش فعالیت',
    ];

    public function run(): void
    {
        foreach ($this->modules as $module => $label) {
            Permission::query()->updateOrCreate(
                ['name' => "{$module}.*"],
                [
                    'label' => "مدیریت {$label}",
                    'module' => $module,
                ]
            );
        }

        $roles = [
            'super_admin' => [
                'label' => 'مدیر ارشد',
                'description' => 'دسترسی کامل به تمام بخش‌های سیستم',
                'permissions' => 'all',
            ],
            'admin' => [
                'label' => 'مدیر',
                'description' => 'مدیریت عملیاتی فروشگاه',
                'permissions' => [
                    'products.*', 'categories.*', 'brands.*', 'inventory.*',
                    'orders.*', 'customers.*', 'payments.*', 'coupons.*',
                    'shipping.*', 'settings.*', 'sms.*', 'activity_logs.*',
                ],
            ],
            'product_manager' => [
                'label' => 'مدیر محصول',
                'description' => 'مدیریت کاتالوگ محصولات',
                'permissions' => ['products.*', 'categories.*', 'brands.*'],
            ],
            'inventory_manager' => [
                'label' => 'مدیر انبار',
                'description' => 'مدیریت موجودی و انبار',
                'permissions' => ['inventory.*', 'products.*'],
            ],
            'order_manager' => [
                'label' => 'مدیر سفارش',
                'description' => 'مدیریت سفارش‌ها و ارسال',
                'permissions' => [
                    'orders.*', 'customers.*', 'payments.*', 'shipping.*',
                ],
            ],
        ];

        $allPermissions = Permission::query()->pluck('id', 'name');

        foreach ($roles as $name => $config) {
            $role = Role::query()->updateOrCreate(
                ['name' => $name],
                [
                    'label' => $config['label'],
                    'description' => $config['description'],
                ]
            );

            if ($config['permissions'] === 'all') {
                $role->permissions()->sync($allPermissions->values()->all());
            } else {
                $ids = collect($config['permissions'])
                    ->map(fn (string $perm) => $allPermissions[$perm] ?? null)
                    ->filter()
                    ->values()
                    ->all();
                $role->permissions()->sync($ids);
            }
        }
    }
}