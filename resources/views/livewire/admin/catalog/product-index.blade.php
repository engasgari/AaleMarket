<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-ink">محصولات</h1>
            <p class="mt-1 text-sm text-ink-muted">مدیریت کاتالوگ محصولات</p>
        </div>
        @can('products.create')
            <a href="{{ route('admin.products.create') }}" class="btn-primary text-sm">محصول جدید</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-line bg-surface px-3 py-2 text-sm text-ink">
            {{ session('status') }}
        </div>
    @endif

    <div class="card-surface mb-4 rounded-md p-4">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label class="label" for="search">جستجو</label>
                <input wire:model.live.debounce.300ms="search" id="search" type="search" class="input" placeholder="نام، SKU یا کد محصول">
            </div>
            <div>
                <label class="label" for="status">وضعیت</label>
                <select wire:model.live="statusFilter" id="status" class="input">
                    <option value="">همه</option>
                    <option value="published">منتشر شده</option>
                    <option value="draft">پیش‌نویس</option>
                    <option value="archived">آرشیو</option>
                </select>
            </div>
        </div>

        @can('products.update')
            <div class="mt-3 flex flex-wrap gap-2 border-t border-line pt-3">
                <button type="button" wire:click="bulkPublish" class="btn-secondary text-sm" @disabled(count($selected) === 0)>انتشار گروهی</button>
                <button type="button" wire:click="bulkDraft" class="btn-ghost text-sm" @disabled(count($selected) === 0)>پیش‌نویس گروهی</button>
            </div>
        @endcan
    </div>

    <div class="card-surface overflow-hidden rounded-md">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                    <tr>
                        @can('products.update')
                            <th class="w-10 px-3 py-2 text-right font-medium"></th>
                        @endcan
                        <th class="px-3 py-2 text-right font-medium">نام</th>
                        <th class="px-3 py-2 text-right font-medium">SKU</th>
                        <th class="px-3 py-2 text-right font-medium">برند</th>
                        <th class="px-3 py-2 text-right font-medium">قیمت (ریال)</th>
                        <th class="px-3 py-2 text-right font-medium">وضعیت</th>
                        <th class="px-3 py-2 text-right font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-surface-secondary/60">
                            @can('products.update')
                                <td class="px-3 py-2">
                                    <input type="checkbox" wire:model="selected" value="{{ $product->id }}" class="rounded border-line">
                                </td>
                            @endcan
                            <td class="px-3 py-2 font-medium text-ink">{{ $product->name }}</td>
                            <td class="px-3 py-2 text-ink-muted">{{ $product->sku }}</td>
                            <td class="px-3 py-2 text-ink-muted">{{ $product->brand?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-ink-muted">{{ number_format($product->finalPrice()) }}</td>
                            <td class="px-3 py-2">
                                <span @class([
                                    'inline-flex rounded px-2 py-0.5 text-xs',
                                    'bg-surface-secondary text-ink' => $product->status === 'draft',
                                    'text-primary' => $product->status === 'published',
                                    'text-ink-muted' => $product->status === 'archived',
                                ])>{{ $product->status }}</span>
                            </td>
                            <td class="px-3 py-2 text-left">
                                @can('products.update')
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-ghost text-xs py-1 px-2">ویرایش</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-ink-muted">محصولی یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-line px-3 py-3">
            {{ $products->links() }}
        </div>
    </div>
</div>