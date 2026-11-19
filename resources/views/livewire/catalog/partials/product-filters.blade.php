@props(['brands', 'showCategory' => false])

<div class="card-surface p-4 space-y-4 lg:sticky lg:top-20">
    <div>
        <label class="label" for="catalog-search">جستجو</label>
        <input id="catalog-search" type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="نام، کد یا SKU...">
    </div>

    @if($showCategory)
        {{-- دسته در صفحه دسته‌بندی از قبل مشخص است --}}
    @endif

    <div>
        <label class="label" for="catalog-brand">برند</label>
        <select id="catalog-brand" wire:model.live="brandId" class="input">
            <option value="">همه برندها</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="label" for="catalog-sort">مرتب‌سازی</label>
        <select id="catalog-sort" wire:model.live="sort" class="input">
            <option value="newest">جدیدترین</option>
            <option value="featured">ویژه</option>
            <option value="price_asc">قیمت: کم به زیاد</option>
            <option value="price_desc">قیمت: زیاد به کم</option>
        </select>
    </div>
</div>