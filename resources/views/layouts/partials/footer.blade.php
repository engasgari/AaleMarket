<footer class="mt-auto border-t border-line bg-surface-secondary">
    <div class="container-store py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="text-lg font-semibold text-primary mb-3">آله مارکت</div>
                <p class="text-sm leading-7 text-ink-muted">
                    فروشگاه آنلاین آله مارکت — تأمین‌کننده تجهیزات الکترونیکی، شبکه، دوربین مداربسته و اتوماسیون صنعتی.
                </p>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-ink">دسترسی سریع</h3>
                <ul class="space-y-2 text-sm text-ink-muted">
                    <li><a href="{{ url('/categories') }}" class="hover:text-ink">دسته‌بندی‌ها</a></li>
                    <li><a href="{{ url('/products') }}" class="hover:text-ink">محصولات</a></li>
                    <li><a href="{{ url('/cart') }}" class="hover:text-ink">سبد خرید</a></li>
                    <li><a href="{{ url('/account') }}" class="hover:text-ink">حساب کاربری</a></li>
                </ul>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-ink">راهنما</h3>
                <ul class="space-y-2 text-sm text-ink-muted">
                    <li><a href="{{ url('/pages/shopping-guide') }}" class="hover:text-ink">راهنمای خرید</a></li>
                    <li><a href="{{ url('/pages/privacy') }}" class="hover:text-ink">حریم خصوصی</a></li>
                    <li><a href="{{ url('/pages/terms') }}" class="hover:text-ink">قوانین و مقررات</a></li>
                </ul>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-ink">تماس با ما</h3>
                <ul class="space-y-2 text-sm text-ink-muted">
                    <li>وب‌سایت: <a href="https://shop.aale.ir" class="hover:text-ink">shop.aale.ir</a></li>
                    <li>شرکت: بیکران پایش آله</li>
                </ul>
            </div>
        </div>

        <div class="mt-10 border-t border-line pt-6 text-center text-xs text-ink-muted">
            © {{ \Hekmatinasser\Verta\Verta::now()->format('Y') }} بیکران پایش آله. تمامی حقوق محفوظ است.
        </div>
    </div>
</footer>
