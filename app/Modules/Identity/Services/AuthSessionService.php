<?php

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Cart\Services\CartService;
use App\Modules\Customers\Models\CustomerProfile;
use App\Modules\Identity\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthSessionService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function loginUser(User $user, string $method = 'otp'): void
    {
        if ($user->status !== 'active') {
            $this->recordLogin($user->id, (string) $user->mobile, $method, 'failed', 'inactive');
            throw ValidationException::withMessages([
                'mobile' => 'حساب کاربری غیرفعال است.',
            ]);
        }

        $guestSessionId = session()->getId();

        Auth::login($user, true);
        request()->session()->regenerate();

        $this->cart->mergeGuestCartBySessionId($user, $guestSessionId);

        $user->forceFill([
            'last_login_at' => now(),
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ])->save();

        $this->recordLogin($user->id, (string) $user->mobile, $method, 'success');
    }

    public function findOrCreateCustomerByMobile(string $mobile, ?string $name = null): User
    {
        $user = User::query()->where('mobile', $mobile)->first();

        if ($user) {
            return $user;
        }

        $user = User::query()->create([
            'name' => $name ?: ('کاربر '.$mobile),
            'mobile' => $mobile,
            'password' => Hash::make(str()->random(32)),
            'status' => 'active',
            'mobile_verified_at' => now(),
        ]);

        CustomerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $name ?: 'کاربر',
                'last_name' => '',
            ]
        );

        return $user;
    }

    public function recordLogin(?int $userId, string $mobile, string $method, string $status, ?string $reason = null): void
    {
        LoginHistory::query()->create([
            'user_id' => $userId,
            'mobile' => $mobile,
            'method' => $method,
            'status' => $status,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'failure_reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
