<?php

namespace Database\Seeders;

use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::query()->firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'انبار مرکزی',
                'address' => 'تهران',
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }
}
