<div class="max-w-xl space-y-4">
    <h1 class="text-xl font-semibold text-ink">{{ $coupon ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید' }}</h1>

    <div>
        <label class="label">کد</label>
        <input class="input" wire:model="code" dir="ltr">
        @error('code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="label">نوع</label>
            <select class="input" wire:model="type">
                <option value="percentage">درصدی</option>
                <option value="fixed">مبلغ ثابت</option>
            </select>
        </div>
        <div>
            <label class="label">مقدار</label>
            <input type="number" class="input" wire:model="value" dir="ltr">
            @error('value') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">حداقل خرید</label>
            <input type="number" class="input" wire:model="min_purchase" dir="ltr">
        </div>
        <div>
            <label class="label">سقف تخفیف</label>
            <input type="number" class="input" wire:model="max_discount" dir="ltr">
        </div>
        <div>
            <label class="label">سقف کل استفاده</label>
            <input type="number" class="input" wire:model="usage_limit" dir="ltr">
        </div>
        <div>
            <label class="label">سقف هر مشتری</label>
            <input type="number" class="input" wire:model="usage_per_customer" dir="ltr">
        </div>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active"> فعال</label>

    <div class="flex gap-2">
        <button type="button" class="btn-primary" wire:click="save">ذخیره</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">بازگشت</a>
    </div>
</div>
