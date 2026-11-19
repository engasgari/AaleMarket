<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-ink">موجودی</h1>
        <p class="mt-1 text-sm text-ink-muted">مشاهده و تعدیل موجودی انبار</p>
    </div>

    @if (session('status'))
        <div class="mb-4 border border-line bg-surface px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    <div class="mb-4 grid gap-3 sm:grid-cols-2 max-w-2xl">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="جستجوی محصول...">
        <select wire:model.live="warehouseId" class="input">
            <option value="">همه انبارها</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">محصول</th>
                    <th class="px-4 py-3 text-right font-medium">انبار</th>
                    <th class="px-4 py-3 text-right font-medium">موجودی</th>
                    <th class="px-4 py-3 text-right font-medium">رزرو</th>
                    <th class="px-4 py-3 text-right font-medium">قابل فروش</th>
                    <th class="px-4 py-3 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    <tr class="border-b border-line" wire:key="stock-{{ $stock->id }}">
                        <td class="px-4 py-3">
                            {{ $stock->product?->name }}
                            @if($stock->productVariant)<div class="text-xs text-ink-muted">{{ $stock->productVariant->name }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ $stock->warehouse?->name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $stock->quantity }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $stock->reserved_quantity }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ $stock->available_quantity }}</td>
                        <td class="px-4 py-3 space-x-1 space-x-reverse">
                            <button type="button" class="btn-ghost" wire:click="openAdjust({{ $stock->id }})">تعدیل</button>
                            <button type="button" class="btn-ghost" wire:click="openStockIn({{ $stock->id }})">ورود</button>
                        </td>
                    </tr>
                    @if($activeStockId === $stock->id)
                        <tr class="bg-surface-secondary">
                            <td colspan="6" class="px-4 py-4">
                                @if($mode === 'adjust')
                                    <div class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="label">موجودی جدید</label>
                                            <input type="number" min="0" class="input w-32" wire:model="adjustNewQuantity" dir="ltr">
                                        </div>
                                        <div class="flex-1 min-w-48">
                                            <label class="label">دلیل</label>
                                            <input class="input" wire:model="adjustReason">
                                        </div>
                                        <button type="button" class="btn-primary" wire:click="submitAdjust">ثبت</button>
                                        <button type="button" class="btn-secondary" wire:click="closePanel">انصراف</button>
                                    </div>
                                @elseif($mode === 'stock_in')
                                    <div class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="label">تعداد ورود</label>
                                            <input type="number" min="1" class="input w-32" wire:model="stockInQuantity" dir="ltr">
                                        </div>
                                        <div class="flex-1 min-w-48">
                                            <label class="label">یادداشت</label>
                                            <input class="input" wire:model="stockInNotes">
                                        </div>
                                        <button type="button" class="btn-primary" wire:click="submitStockIn">ثبت</button>
                                        <button type="button" class="btn-secondary" wire:click="closePanel">انصراف</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-ink-muted">موجودی‌ای یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $stocks->links() }}</div>
</div>
