<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-ink">حرکت‌های موجودی</h1>
        <p class="mt-1 text-sm text-ink-muted">تاریخچه ورود، خروج و تعدیل</p>
    </div>

    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">زمان</th>
                    <th class="px-4 py-3 text-right font-medium">نوع</th>
                    <th class="px-4 py-3 text-right font-medium">محصول</th>
                    <th class="px-4 py-3 text-right font-medium">انبار</th>
                    <th class="px-4 py-3 text-right font-medium">تعداد</th>
                    <th class="px-4 py-3 text-right font-medium">قبل/بعد</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3 text-xs">{{ \App\Support\Helpers\JalaliDate::format($movement->created_at) }}</td>
                        <td class="px-4 py-3">{{ $movement->type }}</td>
                        <td class="px-4 py-3">{{ $movement->product?->name }}</td>
                        <td class="px-4 py-3">{{ $movement->warehouse?->name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $movement->quantity }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-ink-muted">حرکتی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
</div>
