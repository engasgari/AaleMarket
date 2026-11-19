<div>
    <div class="mb-6"><h1 class="text-xl font-semibold text-ink">پرداخت‌ها</h1></div>
    <div class="mb-4 max-w-sm">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="Authority / مرجع / شماره سفارش...">
    </div>
    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">سفارش</th>
                    <th class="px-4 py-3 text-right font-medium">مبلغ</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium">مرجع</th>
                    <th class="px-4 py-3 text-right font-medium">زمان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3" dir="ltr">{{ $payment->order?->order_number }}</td>
                        <td class="px-4 py-3">{{ number_format($payment->amount) }} ریال</td>
                        <td class="px-4 py-3">{{ $payment->status?->value ?? $payment->status }}</td>
                        <td class="px-4 py-3 text-xs" dir="ltr">{{ $payment->reference_id ?: $payment->authority }}</td>
                        <td class="px-4 py-3 text-xs">{{ \App\Support\Helpers\JalaliDate::format($payment->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-muted">پرداختی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
