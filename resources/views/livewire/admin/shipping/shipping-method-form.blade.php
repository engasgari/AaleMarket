<div class="max-w-2xl space-y-4">
    <h1 class="text-xl font-semibold text-ink">{{ $shippingMethod ? 'ویرایش روش ارسال' : 'روش ارسال جدید' }}</h1>

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
        <label class="label">توضیح</label>
        <textarea class="input" rows="2" wire:model="description"></textarea>
    </div>
    <div>
        <label class="label">ترتیب</label>
        <input type="number" class="input w-32" wire:model="sort_order" dir="ltr">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active"> فعال</label>

    <div class="space-y-3 border-t border-line pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-medium">نرخ‌ها</h2>
            <button type="button" class="btn-secondary" wire:click="addRate">افزودن نرخ</button>
        </div>
        @foreach($rates as $index => $rate)
            <div class="card-surface p-4 grid gap-3 sm:grid-cols-2" wire:key="rate-{{ $index }}">
                <div>
                    <label class="label">نام نرخ</label>
                    <input class="input" wire:model="rates.{{ $index }}.name">
                </div>
                <div>
                    <label class="label">حداقل مبلغ سفارش</label>
                    <input type="number" class="input" wire:model="rates.{{ $index }}.min_order_amount" dir="ltr">
                </div>
                <div>
                    <label class="label">هزینه ارسال</label>
                    <input type="number" class="input" wire:model="rates.{{ $index }}.price" dir="ltr">
                </div>
                <div class="flex items-end justify-between gap-2">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="rates.{{ $index }}.is_active"> فعال</label>
                    <button type="button" class="btn-ghost text-danger" wire:click="removeRate({{ $index }})">حذف</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex gap-2">
        <button type="button" class="btn-primary" wire:click="save">ذخیره</button>
        <a href="{{ route('admin.shipping.index') }}" class="btn-secondary">بازگشت</a>
    </div>
</div>
