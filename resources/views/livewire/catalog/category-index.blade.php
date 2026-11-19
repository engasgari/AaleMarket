<div class="container-store py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-ink">دسته‌بندی‌ها</h1>
        <p class="mt-1 text-sm text-ink-muted">انتخاب دسته‌بندی محصولات</p>
    </div>

    @if($categories->isEmpty())
        <div class="card-surface px-6 py-12 text-center text-sm text-ink-muted">دسته‌بندی فعالی وجود ندارد.</div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="card-surface p-5 hover:bg-surface-secondary">
                    <div class="text-sm font-medium text-ink">{{ $category->name }}</div>
                    @if($category->description)
                        <p class="mt-2 line-clamp-2 text-xs text-ink-muted">{{ $category->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
