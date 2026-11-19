@props(['products', 'quickAdd' => false, 'emptyTitle' => 'محصولی یافت نشد', 'emptyText' => 'فیلترها را تغییر دهید یا بعداً مراجعه کنید.'])

@if($products->count() > 0)
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($products as $product)
            <x-product-card :product="$product" :show-quick-add="$quickAdd" />
        @endforeach
    </div>
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@else
    <div class="card-surface px-6 py-14 text-center">
        <p class="text-base font-medium text-ink">{{ $emptyTitle }}</p>
        <p class="mt-2 text-sm text-ink-muted">{{ $emptyText }}</p>
    </div>
@endif