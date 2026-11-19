<div class="relative w-full">
    <form wire:submit="submit" class="flex w-full">
        <input
            type="search"
            wire:model.live.debounce.350ms="query"
            class="input"
            placeholder="جستجوی محصول، کد یا برند..."
            autocomplete="off"
        >
    </form>

    @if($suggestions->isNotEmpty() && strlen(trim($query)) >= 2)
        <div class="absolute inset-x-0 top-full z-50 mt-1 border border-line bg-surface shadow-sm">
            @foreach($suggestions as $item)
                <a
                    href="{{ route('products.show', $item) }}"
                    class="flex items-center gap-3 border-b border-line px-3 py-2 text-sm last:border-b-0 hover:bg-surface-secondary"
                    wire:navigate
                >
                    @if($item->main_image_path)
                        <img src="{{ '/storage/'.ltrim($item->main_image_path, '/') }}" alt="" class="h-10 w-10 object-contain">
                    @endif
                    <span class="flex-1 text-ink">{{ $item->name }}</span>
                    <span class="text-xs text-ink-muted">{{ $item->sku }}</span>
                </a>
            @endforeach
            <button type="button" wire:click="submit" class="block w-full px-3 py-2 text-start text-sm text-primary hover:bg-surface-secondary">
                مشاهده همه نتایج
            </button>
        </div>
    @endif
</div>