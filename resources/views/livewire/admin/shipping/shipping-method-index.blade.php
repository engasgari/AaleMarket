<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-ink">روش‌های ارسال</h1>
        <a href="{{ route('admin.shipping.create') }}" class="btn-primary">روش جدید</a>
    </div>
    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">نام</th>
                    <th class="px-4 py-3 text-right font-medium">کد</th>
                    <th class="px-4 py-3 text-right font-medium">نرخ‌ها</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($methods as $method)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3">{{ $method->name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $method->code }}</td>
                        <td class="px-4 py-3">{{ $method->rates_count }}</td>
                        <td class="px-4 py-3">{{ $method->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.shipping.edit', $method) }}" class="btn-ghost">ویرایش</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-muted">روش ارسالی نیست.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $methods->links() }}</div>
</div>
