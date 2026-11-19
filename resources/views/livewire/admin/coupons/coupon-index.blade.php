<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-ink">کدهای تخفیف</h1>
        <a href="{{ route('admin.coupons.create') }}" class="btn-primary">کد جدید</a>
    </div>
    <div class="mb-4 max-w-sm">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="جستجوی کد...">
    </div>
    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">کد</th>
                    <th class="px-4 py-3 text-right font-medium">نوع</th>
                    <th class="px-4 py-3 text-right font-medium">مقدار</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3" dir="ltr">{{ $coupon->code }}</td>
                        <td class="px-4 py-3">{{ $coupon->type }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $coupon->value }}</td>
                        <td class="px-4 py-3">{{ $coupon->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-ghost">ویرایش</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-muted">کدی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
</div>
