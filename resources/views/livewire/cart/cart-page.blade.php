<div class="container-store py-10">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-ink">سبد خرید</h1>
            <p class="mt-1 text-sm text-ink-muted">بررسی کالاها قبل از تسویه حساب</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">ادامه خرید</a>
    </div>

    @include('livewire.catalog.partials.flash')

    @if($items->isEmpty())
        <div class="card-surface px-6 py-14 text-center">
            <p class="text-base font-medium text-ink">سبد خرید خالی است</p>
            <a href="{{ route('products.index') }}" class="btn-primary mt-6 inline-flex">مشاهده محصولات</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($items as $item)
                <div class="card-surface p-4 sm:p-5" wire:key="cart-item-{{ $item->id }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex gap-4">
                            <div class="h-20 w-20 shrink-0 bg-surface-secondary">
                                @if($item->product?->mainImageUrl())
                                    <img src="{{ $item->product->mainImageUrl() }}" alt="" class="h-full w-full object-contain p-2">
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('products.show', $item->product) }}" class="text-sm font-medium text-ink hover:text-primary">
                                    {{ $item->product?->name }}
                                </a>
                                @if($item->productVariant)
                                    <p class="mt-1 text-xs text-ink-muted">{{ $item->productVariant->name }}</p>
                                @endif
                                <div class="mt-2">
                                    <x-price :amount="$item->unit_price" />
                                </div>
                                @if(isset($stockErrors[$item->id]))
                                    <p class="mt-2 text-xs text-danger">{{ $stockErrors[$item->id] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                min="1"
                                value="{{ $item->quantity }}"
                                class="input w-24"
                                dir="ltr"
                                wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                            >
                            <div class="min-w-28 text-sm font-medium">
                                {{ \App\Support\Helpers\Money::format($item->lineTotal()) }}
                            </div>
                            <button type="button" class="btn-ghost text-danger" wire:click="remove({{ $item->id }})">حذف</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-col items-stretch justify-between gap-4 border-t border-line pt-6 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm text-ink-muted">جمع جزء</p>
                <p class="mt-1 text-xl font-semibold text-ink">{{ \App\Support\Helpers\Money::format($subtotal) }}</p>
            </div>
            <a href="{{ route('checkout') }}" class="btn-primary">ادامه فرآیند خرید</a>
        </div>
    @endif
</div>
