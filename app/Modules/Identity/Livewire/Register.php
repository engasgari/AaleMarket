<?php

namespace App\Modules\Identity\Livewire;

use App\Models\User;
use App\Modules\Customers\Models\CustomerProfile;
use App\Modules\Identity\Services\AuthSessionService;
use App\Modules\Identity\Services\OtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('ثبت‌نام | آله مارکت')]
class Register extends Component
{
    public string $name = '';

    public string $mobile = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $otpCode = '';

    public bool $otpSent = false;

    public function sendOtp(OtpService $otp): void
    {
        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
        ], [
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست.',
        ]);

        $result = $otp->send($otp->normalizeMobile($this->mobile), 'register');
        $this->otpSent = true;

        $message = $result['simulated']
            ? 'کد تأیید ایجاد شد (ارسال واقعی هنوز فعال نیست؛ الگو در انتظار تأیید است).'
            : 'کد تأیید به شماره موبایل شما ارسال شد.';

        if ($result['debug_code']) {
            $message .= ' کد آزمایشی: '.$result['debug_code'];
        }

        session()->flash('status', $message);
    }

    public function register(OtpService $otp, AuthSessionService $authSession): void
    {
        $otpLength = (int) config('services.melipayamak.otp_length', 5);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'otpCode' => ['required', 'digits:'.$otpLength],
        ], [
            'name.required' => 'نام الزامی است.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.confirmed' => 'تأیید رمز عبور مطابقت ندارد.',
            'otpCode.required' => 'کد تأیید الزامی است.',
            'otpCode.digits' => "کد تأیید باید {$otpLength} رقم باشد.",
        ]);

        $mobile = $otp->normalizeMobile($this->mobile);
        $otp->verify($mobile, $this->otpCode, 'register');

        $user = DB::transaction(function () use ($mobile) {
            $user = User::query()->create([
                'name' => $this->name,
                'mobile' => $mobile,
                'password' => Hash::make($this->password),
                'status' => 'active',
                'mobile_verified_at' => now(),
            ]);

            CustomerProfile::query()->create([
                'user_id' => $user->id,
                'first_name' => $this->name,
                'last_name' => '',
            ]);

            return $user;
        });

        $authSession->loginUser($user, 'otp');

        $this->redirect(route('account.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.identity.register');
    }
}
