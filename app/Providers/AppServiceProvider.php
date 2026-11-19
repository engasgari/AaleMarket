<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var list<string> */
    protected array $permissionModules = [
        'products',
        'categories',
        'brands',
        'inventory',
        'orders',
        'customers',
        'payments',
        'coupons',
        'shipping',
        'settings',
        'users',
        'sms',
        'activity_logs',
    ];

    /** @var list<string> */
    protected array $permissionActions = ['view', 'create', 'update', 'delete'];

    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Payments\Contracts\PaymentGatewayInterface::class,
            function () {
                $driver = config('aale.payment.driver', 'zarinpal');

                return match ($driver) {
                    'zarinpal' => $this->app->make(\App\Modules\Payments\Gateways\ZarinpalGateway::class),
                    default => throw new \InvalidArgumentException('درایور پرداخت پشتیبانی نمی‌شود: '.$driver),
                };
            }
        );
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Carbon::setLocale(config('app.locale', 'fa'));

        Gate::before(function (?User $user, string $ability) {
            if (! $user instanceof User) {
                return null;
            }

            if ($user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });

        foreach ($this->permissionModules as $module) {
            foreach ($this->permissionActions as $action) {
                $name = "{$module}.{$action}";
                Gate::define($name, fn (User $user) => $user->hasPermission($name));
            }
            Gate::define("{$module}.*", fn (User $user) => $user->hasPermission("{$module}.*"));
        }
    }
}
