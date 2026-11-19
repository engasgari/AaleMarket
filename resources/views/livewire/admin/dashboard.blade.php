<div>
    <div class="mb-6">
        <h1 class="text-lg font-semibold text-ink">داشبورد</h1>
        <p class="mt-1 text-sm text-ink-muted">خلاصه وضعیت فروشگاه</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card-surface rounded-md p-4">
            <p class="text-sm text-ink-muted">سفارش‌ها</p>
            <p class="mt-2 text-2xl font-semibold text-ink">{{ number_format($ordersCount) }}</p>
        </div>
        <div class="card-surface rounded-md p-4">
            <p class="text-sm text-ink-muted">محصولات</p>
            <p class="mt-2 text-2xl font-semibold text-ink">{{ number_format($productsCount) }}</p>
        </div>
        <div class="card-surface rounded-md p-4">
            <p class="text-sm text-ink-muted">کاربران</p>
            <p class="mt-2 text-2xl font-semibold text-ink">{{ number_format($usersCount) }}</p>
        </div>
    </div>
</div>