<header class="border-b border-line bg-surface sticky top-0 z-40">
    <div class="container-store">
        <div class="flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="text-xl font-semibold tracking-tight text-primary">آله مارکت</span>
                <span class="hidden sm:inline text-xs text-ink-muted">AALE Market</span>
            </a>

            <form action="{{ route('search') }}" method="GET" class="hidden md:flex flex-1 max-w-xl mx-6">
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="جستجوی محصول، کد یا برند..."
                    class="input"
                    autocomplete="off"
                >
            </form>

            <nav class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('categories.index') }}" class="btn-ghost hidden sm:inline-flex">دسته‌بندی‌ها</a>
                @auth
                    <a href="{{ route('account.index') }}" class="btn-ghost">حساب کاربری</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost">ورود</a>
                @endauth
                @php
                    $cartCount = (int) app(\App\Modules\Cart\Services\CartService::class)
                        ->items()
                        ->sum('quantity');
                @endphp
                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex min-w-11 flex-col items-center justify-center gap-0.5 rounded-md px-1.5 py-1 text-ink transition-colors hover:bg-surface-secondary"
                    aria-label="سبد خرید"
                    title="سبد خرید"
                >
                    <i class="fa-solid fa-cart-shopping text-xl" aria-hidden="true"></i>
                    <span class="text-[11px] font-semibold leading-none text-black">
                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                    </span>
                </a>
                <a href="{{ route('search') }}" class="btn-ghost md:hidden" aria-label="جستجو">جستجو</a>
            </nav>
        </div>
    </div>
</header>
