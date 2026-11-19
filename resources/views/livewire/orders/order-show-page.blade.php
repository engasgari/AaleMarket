<div class="container-store py-10 sm:py-14">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-primary">سفارش {{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-ink-muted">وضعیت: {{ $statusLabel }}</p>
        </div>
        <a href="{{ route('account.orders.index') }}" class="text-sm text-primary">بازگشت به لیست</a>
    </div>

    @include('livewire.catalog.partials.flash')

    @if($order->isAwaitingPayment())
        <div class="mt-6 flex flex-wrap items-center gap-3 border border-line bg-surface-secondary px-4 py-4">
            <p class="me-auto text-sm text-ink">این سفارش هنوز پرداخت نشده است.</p>
            <button
                type="button"
                wire:click="payOnline"
                wire:loading.attr="disabled"
                class="btn-primary"
            >
                پرداخت آنلاین
            </button>
            <button
                type="button"
                wire:click="deleteUnpaid"
                wire:confirm="سفارش پرداخت‌نشده حذف شود؟"
                wire:loading.attr="disabled"
                class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-danger/30 text-danger transition-colors hover:bg-danger/10"
                aria-label="حذف سفارش پرداخت‌نشده"
                title="حذف سفارش"
            >
                <i class="fa-solid fa-trash-can text-base" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="border border-line p-6">
            <h2 class="font-medium">اقلام</h2>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between border-b border-line pb-2">
                        <span>{{ $item->product_name }} @if($item->variant_name)<span class="text-ink-muted">({{ $item->variant_name }})</span>@endif × {{ $item->quantity }}</span>
                        <span dir="ltr">{{ number_format($item->total_price) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-ink-muted">جمع</dt><dd dir="ltr">{{ number_format($order->subtotal) }}</dd></div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between"><dt>تخفیف</dt><dd dir="ltr">−{{ number_format($order->discount_amount) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-ink-muted">ارسال ({{ $order->shipping_method_name }})</dt><dd dir="ltr">{{ number_format($order->shipping_amount) }}</dd></div>
                <div class="flex justify-between font-semibold pt-2 border-t border-line"><dt>جمع کل</dt><dd dir="ltr">{{ number_format($order->total_amount) }} {{ $currencyLabel }}</dd></div>
            </dl>
        </section>

        <section class="border border-line p-6 text-sm space-y-4">
            @if ($order->address)
                <div>
                    <h2 class="font-medium">آدرس</h2>
                    <p class="mt-2">{{ $order->address->recipient_name }} — {{ $order->address->recipient_mobile }}</p>
                    <p class="text-ink-muted">{{ $order->address->province }}، {{ $order->address->city }}</p>
                    <p>{{ $order->address->address }}</p>
                    <p class="text-ink-muted" dir="ltr">کد پستی: {{ $order->address->postal_code }}</p>
                </div>
            @endif

            @if ($order->statusHistories->isNotEmpty())
                <div>
                    <h2 class="font-medium">تاریخچه وضعیت</h2>
                    <ul class="mt-2 space-y-2 text-ink-muted">
                        @foreach ($order->statusHistories as $history)
                            <li>{{ $history->created_at?->format('Y/m/d H:i') }} — {{ $history->note ?: $history->to_status }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    </div>
</div>
