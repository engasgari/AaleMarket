<div class="container-store py-10 sm:py-14">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-primary">سفارش‌های من</h1>
        <a href="{{ route('account.index') }}" class="text-sm text-primary">حساب کاربری</a>
    </div>

    @include('livewire.catalog.partials.flash')

    <div class="mt-8 space-y-4">
        @forelse ($orders as $order)
            <div wire:key="order-{{ $order->id }}" class="border border-line p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <a href="{{ route('account.orders.show', $order) }}" class="font-medium hover:text-primary">
                        {{ $order->order_number }}
                    </a>
                    <span class="text-sm text-ink-muted">{{ $order->created_at?->format('Y/m/d H:i') }}</span>
                </div>

                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span>
                        {{ $order->items->count() }} قلم
                        @if($order->isAwaitingPayment())
                            <span class="ms-2 text-warning">در انتظار پرداخت</span>
                        @endif
                    </span>
                    <span dir="ltr">{{ number_format($order->total_amount) }} {{ $currencyLabel }}</span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('account.orders.show', $order) }}" class="btn-secondary px-3 py-1.5 text-xs">
                        جزئیات
                    </a>

                    @if($order->isAwaitingPayment())
                        <button
                            type="button"
                            wire:click="payOnline({{ $order->id }})"
                            wire:loading.attr="disabled"
                            class="btn-primary px-3 py-1.5 text-xs"
                        >
                            پرداخت آنلاین
                        </button>
                        <button
                            type="button"
                            wire:click="deleteUnpaid({{ $order->id }})"
                            wire:confirm="سفارش پرداخت‌نشده حذف شود؟"
                            wire:loading.attr="disabled"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-danger transition-colors hover:bg-danger/10"
                            aria-label="حذف سفارش پرداخت‌نشده"
                            title="حذف سفارش"
                        >
                            <i class="fa-solid fa-trash-can text-base" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-ink-muted">هنوز سفارشی ثبت نکرده‌اید.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</div>
