<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? View::yieldContent('title', 'پنل مدیریت | '.config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-surface-secondary text-ink font-sans antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-56 shrink-0 border-l border-line bg-surface lg:block">
            <div class="border-b border-line px-4 py-5">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-primary">پنل مدیریت آله مارکت</a>
            </div>
            <nav class="space-y-0.5 p-3 text-sm">
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'داشبورد', 'active' => request()->routeIs('admin.dashboard')],
                        ['route' => 'admin.products.index', 'label' => 'محصولات', 'active' => request()->routeIs('admin.products.*')],
                        ['route' => 'admin.categories.index', 'label' => 'دسته‌بندی‌ها', 'active' => request()->routeIs('admin.categories.*')],
                        ['route' => 'admin.brands.index', 'label' => 'برندها', 'active' => request()->routeIs('admin.brands.*')],
                        ['route' => 'admin.warehouses.index', 'label' => 'انبارها', 'active' => request()->routeIs('admin.warehouses.*')],
                        ['route' => 'admin.inventory.index', 'label' => 'موجودی', 'active' => request()->routeIs('admin.inventory.index')],
                        ['route' => 'admin.inventory.movements', 'label' => 'گردش انبار', 'active' => request()->routeIs('admin.inventory.movements')],
                        ['route' => 'admin.orders.index', 'label' => 'سفارش‌ها', 'active' => request()->routeIs('admin.orders.*')],
                        ['route' => 'admin.customers.index', 'label' => 'مشتریان', 'active' => request()->routeIs('admin.customers.*')],
                        ['route' => 'admin.coupons.index', 'label' => 'کدهای تخفیف', 'active' => request()->routeIs('admin.coupons.*')],
                        ['route' => 'admin.shipping.index', 'label' => 'روش‌های ارسال', 'active' => request()->routeIs('admin.shipping.*')],
                        ['route' => 'admin.payments.index', 'label' => 'پرداخت‌ها', 'active' => request()->routeIs('admin.payments.*')],
                        ['route' => 'admin.sms-logs.index', 'label' => 'پیامک‌ها', 'active' => request()->routeIs('admin.sms-logs.*')],
                        ['route' => 'admin.activity-logs.index', 'label' => 'گزارش فعالیت', 'active' => request()->routeIs('admin.activity-logs.*')],
                        ['route' => 'admin.settings.index', 'label' => 'تنظیمات', 'active' => request()->routeIs('admin.settings.*')],
                    ];
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       @class([
                           'block rounded-md px-3 py-2 transition-colors',
                           'bg-surface-secondary font-medium text-primary' => $link['active'],
                           'text-ink-muted hover:bg-surface-secondary hover:text-ink' => ! $link['active'],
                       ])>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="border-b border-line bg-surface">
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <div class="text-sm text-ink-muted lg:hidden">پنل مدیریت</div>
                    <div class="flex items-center gap-3 ms-auto">
                        <span class="hidden text-sm text-ink-muted sm:inline">{{ auth()->user()?->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-ghost text-sm py-1.5 px-3">خروج</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>