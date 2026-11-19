<div class="container-store py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-ink">محصولات</h1>
        <p class="mt-1 text-sm text-ink-muted">جستجو، فیلتر و مشاهده محصولات آله مارکت</p>
    </div>

    @include('livewire.catalog.partials.flash')
    @include('livewire.catalog.partials.product-filters')

    <div class="mt-8">
        @include('livewire.catalog.partials.product-grid', ['products' => $products, 'quickAdd' => true])
    </div>
</div>
