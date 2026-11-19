@extends('layouts.storefront')

@section('title', 'آله مارکت')
@section('meta_description', 'آله مارکت — فروشگاه آنلاین تجهیزات الکترونیکی، شبکه، دوربین مداربسته و اتوماسیون صنعتی')
@section('canonical', url('/'))

@section('content')
    <section class="border-b border-line bg-surface-secondary">
        <div class="container-store py-16 sm:py-20">
            <p class="mb-3 text-sm tracking-wide text-ink-muted">بیکران پایش آله</p>
            <h1 class="max-w-2xl text-3xl font-semibold leading-tight text-primary sm:text-4xl">
                آله مارکت
            </h1>
            <p class="mt-4 max-w-xl text-base leading-8 text-ink-muted">
                تأمین تجهیزات الکترونیکی، شبکه، دوربین مداربسته و اتوماسیون صنعتی با موجودی واقعی و پشتیبانی تخصصی.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ url('/products') }}" class="btn-primary">مشاهده محصولات</a>
                <a href="{{ url('/categories') }}" class="btn-secondary">دسته‌بندی‌ها</a>
            </div>
        </div>
    </section>

    <section class="container-store py-14">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-ink">دسته‌بندی‌های ویژه</h2>
                <p class="mt-1 text-sm text-ink-muted">انتخاب سریع بر اساس نیاز فنی شما</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                'تجهیزات شبکه',
                'دوربین مداربسته',
                'اتوماسیون صنعتی',
                'لوازم الکترونیکی',
            ] as $category)
                <a href="{{ url('/categories') }}" class="card-surface p-5 transition-colors hover:bg-surface-secondary">
                    <div class="text-sm font-medium text-ink">{{ $category }}</div>
                    <div class="mt-2 text-xs text-ink-muted">مشاهده محصولات</div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-t border-line bg-surface-secondary">
        <div class="container-store py-14">
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-ink">محصولات ویژه</h2>
                <p class="mt-1 text-sm text-ink-muted">به‌زودی از پنل مدیریت بارگذاری می‌شوند</p>
            </div>
            <div class="card-surface px-6 py-12 text-center text-sm text-ink-muted">
                هنوز محصولی برای نمایش وجود ندارد.
            </div>
        </div>
    </section>
@endsection
