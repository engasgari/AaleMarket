<?php

namespace App\Modules\Customers\Livewire;

use App\Modules\Customers\Models\CustomerProfile;
use App\Modules\Identity\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('تنظیمات پروفایل | آله مارکت')]
class ProfilePage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->mobile = (string) ($user->mobile ?? '');
    }

    public function saveProfile(OtpService $otp): void
    {
        $user = Auth::user();
        $this->mobile = $otp->normalizeMobile($this->mobile);

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'nullable',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile' => [
                'required',
                'regex:/^09\d{9}$/',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
        ], [
            'name.required' => 'نام الزامی است.',
            'email.email' => 'ایمیل معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'شماره موبایل باید با فرمت ۰۹xxxxxxxxx باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        ]);

        $user->forceFill([
            'name' => trim($validated['name']),
            'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            'mobile' => $validated['mobile'],
        ])->save();

        $profile = CustomerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['first_name' => trim($validated['name']), 'last_name' => '']
        );

        $profile->forceFill(['first_name' => trim($validated['name'])])->save();

        $this->dispatch('toast', message: 'اطلاعات پروفایل ذخیره شد.', type: 'success');
    }

    public function changePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'currentPassword' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ], [
            'currentPassword.required' => 'رمز عبور فعلی الزامی است.',
            'password.required' => 'رمز عبور جدید الزامی است.',
            'password.same' => 'تکرار رمز عبور مطابقت ندارد.',
            'passwordConfirmation.required' => 'تکرار رمز عبور الزامی است.',
        ]);

        if (! Hash::check($this->currentPassword, (string) $user->password)) {
            $this->addError('currentPassword', 'رمز عبور فعلی نادرست است.');

            return;
        }

        $user->forceFill([
            'password' => $this->password,
        ])->save();

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        $this->dispatch('toast', message: 'رمز عبور با موفقیت تغییر کرد.', type: 'success');
    }

    public function render()
    {
        return view('livewire.customers.profile-page');
    }
}
