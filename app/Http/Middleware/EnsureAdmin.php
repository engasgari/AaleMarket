<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /** @var list<string> */
    protected array $adminRoles = [
        'super_admin',
        'admin',
        'product_manager',
        'inventory_manager',
        'order_manager',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($this->adminRoles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'دسترسی به پنل مدیریت مجاز نیست.');
    }
}