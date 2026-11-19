<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-ink">{{ $product ? 'ویرایش محصول' : 'محصول جدید' }}</h1>
            <p class="mt-1 text-sm text-ink-muted">اطلاعات اصلی کاتالوگ</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn-ghost text-sm">بازگشت به فهرست</a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-line bg-surface px-3 py-2 text-sm text-ink">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="card-surface rounded-md p-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="name">نام محصول</label>
                    <input wire:model.live="name" id="name" type="text" class="input @error('name') border-danger @enderror">
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="slug">نامک (slug)</label>
                    <input wire:model.live="slug" wire:focus="$set('slugManual', true)" id="slug" type="text" class="input @error('slug') border-danger @enderror">
                    @error('slug') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="sku">SKU</label>
                    <input wire:model="sku" id="sku" type="text" class="input @error('sku') border-danger @enderror" @disabled($product)>
                    @error('sku') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="product_code">کد محصول</label>
                    <input wire:model="product_code" id="product_code" type="text" class="input">
                </div>
                <div>
                    <label class="label" for="brand_id">برند</label>
                    <select wire:model="brand_id" id="brand_id" class="input">
                        <option value="">— بدون برند —</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">دسته‌بندی‌ها</label>
                    <select wire:model="category_ids" multiple class="input min-h-[120px]">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_ids') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="short_description">توضیح کوتاه</label>
                    <textarea wire:model="short_description" id="short_description" rows="2" class="input"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="description">توضیحات کامل</label>
                    <textarea wire:model="description" id="description" rows="5" class="input"></textarea>
                </div>
            </div>
        </div>

        <div class="card-surface rounded-md p-4">
            <h2 class="mb-4 text-sm font-semibold text-ink">قیمت و موجودی</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label" for="purchase_price">قیمت خرید (ریال)</label>
                    <input wire:model="purchase_price" id="purchase_price" type="number" min="0" class="input">
                </div>
                <div>
                    <label class="label" for="selling_price">قیمت فروش (ریال)</label>
                    <input wire:model="selling_price" id="selling_price" type="number" min="0" class="input">
                </div>
                <div>
                    <label class="label" for="discount_price">قیمت با تخفیف (ریال)</label>
                    <input wire:model="discount_price" id="discount_price" type="number" min="0" class="input">
                </div>
                <div>
                    <label class="label" for="min_order_qty">حداقل سفارش</label>
                    <input wire:model="min_order_qty" id="min_order_qty" type="number" min="1" class="input">
                </div>
                <div>
                    <label class="label" for="status">وضعیت</label>
                    <select wire:model="status" id="status" class="input">
                        <option value="draft">پیش‌نویس</option>
                        <option value="published">منتشر شده</option>
                        <option value="archived">آرشیو</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-ink">
                        <input wire:model="is_featured" type="checkbox" class="rounded border-line">
                        محصول ویژه
                    </label>
                </div>
            </div>
        </div>

        <div class="card-surface rounded-md p-4">
            <h2 class="mb-4 text-sm font-semibold text-ink">تصویر اصلی</h2>
            @if ($mainImageUrl)
                <img src="{{ $mainImageUrl }}" alt="" class="mb-3 h-32 w-32 rounded-md border border-line object-cover">
            @endif
            <input wire:model="main_image" type="file" accept="image/*" class="block w-full text-sm text-ink-muted">
            <div wire:loading wire:target="main_image" class="mt-2 text-xs text-ink-muted">در حال بارگذاری…</div>
            @error('main_image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>

        <div class="card-surface rounded-md p-4">
            <h2 class="mb-4 text-sm font-semibold text-ink">سئو</h2>
            <div class="grid gap-4">
                <div>
                    <label class="label" for="seo_title">عنوان سئو</label>
                    <input wire:model="seo_title" id="seo_title" type="text" class="input">
                </div>
                <div>
                    <label class="label" for="seo_description">توضیحات سئو</label>
                    <textarea wire:model="seo_description" id="seo_description" rows="2" class="input"></textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">ذخیره</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">انصراف</a>
        </div>
    </form>
</div>