<div class="container-store py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-ink">جستجو</h1>
        <p class="mt-1 text-sm text-ink-muted">
            @if($search !== '')
                نتایج برای «{{ $search }}»
            @else
                عبارت مورد نظر خود را جستجو کنید
            @endif
        </p>
    </div>

    <form wire:submit.prevent="$refresh" class="mb-8 max-w-xl">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="نام محصول، SKU یا کد..." dir="rtl">
    </form>

    @include('livewire.catalog.partials.product-grid', [
        'products' => $products,
        'quickAdd' => true,
        'emptyTitle' => 'نتیجه‌ای یافت نشد',
        'emptyText' => 'عبارت دیگری را امتحان کنید.',
    ])
</div>
