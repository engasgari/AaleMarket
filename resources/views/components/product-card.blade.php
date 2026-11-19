@props([
    'product',
    'showQuickAdd' => false,
])

@php
    /** @var \App\Modules\Catalog\Models\Product $product */
    use App\Modules\Inventory\Services\InventoryService;
    $imageUrl = $product->mainImageUrl();
    $finalPrice = $product->finalPrice();
    $listPrice = $product->hasDiscount() ? (int) $product->selling_price : null;
    $available = app(InventoryService::class)->availableQuantity((int) $product->id);
    $inStock = $available > 0;
@endphp

<article
    {{ $attributes->merge(['class' => 'card-surface flex h-full flex-col overflow-hidden']) }}
    wire:key="product-card-{{ $product->id }}"
>
    <a href="{{ route('products.show', $product) }}" class="block aspect-[4/3] bg-surface-secondary">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-3" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center text-xs text-ink-muted">بدون تصویر</div>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-3 p-4">
        @if($product->brand)
            <p class="text-xs text-ink-muted">{{ $product->brand->name }}</p>
        @endif

        <h3 class="line-clamp-2 min-h-[2.75rem] text-sm font-medium leading-6 text-ink">
            <a href="{{ route('products.show', $product) }}" class="hover:text-primary">{{ $product->name }}</a>
        </h3>

        <x-price :amount="$finalPrice" :compare="$listPrice" class="mt-auto" />

        <div class="flex items-center justify-between gap-2 pt-1">
            @if($inStock)
                <span class="text-xs text-success">موجود ({{ $available }})</span>
            @else
                <span class="text-xs text-danger">ناموجود</span>
            @endif

            @if($showQuickAdd && $inStock)
                <button
                    type="button"
                    wire:click="quickAdd({{ $product->id }})"
                    class="btn-secondary px-3 py-1.5 text-xs"
                >
                    افزودن
                </button>
            @endif
        </div>
    </div>
</article>