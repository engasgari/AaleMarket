<div class="container-store py-12 sm:py-16">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-primary">ورود به آله مارکت</h1>
        <p class="mt-2 text-sm text-ink-muted">با شماره موبایل وارد شوید.</p>

        <div class="mt-6 flex border border-line">
            <button type="button" wire:click="switchMode('password')" class="flex-1 px-4 py-2.5 text-sm {{ $mode === 'password' ? 'bg-primary text-white' : 'bg-surface text-ink-muted hover:bg-surface-secondary' }}">
                رمز عبور
            </button>
            <button type="button" wire:click="switchMode('otp')" class="flex-1 px-4 py-2.5 text-sm {{ $mode === 'otp' ? 'bg-primary text-white' : 'bg-surface text-ink-muted hover:bg-surface-secondary' }}">
                کد یکبارمصرف
            </button>
        </div>

        @if (session('status'))
            <div class="mt-4 border border-line bg-surface-secondary px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <label class="label" for="mobile">شماره موبایل</label>
                <input id="mobile" type="tel" wire:model="mobile" class="input" placeholder="09123456789" dir="ltr">
                @error('mobile') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            @if ($mode === 'password')
                <div>
                    <label class="label" for="password">رمز عبور</label>
                    <input id="password" type="password" wire:model="password" class="input" dir="ltr">
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <button type="button" wire:click="loginWithPassword" class="btn-primary w-full" wire:loading.attr="disabled">
                    ورود
                </button>
            @else
                @if ($otpSent)
                    <div>
                        <label class="label" for="otpCode">کد تأیید</label>
                        <input id="otpCode" type="text" inputmode="numeric" maxlength="{{ (int) config('services.melipayamak.otp_length', 5) }}" wire:model="otpCode" class="input" dir="ltr">
                        @error('code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        @error('otpCode') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="loginWithOtp" class="btn-primary w-full" wire:loading.attr="disabled">
                        تأیید و ورود
                    </button>
                @endif

                <button type="button" wire:click="sendOtp" class="btn-secondary w-full" wire:loading.attr="disabled">
                    {{ $otpSent ? 'ارسال مجدد کد' : 'ارسال کد تأیید' }}
                </button>
            @endif
        </div>

        <p class="mt-6 text-center text-sm text-ink-muted">
            حساب ندارید؟
            <a href="{{ route('register') }}" class="text-primary hover:underline">ثبت‌نام</a>
        </p>
    </div>
</div>
