<div class="container-store py-10">
    <h1 class="text-2xl font-semibold text-ink">تسویه حساب</h1>
    <p class="mt-1 text-sm text-ink-muted">مراحل نهایی ثبت سفارش</p>

    @include('livewire.catalog.partials.flash')

    <div class="mt-6 flex flex-wrap gap-2 text-xs">
        @foreach([1 => 'آدرس', 2 => 'ارسال', 3 => 'تخفیف', 4 => 'بازبینی', 5 => 'پرداخت'] as $num => $label)
            <button type="button" wire:click="goToStep({{ $num }})" class="px-3 py-1.5 border border-line {{ $step === $num ? 'bg-primary text-white' : 'bg-surface text-ink-muted' }}">
                {{ $num }}. {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @if($step === 1)
                <div class="card-surface p-5 space-y-4">
                    <h2 class="text-lg font-medium">آدرس تحویل</h2>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm text-ink-muted">یکی از آدرس‌های ذخیره‌شده را انتخاب کنید.</p>
                        <a href="{{ route('account.addresses.index') }}" class="text-xs text-primary">مدیریت آدرس‌ها</a>
                    </div>

                    @forelse($addresses as $address)
                        <label class="flex gap-3 border border-line p-3 cursor-pointer {{ (int) $customerAddressId === (int) $address->id && ! $useNewAddress ? 'border-primary bg-surface-secondary' : '' }}">
                            <input type="radio" wire:model.live="customerAddressId" value="{{ $address->id }}" @click="$wire.set('useNewAddress', false)">
                            <span class="text-sm leading-6">
                                <strong>{{ $address->title ?: $address->recipient_name }}</strong>
                                @if($address->is_default)
                                    <span class="text-xs text-success">پیش‌فرض</span>
                                @endif
                                <br>
                                {{ $address->recipient_name }} — {{ $address->recipient_mobile }}<br>
                                {{ $address->province }}، {{ $address->city }}، {{ $address->address }}
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-ink-muted">آدرس ذخیره‌شده‌ای ندارید. آدرس جدید وارد کنید.</p>
                    @endforelse

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="useNewAddress">
                        ثبت آدرس جدید برای این سفارش
                    </label>

                    @if($useNewAddress || $addresses->isEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="label">نام گیرنده</label>
                                <input class="input" wire:model="recipientName">
                                @error('recipientName') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">موبایل گیرنده</label>
                                <input class="input" wire:model="recipientMobile" dir="ltr">
                                @error('recipientMobile') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">استان</label>
                                <input class="input" wire:model="province">
                                @error('province') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">شهر</label>
                                <input class="input" wire:model="city">
                                @error('city') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">کد پستی</label>
                                <input class="input" wire:model="postalCode" dir="ltr">
                                @error('postalCode') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">آدرس کامل</label>
                                <textarea class="input" rows="3" wire:model="address"></textarea>
                                @error('address') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                            <label class="sm:col-span-2 flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="saveNewAddress">
                                ذخیره این آدرس در حساب کاربری
                            </label>
                        </div>
                    @endif
                    @error('customerAddressId') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($step === 2)
                <div class="card-surface p-5 space-y-3">
                    <h2 class="text-lg font-medium">روش ارسال</h2>
                    @forelse($shippingMethods as $method)
                        <label class="flex gap-3 border border-line p-3 cursor-pointer">
                            <input type="radio" wire:model.live="shippingMethodId" value="{{ $method->id }}">
                            <span class="text-sm">
                                <strong>{{ $method->name }}</strong>
                                @if($method->description)
                                    <span class="block text-ink-muted mt-1">{{ $method->description }}</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-ink-muted">روش ارسال فعالی تعریف نشده است.</p>
                    @endforelse
                    @error('shippingMethodId') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            @if($step === 3)
                <div class="card-surface p-5 space-y-4">
                    <h2 class="text-lg font-medium">کد تخفیف</h2>
                    <div class="flex gap-2">
                        <input class="input" wire:model="couponCode" placeholder="کد تخفیف">
                        <button type="button" class="btn-secondary" wire:click="applyCoupon">اعمال</button>
                    </div>
                    @if($appliedDiscount)
                        <p class="text-sm text-success">تخفیف اعمال‌شده: {{ number_format($appliedDiscount) }} {{ $currencyLabel }}</p>
                        <button type="button" class="btn-ghost" wire:click="removeCoupon">حذف کد</button>
                    @endif
                </div>
            @endif

            @if($step >= 4)
                <div class="card-surface p-5 space-y-3">
                    <h2 class="text-lg font-medium">بازبینی سفارش</h2>
                    @foreach($cartItems as $item)
                        <div class="flex justify-between gap-4 text-sm border-b border-line py-3">
                            <div>
                                {{ $item->product?->name }}
                                @if($item->productVariant) <span class="text-ink-muted">({{ $item->productVariant->name }})</span> @endif
                                <div class="text-xs text-ink-muted">× {{ $item->quantity }}</div>
                            </div>
                            <div>{{ number_format($item->lineTotal()) }} {{ $currencyLabel }}</div>
                        </div>
                    @endforeach
                    <div>
                        <label class="label">یادداشت سفارش</label>
                        <textarea class="input" rows="2" wire:model="notes"></textarea>
                    </div>
                </div>
            @endif

            <div class="flex justify-between gap-3">
                @if($step > 1)
                    <button type="button" class="btn-secondary" wire:click="previousStep">قبلی</button>
                @else
                    <span></span>
                @endif

                @if($step < 5)
                    <button type="button" class="btn-primary" wire:click="nextStep">ادامه</button>
                @else
                    <button type="button" class="btn-primary" wire:click="placeOrder" wire:loading.attr="disabled">پرداخت آنلاین</button>
                @endif
            </div>
        </div>

        <aside class="card-surface p-5 h-fit space-y-3 text-sm">
            <h2 class="font-medium text-ink">خلاصه مبلغ</h2>
            <div class="flex justify-between"><span class="text-ink-muted">جمع جزء</span><span>{{ number_format($subtotal) }} {{ $currencyLabel }}</span></div>
            <div class="flex justify-between"><span class="text-ink-muted">تخفیف</span><span>{{ number_format($discount) }} {{ $currencyLabel }}</span></div>
            <div class="flex justify-between"><span class="text-ink-muted">ارسال</span><span>{{ number_format($shippingPrice) }} {{ $currencyLabel }}</span></div>
            <div class="flex justify-between border-t border-line pt-3 text-base font-semibold"><span>مبلغ نهایی</span><span>{{ number_format($total) }} {{ $currencyLabel }}</span></div>
        </aside>
    </div>
</div>
