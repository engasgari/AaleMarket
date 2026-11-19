<div class="max-w-xl space-y-4">
    <h1 class="text-xl font-semibold text-ink">{{ $warehouse ? 'ویرایش انبار' : 'انبار جدید' }}</h1>

    <div>
        <label class="label">نام</label>
        <input class="input" wire:model="name">
        @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="label">کد</label>
        <input class="input" wire:model="code" dir="ltr">
        @error('code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="label">آدرس</label>
        <textarea class="input" rows="3" wire:model="address"></textarea>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active"> فعال</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_default"> انبار پیش‌فرض</label>

    <div class="flex gap-2">
        <button type="button" class="btn-primary" wire:click="save">ذخیره</button>
        <a href="{{ route('admin.warehouses.index') }}" class="btn-secondary">بازگشت</a>
    </div>
</div>
