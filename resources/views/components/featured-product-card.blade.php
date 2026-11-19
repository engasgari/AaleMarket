@props([
    'product',
    'showQuickAdd' => true,
])

@php
    /** @var \App\Modules\Catalog\Models\Product $product */
    use App\Modules\Inventory\Services\InventoryService;

    $imageUrl = $product->mainImageUrl();
    $finalPrice = $product->finalPrice();
    $listPrice = $product->hasDiscount() ? (int) $product->selling_price : null;
    $available = app(InventoryService::class)->availableQuantity((int) $product->id);
    $inStock = $available > 0;

    $kind = match (true) {
        str_contains($product->slug, 'priz') || str_contains($product->name, 'پریز') => 'outlet',
        str_contains($product->slug, 'ghalade') || str_contains($product->name, 'قلاده') => 'collar',
        str_contains($product->slug, 'ups') || str_contains(mb_strtolower($product->name), 'ups') => 'ups',
        default => 'default',
    };

    $visual = match ($kind) {
        'outlet' => ['label' => 'پریز', 'tone' => 'from-[#1B3A5F] to-[#0F1C33]'],
        'collar' => ['label' => 'قلاده', 'tone' => 'from-[#1F4D4A] to-[#0F2A28]'],
        'ups' => ['label' => 'UPS', 'tone' => 'from-[#4A3A1B] to-[#2A210F]'],
        default => ['label' => 'آله', 'tone' => 'from-primary to-primary-dark'],
    };
@endphp

<article
    {{ $attributes->merge(['class' => 'group card-surface flex h-full flex-col overflow-hidden transition-transform duration-300 hover:-translate-y-1']) }}
    wire:key="featured-product-{{ $product->id }}"
>
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden bg-surface-secondary">
        @if($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $product->name }}"
                class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-[1.03]"
                loading="lazy"
            >
        @else
            <div class="relative flex h-full items-center justify-center bg-gradient-to-br {{ $visual['tone'] }} text-white">
                <svg viewBox="0 0 24 24" class="h-16 w-16 opacity-90" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    @if($kind === 'outlet')
                        <rect x="5" y="8" width="14" height="11" rx="2" />
                        <path d="M9 3v4M15 3v4M9 13h6" stroke-linecap="round" />
                    @elseif($kind === 'collar')
                        <circle cx="12" cy="10" r="5" />
                        <circle cx="12" cy="10" r="1.5" />
                        <path d="M8 14.5c-1.5 1.2-2.5 2.7-2.5 4M16 14.5c1.5 1.2 2.5 2.7 2.5 4" stroke-linecap="round" />
                    @elseif($kind === 'ups')
                        <path d="M13 2L4 14h7l-1 8 10-14h-7l1-6z" stroke-linejoin="round" />
                    @else
                        <rect x="4" y="7" width="16" height="10" rx="1" />
                        <path d="M8 4h8M8 20h8" stroke-linecap="round" />
                    @endif
                </svg>
                <span class="absolute bottom-4 left-4 text-xs tracking-wide text-white/70">{{ $visual['label'] }}</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-3 p-5 text-center">
        @if($product->brand)
            <p class="text-xs text-ink-muted">{{ $product->brand->name }}</p>
        @endif

        <h3 class="text-base font-semibold leading-7 text-ink">
            <a href="{{ route('products.show', $product) }}" class="hover:text-primary">{{ $product->name }}</a>
        </h3>

        @if($product->short_description)
            <p class="line-clamp-2 text-sm leading-6 text-ink-muted">{{ $product->short_description }}</p>
        @endif

        <div class="mt-auto flex flex-col items-center gap-3 pt-2">
            <x-price :amount="$finalPrice" :compare="$listPrice" class="justify-center" />

            <div class="flex w-full items-center justify-center gap-3">
                @if($inStock)
                    <span class="text-xs text-success">موجود</span>
                @else
                    <span class="text-xs text-danger">ناموجود</span>
                @endif

                @if($showQuickAdd && $inStock)
                    <button
                        type="button"
                        wire:click="quickAdd({{ $product->id }})"
                        class="btn-primary px-4 py-2 text-xs"
                    >
                        افزودن به سبد
                    </button>
                @endif
            </div>
        </div>
    </div>
</article>
