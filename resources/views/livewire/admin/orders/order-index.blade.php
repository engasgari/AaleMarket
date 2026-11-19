<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-ink">سفارش‌ها</h1>
    </div>

    <div class="mb-4 grid gap-3 sm:grid-cols-2 max-w-2xl">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="شماره سفارش یا موبایل...">
        <select wire:model.live="status" class="input">
            <option value="">همه وضعیت‌ها</option>
            @foreach($statuses as $st)
                <option value="{{ $st->value }}">{{ \App\Modules\Admin\Livewire\Orders\OrderIndex::statusLabel($st) }}</option>
            @endforeach
        </select>
    </div>

    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">شماره</th>
                    <th class="px-4 py-3 text-right font-medium">مشتری</th>
                    <th class="px-4 py-3 text-right font-medium">مبلغ</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium">تاریخ</th>
                    <th class="px-4 py-3 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3" dir="ltr">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">{{ $order->user?->name }} <span class="text-xs text-ink-muted" dir="ltr">{{ $order->user?->mobile }}</span></td>
                        <td class="px-4 py-3">{{ number_format($order->total_amount) }} ریال</td>
                        <td class="px-4 py-3">{{ \App\Modules\Admin\Livewire\Orders\OrderIndex::statusLabel($order->status) }}</td>
                        <td class="px-4 py-3 text-xs">{{ \App\Support\Helpers\JalaliDate::format($order->created_at) }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="btn-ghost">جزئیات</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-ink-muted">سفارشی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
