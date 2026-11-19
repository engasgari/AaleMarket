<div class="container-store py-10">
    @include('livewire.catalog.partials.flash')

    <div class="grid gap-10 lg:grid-cols-2">
        <div>
            <div class="card-surface aspect-square bg-surface-secondary">
                @php $main = $product->mainImageUrl(); @endphp
                @if($main)
                    <img src="{{ $main }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-6">
                @else
                    <div class="flex h-full items-center justify-center text-sm text-ink-muted">بدون تصویر</div>
                @endif
            </div>
            @if($product->images->count() > 1)
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach($product->images as $image)
                        <div class="card-surface aspect-square bg-surface-secondary">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt ?: $product->name }}" class="h-full w-full object-contain p-2">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <p class="text-sm text-ink-muted">{{ $product->brand?->name }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-ink">{{ $product->name }}</h1>
            <p class="mt-2 text-xs text-ink-muted" dir="ltr">SKU: {{ $product->sku }}</p>

            <div class="mt-6">
                <x-price :amount="$this->displayPrice" :compare="$this->listPrice !== $this->displayPrice ? $this->listPrice : null" />
            </div>

            <p class="mt-4 text-sm {{ $available > 0 ? 'text-success' : 'text-danger' }}">
                {{ $available > 0 ? 'موجود در انبار ('.$available.')' : 'ناموجود' }}
            </p>

            @if($product->variants->isNotEmpty())
                <div class="mt-6">
                    <label class="label">انتخاب مدل</label>
                    <select wire:model.live="variantId" class="input">
                        @foreach($product->variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->name ?: $variant->sku }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="mt-6 max-w-xs">
                <label class="label" for="quantity">تعداد</label>
                <input id="quantity" type="number" min="1" wire:model="quantity" class="input" dir="ltr">
                @error('quantity') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" wire:click="addToCart" class="btn-primary" @disabled($available < 1) wire:loading.attr="disabled">
                    افزودن به سبد
                </button>
                <a href="{{ route('cart.index') }}" class="btn-secondary">مشاهده سبد</a>
            </div>

            @if($product->short_description)
                <p class="mt-8 text-sm leading-7 text-ink-muted">{{ $product->short_description }}</p>
            @endif
        </div>
    </div>

    @if($product->description)
        <section class="mt-12 border-t border-line pt-10">
            <h2 class="text-lg font-semibold text-ink">توضیحات</h2>
            <div class="prose-store mt-4 text-sm leading-8 text-ink-muted whitespace-pre-line">{{ $product->description }}</div>
        </section>
    @endif

    @push('meta')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->short_description,
                'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'IRR',
                    'price' => $this->displayPrice,
                    'availability' => $available > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                ],
            ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
</div>
