<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-ink">{{ $brand ? 'ویرایش برند' : 'برند جدید' }}</h1>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn-ghost text-sm">بازگشت</a>
    </div>

    <form wire:submit="save" class="card-surface max-w-2xl space-y-4 rounded-md p-4">
        <div>
            <label class="label" for="brand-name">نام</label>
            <input wire:model.live="name" id="brand-name" type="text" class="input">
            @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="brand-slug">نامک</label>
            <input wire:model.live="slug" wire:focus="$set('slugManual', true)" id="brand-slug" type="text" class="input">
            @error('slug') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="website">وب‌سایت</label>
            <input wire:model="website" id="website" type="url" class="input" placeholder="https://">
        </div>
        <div>
            <label class="label" for="brand-desc">توضیحات</label>
            <textarea wire:model="description" id="brand-desc" rows="3" class="input"></textarea>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="sort_order">ترتیب</label>
                <input wire:model="sort_order" id="sort_order" type="number" class="input">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input wire:model="is_active" type="checkbox" class="rounded border-line">
                    فعال
                </label>
            </div>
        </div>
        <div>
            <label class="label" for="seo_title">عنوان سئو</label>
            <input wire:model="seo_title" id="seo_title" type="text" class="input">
        </div>
        <div>
            <label class="label" for="seo_description">توضیحات سئو</label>
            <textarea wire:model="seo_description" id="seo_description" rows="2" class="input"></textarea>
        </div>
        <button type="submit" class="btn-primary">ذخیره</button>
    </form>
</div>