<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Models\Role;
use Illuminate\Database\Seeder;
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SUPER_ADMIN_PASSWORD', '');

        if ($password === null || $password === '') {
            $this->command?->warn('SuperAdminSeeder: SUPER_ADMIN_PASSWORD is empty; skipping super admin creation.');

            return;
        }

        $mobile = env('SUPER_ADMIN_MOBILE', '09120000000');
        $email = env('SUPER_ADMIN_EMAIL');
        $name = env('SUPER_ADMIN_NAME', 'مدیر سیستم');

        $user = User::query()->updateOrCreate(
            ['mobile' => $mobile],
            [
                'name' => $name,
                'email' => $email ?: null,
                'password' => $password,
                'status' => 'active',
                'mobile_verified_at' => now(),
                'email_verified_at' => $email ? now() : null,
            ]
        );

        $superAdminRole = Role::query()->where('name', 'super_admin')->first();

        if ($superAdminRole !== null) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}