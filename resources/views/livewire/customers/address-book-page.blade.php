<div class="container-store py-10 sm:py-14">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-primary">مدیریت آدرس‌ها</h1>
            <p class="mt-1 text-sm text-ink-muted">آدرس‌های ذخیره‌شده برای ارسال سفارش</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('account.index') }}" class="text-sm text-primary">بازگشت به حساب کاربری</a>
            @unless($showForm)
                <button type="button" class="btn-primary" wire:click="startCreate">افزودن آدرس</button>
            @endunless
        </div>
    </div>

    @include('livewire.catalog.partials.flash')

    @if($showForm)
        <section class="mt-8 border border-line p-6">
            <h2 class="text-lg font-medium">{{ $editingId ? 'ویرایش آدرس' : 'آدرس جدید' }}</h2>
            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">عنوان آدرس (اختیاری)</label>
                    <input class="input" wire:model="title" placeholder="منزل، محل کار...">
                    @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">نام گیرنده</label>
                    <input class="input" wire:model="recipientName">
                    @error('recipientName') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">موبایل گیرنده</label>
                    <input class="input" wire:model="recipientMobile" dir="ltr">
                    @error('recipientMobile') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">استان</label>
                    <input class="input" wire:model="province">
                    @error('province') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">شهر</label>
                    <input class="input" wire:model="city">
                    @error('city') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">کد پستی</label>
                    <input class="input" wire:model="postalCode" dir="ltr">
                    @error('postalCode') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label">آدرس کامل</label>
                    <textarea class="input" rows="3" wire:model="address"></textarea>
                    @error('address') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <label class="sm:col-span-2 flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="isDefault">
                    تنظیم به‌عنوان آدرس پیش‌فرض
                </label>
                <div class="sm:col-span-2 flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">ذخیره آدرس</button>
                    <button type="button" class="btn-secondary" wire:click="cancelForm">انصراف</button>
                </div>
            </form>
        </section>
    @endif

    <div class="mt-8 space-y-4">
        @forelse($addresses as $item)
            <div wire:key="address-{{ $item->id }}" class="border border-line p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="text-sm leading-7">
                        <div class="font-medium text-ink">
                            {{ $item->title ?: 'آدرس' }}
                            @if($item->is_default)
                                <span class="ms-2 text-xs text-success">پیش‌فرض</span>
                            @endif
                        </div>
                        <div>{{ $item->recipient_name }} — <span dir="ltr">{{ $item->recipient_mobile }}</span></div>
                        <div class="text-ink-muted">{{ $item->province }}، {{ $item->city }}</div>
                        <div>{{ $item->address }}</div>
                        <div class="text-ink-muted" dir="ltr">کد پستی: {{ $item->postal_code }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @unless($item->is_default)
                            <button type="button" class="btn-secondary px-3 py-1.5 text-xs" wire:click="makeDefault({{ $item->id }})">
                                پیش‌فرض
                            </button>
                        @endunless
                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" wire:click="startEdit({{ $item->id }})">
                            ویرایش
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-danger hover:bg-danger/10"
                            wire:click="deleteAddress({{ $item->id }})"
                            wire:confirm="این آدرس حذف شود؟"
                            aria-label="حذف آدرس"
                            title="حذف آدرس"
                        >
                            <i class="fa-solid fa-trash-can text-base" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-muted">هنوز آدرسی ذخیره نکرده‌اید.</p>
        @endforelse
    </div>
</div>
