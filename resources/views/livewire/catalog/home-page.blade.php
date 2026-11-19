<div>
    <section class="home-hero relative isolate min-h-[70vh] overflow-hidden border-b border-line">
        <img
            src="{{ asset('images/banners/home-hero-smart.jpg') }}"
            alt=""
            class="home-hero__media absolute inset-0 h-full w-full object-cover"
            fetchpriority="high"
        >
        <div class="absolute inset-0 bg-gradient-to-l from-primary-dark/85 via-primary-dark/70 to-primary-dark/35"></div>

        <div class="container-store relative flex min-h-[70vh] items-center py-16 sm:py-20">
            <div class="max-w-2xl text-white">
                <p class="home-hero__brand mb-4 text-3xl font-semibold tracking-tight sm:text-5xl">آله مارکت</p>
                <h1 class="home-hero__title text-2xl font-medium leading-relaxed sm:text-3xl">آینده‌ی هوشمند شما</h1>
                <p class="home-hero__copy mt-4 max-w-lg text-base leading-8 text-white/80">
                    تجهیزات هوشمند برای خانه و کسب‌وکار؛ کنترل، امنیت و پایداری در یک فروشگاه.
                </p>
                <div class="home-hero__cta mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index', ['sort' => 'featured']) }}" class="btn-primary bg-white text-primary hover:bg-white/90">
                        مشاهده تجهیزات هوشمند
                    </a>
                    <a href="{{ route('categories.index') }}" class="btn-secondary border-white/30 bg-transparent text-white hover:bg-white/10">
                        دسته‌بندی‌ها
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('livewire.catalog.partials.flash')

    <section class="container-store py-12 sm:py-14">
        @if($featuredCategories->isEmpty())
            <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                <div class="sm:text-right">
                    <h2 class="text-xl font-semibold text-ink">دسته‌بندی‌های ویژه</h2>
                </div>
                <p class="text-sm text-ink-muted">هنوز دسته‌بندی فعالی ثبت نشده است.</p>
            </div>
        @else
            <div class="flex flex-wrap items-center justify-start gap-x-10 gap-y-5 sm:gap-x-14">
                <div class="shrink-0">
                    <h2 class="text-xl font-semibold text-ink">دسته‌بندی‌های ویژه</h2>
                    <p class="mt-1 text-sm text-ink-muted">انتخاب سریع دسته</p>
                </div>

                <nav class="flex flex-wrap items-start gap-x-6 gap-y-5 sm:gap-x-8" aria-label="دسته‌بندی‌های ویژه">
                    @foreach($featuredCategories as $category)
                        @php
                            $sampleProduct = $category->products->first();
                            $sampleImage = $sampleProduct?->mainImageUrl();
                        @endphp

                        <a
                            href="{{ route('categories.show', $category) }}"
                            wire:key="featured-category-{{ $category->id }}"
                            class="group flex w-[4.5rem] flex-col items-center gap-2 text-center transition-colors"
                        >
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-line bg-surface-secondary p-1.5 transition-transform group-hover:scale-105">
                                @if($sampleImage)
                                    <img
                                        src="{{ $sampleImage }}"
                                        alt="{{ $sampleProduct->name }}"
                                        class="h-full w-full object-contain"
                                        loading="lazy"
                                    >
                                @else
                                    <span class="text-[10px] text-ink-muted">بدون تصویر</span>
                                @endif
                            </span>
                            <span class="line-clamp-2 min-h-8 text-[11px] leading-4 text-ink-muted transition-colors group-hover:text-ink">
                                {{ $category->name }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </section>

    <section class="border-t border-line bg-surface-secondary">
        <div class="container-store py-14">
            <div class="mb-10 text-center">
                <h2 class="text-xl font-semibold text-ink">محصولات ویژه</h2>
                <p class="mt-2 text-sm text-ink-muted">سه محصول اصلی آله مارکت برای خانه و کسب‌وکار هوشمند</p>
            </div>
            @if($featuredProducts->isEmpty())
                <div class="card-surface mx-auto max-w-xl px-6 py-12 text-center text-sm text-ink-muted">
                    هنوز محصول ویژه‌ای برای نمایش وجود ندارد.
                </div>
            @else
                <div class="mx-auto grid max-w-5xl grid-cols-1 justify-items-stretch gap-6 sm:grid-cols-3">
                    @foreach($featuredProducts as $product)
                        <x-featured-product-card :product="$product" :show-quick-add="true" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="container-store py-14">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-ink">جدیدترین محصولات</h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm text-primary">مشاهده همه</a>
        </div>
        @if($newProducts->isEmpty())
            <div class="card-surface px-6 py-12 text-center text-sm text-ink-muted">
                هنوز محصولی ثبت نشده است.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($newProducts as $product)
                    <x-product-card :product="$product" :show-quick-add="true" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="border-t border-line bg-surface-secondary">
        <div class="container-store py-14">
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-ink">پرفروش‌ها</h2>
                <p class="mt-1 text-sm text-ink-muted">بر اساس آخرین ثبت‌ها (به‌زودی بر اساس فروش واقعی)</p>
            </div>
            @if($bestSellers->isEmpty())
                <div class="card-surface px-6 py-12 text-center text-sm text-ink-muted">
                    داده فروش هنوز در دسترس نیست.
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($bestSellers as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>