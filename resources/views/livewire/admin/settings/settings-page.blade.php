<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-ink">تنظیمات</h1>
        <p class="mt-1 text-sm text-ink-muted">پیکربندی فروشگاه</p>
    </div>

    @if (session('status'))
        <div class="border border-line bg-surface px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    @forelse($grouped as $group => $settings)
        <div class="card-surface p-5 space-y-4">
            <h2 class="font-medium text-ink">{{ $group }}</h2>
            @foreach($settings as $setting)
                <div>
                    <label class="label">{{ $setting->key }}</label>
                    @if($setting->type === 'boolean')
                        <select class="input" wire:model="values.{{ $group }}.{{ $setting->key }}">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    @else
                        <input class="input" wire:model="values.{{ $group }}.{{ $setting->key }}">
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="card-surface px-6 py-10 text-center text-sm text-ink-muted">تنظیمی یافت نشد.</div>
    @endforelse

    @if($grouped->isNotEmpty())
        <button type="button" class="btn-primary" wire:click="save">ذخیره تنظیمات</button>
    @endif
</div>
