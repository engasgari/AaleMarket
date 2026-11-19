<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-ink">برندها</h1>
            <p class="mt-1 text-sm text-ink-muted">مدیریت برندهای کاتالوگ</p>
        </div>
        @can('brands.create')
            <a href="{{ route('admin.brands.create') }}" class="btn-primary text-sm">برند جدید</a>
        @endcan
    </div>

    <div class="card-surface mb-4 rounded-md p-4">
        <label class="label" for="brand-search">جستجو</label>
        <input wire:model.live.debounce.300ms="search" id="brand-search" type="search" class="input" placeholder="نام برند">
    </div>

    <div class="card-surface overflow-hidden rounded-md">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-3 py-2 text-right font-medium">نام</th>
                    <th class="px-3 py-2 text-right font-medium">نامک</th>
                    <th class="px-3 py-2 text-right font-medium">محصولات</th>
                    <th class="px-3 py-2 text-right font-medium">وضعیت</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($brands as $brand)
                    <tr wire:key="brand-{{ $brand->id }}">
                        <td class="px-3 py-2 font-medium text-ink">{{ $brand->name }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $brand->slug }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $brand->products_count }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $brand->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="px-3 py-2">
                            @can('brands.update')
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn-ghost text-xs py-1 px-2">ویرایش</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-ink-muted">برندی یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-line px-3 py-3">{{ $brands->links() }}</div>
    </div>
</div>