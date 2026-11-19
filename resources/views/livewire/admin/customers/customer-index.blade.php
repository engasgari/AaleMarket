<div>
    <div class="mb-6"><h1 class="text-xl font-semibold text-ink">مشتریان</h1></div>
    <div class="mb-4 max-w-sm">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="نام یا موبایل...">
    </div>
    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">نام</th>
                    <th class="px-4 py-3 text-right font-medium">موبایل</th>
                    <th class="px-4 py-3 text-right font-medium">سفارش‌ها</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3">{{ $customer->name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $customer->mobile }}</td>
                        <td class="px-4 py-3">{{ $customer->orders_count }}</td>
                        <td class="px-4 py-3">{{ $customer->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-ink-muted">مشتری‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
</div>
