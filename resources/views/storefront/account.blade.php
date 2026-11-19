@extends('layouts.storefront')

@section('title', 'حساب کاربری | آله مارکت')

@section('content')
    <div class="container-store py-10">
        <h1 class="text-2xl font-semibold text-ink">حساب کاربری</h1>
        <p class="mt-2 text-sm text-ink-muted">خوش آمدید، {{ auth()->user()->name }}</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('account.profile') }}" class="card-surface p-5 hover:bg-surface-secondary">
                <div class="text-sm font-medium">تنظیمات پروفایل</div>
                <div class="mt-2 text-xs text-ink-muted">نام، ایمیل، موبایل و رمز عبور</div>
            </a>
            <a href="{{ route('account.addresses.index') }}" class="card-surface p-5 hover:bg-surface-secondary">
                <div class="text-sm font-medium">مدیریت آدرس‌ها</div>
                <div class="mt-2 text-xs text-ink-muted">ذخیره و انتخاب آدرس ارسال</div>
            </a>
            <a href="{{ route('account.orders.index') }}" class="card-surface p-5 hover:bg-surface-secondary">
                <div class="text-sm font-medium">سفارش‌های من</div>
                <div class="mt-2 text-xs text-ink-muted">پیگیری وضعیت سفارش‌ها</div>
            </a>
            <a href="{{ route('cart.index') }}" class="card-surface p-5 hover:bg-surface-secondary">
                <div class="text-sm font-medium">سبد خرید</div>
                <div class="mt-2 text-xs text-ink-muted">مشاهده کالاهای انتخاب‌شده</div>
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->roles()->exists())
                <a href="{{ route('admin.dashboard') }}" class="card-surface p-5 hover:bg-surface-secondary">
                    <div class="text-sm font-medium">پنل مدیریت</div>
                    <div class="mt-2 text-xs text-ink-muted">مدیریت فروشگاه</div>
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="card-surface w-full p-5 text-right hover:bg-surface-secondary">
                    <div class="text-sm font-medium">خروج</div>
                    <div class="mt-2 text-xs text-ink-muted">پایان نشست کاربری</div>
                </button>
            </form>
        </div>
    </div>
@endsection
