<div class="container-store py-10 sm:py-14">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-primary">تنظیمات پروفایل</h1>
            <p class="mt-1 text-sm text-ink-muted">نام، ایمیل، موبایل و رمز عبور</p>
        </div>
        <a href="{{ route('account.index') }}" class="text-sm text-primary">بازگشت به حساب کاربری</a>
    </div>

    @include('livewire.catalog.partials.flash')

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="border border-line p-6">
            <h2 class="text-lg font-medium text-ink">اطلاعات شخصی</h2>
            <form wire:submit="saveProfile" class="mt-5 space-y-4">
                <div>
                    <label class="label" for="profile-name">نام</label>
                    <input id="profile-name" type="text" class="input" wire:model="name" autocomplete="name">
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="profile-email">ایمیل</label>
                    <input id="profile-email" type="email" class="input" wire:model="email" dir="ltr" autocomplete="email">
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="profile-mobile">شماره موبایل</label>
                    <input id="profile-mobile" type="tel" class="input" wire:model="mobile" dir="ltr" autocomplete="tel">
                    @error('mobile') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">ذخیره اطلاعات</button>
            </form>
        </section>

        <section class="border border-line p-6">
            <h2 class="text-lg font-medium text-ink">تغییر رمز عبور</h2>
            <form wire:submit="changePassword" class="mt-5 space-y-4">
                <div>
                    <label class="label" for="current-password">رمز عبور فعلی</label>
                    <input id="current-password" type="password" class="input" wire:model="currentPassword" autocomplete="current-password">
                    @error('currentPassword') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="new-password">رمز عبور جدید</label>
                    <input id="new-password" type="password" class="input" wire:model="password" autocomplete="new-password">
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="new-password-confirmation">تکرار رمز عبور جدید</label>
                    <input id="new-password-confirmation" type="password" class="input" wire:model="passwordConfirmation" autocomplete="new-password">
                    @error('passwordConfirmation') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">تغییر رمز عبور</button>
            </form>
        </section>
    </div>
</div>
