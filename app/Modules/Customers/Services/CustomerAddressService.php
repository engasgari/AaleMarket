<?php

namespace App\Modules\Customers\Services;

use App\Models\User;
use App\Modules\Customers\Models\CustomerAddress;
use App\Modules\Identity\Services\OtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerAddressService
{
    public function __construct(
        private readonly OtpService $otp,
    ) {}

    /**
     * @param  array{
     *     title?: string|null,
     *     recipient_name: string,
     *     recipient_mobile: string,
     *     province: string,
     *     city: string,
     *     postal_code: string,
     *     address: string,
     *     is_default?: bool
     * }  $data
     */
    public function create(User $user, array $data): CustomerAddress
    {
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($user, $payload) {
            if ($payload['is_default'] || ! $user->addresses()->exists()) {
                $this->clearDefaults($user);
                $payload['is_default'] = true;
            }

            return CustomerAddress::query()->create([
                'user_id' => $user->id,
                ...$payload,
            ]);
        });
    }

    /**
     * @param  array{
     *     title?: string|null,
     *     recipient_name: string,
     *     recipient_mobile: string,
     *     province: string,
     *     city: string,
     *     postal_code: string,
     *     address: string,
     *     is_default?: bool
     * }  $data
     */
    public function update(User $user, CustomerAddress $address, array $data): CustomerAddress
    {
        $this->assertOwned($user, $address);
        $payload = $this->normalize($data);

        return DB::transaction(function () use ($user, $address, $payload) {
            if ($payload['is_default']) {
                $this->clearDefaults($user);
            }

            $address->fill($payload)->save();

            if (! $user->addresses()->where('is_default', true)->exists()) {
                $address->forceFill(['is_default' => true])->save();
            }

            return $address->fresh();
        });
    }

    public function delete(User $user, CustomerAddress $address): void
    {
        $this->assertOwned($user, $address);

        DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->orderByDesc('id')->first();
                if ($next) {
                    $next->forceFill(['is_default' => true])->save();
                }
            }
        });
    }

    public function makeDefault(User $user, CustomerAddress $address): void
    {
        $this->assertOwned($user, $address);

        DB::transaction(function () use ($user, $address) {
            $this->clearDefaults($user);
            $address->forceFill(['is_default' => true])->save();
        });
    }

    private function assertOwned(User $user, CustomerAddress $address): void
    {
        if ((int) $address->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'address' => 'دسترسی به این آدرس مجاز نیست.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     title: string|null,
     *     recipient_name: string,
     *     recipient_mobile: string,
     *     province: string,
     *     city: string,
     *     postal_code: string,
     *     address: string,
     *     is_default: bool
     * }
     */
    private function normalize(array $data): array
    {
        $mobile = $this->otp->normalizeMobile((string) ($data['recipient_mobile'] ?? ''));

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            throw ValidationException::withMessages([
                'recipient_mobile' => 'شماره موبایل گیرنده معتبر نیست.',
            ]);
        }

        $postal = preg_replace('/\D+/', '', (string) ($data['postal_code'] ?? '')) ?: '';
        if (strlen($postal) !== 10) {
            throw ValidationException::withMessages([
                'postal_code' => 'کد پستی باید ۱۰ رقم باشد.',
            ]);
        }

        return [
            'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : null,
            'recipient_name' => trim((string) ($data['recipient_name'] ?? '')),
            'recipient_mobile' => $mobile,
            'province' => trim((string) ($data['province'] ?? '')),
            'city' => trim((string) ($data['city'] ?? '')),
            'postal_code' => $postal,
            'address' => trim((string) ($data['address'] ?? '')),
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }

    private function clearDefaults(User $user): void
    {
        $user->addresses()->update(['is_default' => false]);
    }
}
