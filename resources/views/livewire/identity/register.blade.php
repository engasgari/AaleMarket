<div class="container-store py-12 sm:py-16">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-primary">ثبت‌نام در آله مارکت</h1>
        <p class="mt-2 text-sm text-ink-muted">برای ایجاد حساب، شماره موبایل خود را تأیید کنید.</p>

        @if (session('status'))
            <div class="mt-4 border border-line bg-surface-secondary px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <label class="label" for="name">نام و نام خانوادگی</label>
                <input id="name" type="text" wire:model="name" class="input">
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="mobile">شماره موبایل</label>
                <input id="mobile" type="tel" wire:model="mobile" class="input" placeholder="09123456789" dir="ltr">
                @error('mobile') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="password">رمز عبور</label>
                <input id="password" type="password" wire:model="password" class="input" dir="ltr">
                @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="password_confirmation">تأیید رمز عبور</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" class="input" dir="ltr">
            </div>

            @if ($otpSent)
                <div>
                    <label class="label" for="otpCode">کد تأیید</label>
                    <input id="otpCode" type="text" inputmode="numeric" maxlength="{{ (int) config('services.melipayamak.otp_length', 5) }}" wire:model="otpCode" class="input" dir="ltr">
                    @error('otpCode') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    @error('code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex flex-col gap-2">
                <button type="button" wire:click="sendOtp" class="btn-secondary w-full" wire:loading.attr="disabled">
                    {{ $otpSent ? 'ارسال مجدد کد' : 'ارسال کد تأیید' }}
                </button>

                @if ($otpSent)
                    <button type="button" wire:click="register" class="btn-primary w-full" wire:loading.attr="disabled">
                        تکمیل ثبت‌نام
                    </button>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-sm text-ink-muted">
            قبلاً ثبت‌نام کرده‌اید؟
            <a href="{{ route('login') }}" class="text-primary hover:underline">ورود</a>
        </p>
    </div>
</div>
