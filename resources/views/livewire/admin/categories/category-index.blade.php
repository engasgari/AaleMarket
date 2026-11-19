<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-ink">دسته‌بندی‌ها</h1>
            <p class="mt-1 text-sm text-ink-muted">ساختار درختی دسته‌ها</p>
        </div>
        @can('categories.create')
            <a href="{{ route('admin.categories.create') }}" class="btn-primary text-sm">دسته جدید</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-line bg-surface px-3 py-2 text-sm text-ink">{{ session('status') }}</div>
    @endif

    <div class="card-surface mb-4 rounded-md p-4">
        <label class="label" for="cat-search">جستجو</label>
        <input wire:model.live.debounce.300ms="search" id="cat-search" type="search" class="input" placeholder="نام دسته">
    </div>

    <div class="card-surface overflow-hidden rounded-md">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-3 py-2 text-right font-medium">نام</th>
                    <th class="px-3 py-2 text-right font-medium">والد</th>
                    <th class="px-3 py-2 text-right font-medium">وضعیت</th>
                    <th class="px-3 py-2 text-right font-medium">ترتیب</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($categories as $category)
                    <tr wire:key="cat-{{ $category->id }}" class="hover:bg-surface-secondary/60">
                        <td class="px-3 py-2 font-medium text-ink">{{ $category->name }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $category->parent?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $category->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td class="px-3 py-2 text-ink-muted">{{ $category->sort_order }}</td>
                        <td class="px-3 py-2">
                            @can('categories.update')
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-ghost text-xs py-1 px-2">ویرایش</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-ink-muted">دسته‌ای یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-line px-3 py-3">{{ $categories->links() }}</div>
    </div>
</div>