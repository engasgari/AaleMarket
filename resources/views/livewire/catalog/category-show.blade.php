<div class="container-store py-10">
    <nav class="mb-6 text-sm text-ink-muted" aria-label="مسیر">
        <a href="{{ route('home') }}" class="hover:text-ink">خانه</a>
        <span class="mx-2">/</span>
        <a href="{{ route('categories.index') }}" class="hover:text-ink">دسته‌بندی‌ها</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ $category->name }}</span>
    </nav>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-ink">{{ $category->name }}</h1>
        @if($category->description)
            <p class="mt-2 max-w-2xl text-sm leading-7 text-ink-muted">{{ $category->description }}</p>
        @endif
    </div>

    @include('livewire.catalog.partials.flash')

    <div class="grid gap-8 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside>
            @include('livewire.catalog.partials.product-filters', ['brands' => $brands, 'showCategory' => true])
        </aside>
        <div>
            @include('livewire.catalog.partials.product-grid', [
                'products' => $products,
                'quickAdd' => true,
                'emptyTitle' => 'محصولی در این دسته نیست',
                'emptyText' => 'به‌زودی محصولات این دسته اضافه می‌شوند.',
            ])
        </div>
    </div>
</div>

@push('meta')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'دسته‌بندی‌ها', 'item' => route('categories.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $category->name, 'item' => route('categories.show', $category)],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush