<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink">انبارها</h1>
            <p class="mt-1 text-sm text-ink-muted">مدیریت انبارهای فیزیکی</p>
        </div>
        <a href="{{ route('admin.warehouses.create') }}" class="btn-primary">انبار جدید</a>
    </div>

    <div class="mb-4 max-w-sm">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="جستجوی نام یا کد...">
    </div>

    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">نام</th>
                    <th class="px-4 py-3 text-right font-medium">کد</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3">
                            {{ $warehouse->name }}
                            @if($warehouse->is_default)<span class="ms-2 text-xs text-primary">پیش‌فرض</span>@endif
                        </td>
                        <td class="px-4 py-3" dir="ltr">{{ $warehouse->code }}</td>
                        <td class="px-4 py-3">{{ $warehouse->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn-ghost">ویرایش</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-ink-muted">انباری ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $warehouses->links() }}</div>
</div>
