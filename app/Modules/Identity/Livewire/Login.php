<?php

namespace App\Modules\Identity\Livewire;

use App\Models\User;
use App\Modules\Identity\Services\AuthSessionService;
use App\Modules\Identity\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('ورود | آله مارکت')]
class Login extends Component
{
    public string $mode = 'otp';

    public string $mobile = '';

    public string $password = '';

    public string $otpCode = '';

    public bool $otpSent = false;

    public function switchMode(string $mode): void
    {
        $this->mode = in_array($mode, ['password', 'otp'], true) ? $mode : 'otp';
        $this->resetValidation();
        $this->otpSent = false;
        $this->otpCode = '';
        $this->password = '';
    }

    public function loginWithPassword(OtpService $otp, AuthSessionService $authSession): void
    {
        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $mobile = $otp->normalizeMobile($this->mobile);
        $key = 'login-password:'.$mobile.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'mobile' => 'تلاش‌های ورود بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
            ]);
        }

        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user || ! $user->password || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 300);
            $authSession->recordLogin($user?->id, $mobile, 'password', 'failed', 'invalid_credentials');

            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل یا رمز عبور نادرست است.',
            ]);
        }

        RateLimiter::clear($key);
        $authSession->loginUser($user, 'password');

        $this->redirectIntended(default: route('account.index'), navigate: true);
    }

    public function sendOtp(OtpService $otp): void
    {
        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست.',
        ]);

        $result = $otp->send($this->mobile, 'login');
        $this->otpSent = true;

        $message = $result['simulated']
            ? 'کد تأیید ایجاد شد (ارسال واقعی هنوز فعال نیست؛ الگو در انتظار تأیید است).'
            : 'کد تأیید به شماره موبایل شما ارسال شد.';

        if ($result['debug_code']) {
            $message .= ' کد آزمایشی: '.$result['debug_code'];
        }

        session()->flash('status', $message);
    }

    public function loginWithOtp(OtpService $otp, AuthSessionService $authSession): void
    {
        $otpLength = (int) config('services.melipayamak.otp_length', 5);

        $this->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'otpCode' => ['required', 'digits:'.$otpLength],
        ], [
            'otpCode.required' => 'کد تأیید الزامی است.',
            'otpCode.digits' => "کد تأیید باید {$otpLength} رقم باشد.",
        ]);

        $mobile = $otp->normalizeMobile($this->mobile);
        $otp->verify($mobile, $this->otpCode, 'login');

        $user = $authSession->findOrCreateCustomerByMobile($mobile);
        $authSession->loginUser($user, 'otp');

        $this->redirectIntended(default: route('account.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.identity.login');
    }
}
