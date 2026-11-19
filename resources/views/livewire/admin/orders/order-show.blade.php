<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-ink">سفارش {{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-ink-muted">{{ $order->user?->name }} — <span dir="ltr">{{ $order->user?->mobile }}</span></p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">بازگشت</a>
    </div>

    @if (session('status'))
        <div class="border border-line bg-surface px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    <div class="card-surface p-5 grid gap-3 sm:grid-cols-3 text-sm">
        <div><span class="text-ink-muted">جمع جزء</span><div class="font-medium">{{ number_format($order->subtotal) }} ریال</div></div>
        <div><span class="text-ink-muted">ارسال</span><div class="font-medium">{{ number_format($order->shipping_amount) }} ریال</div></div>
        <div><span class="text-ink-muted">نهایی</span><div class="font-medium">{{ number_format($order->total_amount) }} ریال</div></div>
    </div>

    <div class="card-surface p-5 space-y-3">
        <h2 class="font-medium">آیتم‌ها</h2>
        @foreach($order->items as $item)
            <div class="flex justify-between gap-4 border-b border-line py-2 text-sm">
                <div>{{ $item->product_name }} × {{ $item->quantity }}</div>
                <div>{{ number_format($item->total_price) }} ریال</div>
            </div>
        @endforeach
    </div>

    <div class="card-surface p-5 space-y-3 max-w-xl">
        <h2 class="font-medium">تغییر وضعیت</h2>
        <select class="input" wire:model="newStatus">
            @foreach($statuses as $st)
                <option value="{{ $st->value }}">{{ \App\Modules\Admin\Livewire\Orders\OrderIndex::statusLabel($st) }}</option>
            @endforeach
        </select>
        <input class="input" wire:model="statusNote" placeholder="یادداشت (اختیاری)">
        <button type="button" class="btn-primary" wire:click="updateStatus">ذخیره وضعیت</button>
    </div>

    <div class="card-surface p-5 space-y-2 text-sm">
        <h2 class="font-medium mb-2">تاریخچه وضعیت</h2>
        @forelse($order->statusHistories as $history)
            <div class="border-b border-line py-2">
                <span dir="ltr">{{ $history->from_status }} → {{ $history->to_status }}</span>
                <div class="text-xs text-ink-muted">{{ \App\Support\Helpers\JalaliDate::format($history->created_at) }} — {{ $history->note }}</div>
            </div>
        @empty
            <p class="text-ink-muted">تاریخچه‌ای نیست.</p>
        @endforelse
    </div>

    @if($order->payments->isNotEmpty())
        <div class="card-surface p-5 space-y-2 text-sm">
            <h2 class="font-medium mb-2">پرداخت‌ها</h2>
            @foreach($order->payments as $payment)
                <div class="border-b border-line py-2 flex justify-between">
                    <span>{{ $payment->gateway }} — {{ $payment->status?->value ?? $payment->status }}</span>
                    <span>{{ number_format($payment->amount) }} ریال</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
