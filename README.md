# AALE Store — فروشگاه آنلاین بیکران پایش آله

پلتفرم فروشگاهی Laravel 12 + Livewire 4، تمام‌RTL و فارسی، با موجودی انبارمحور و درگاه زرین‌پال.

## اجرای سریع

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

- فروشگاه: http://127.0.0.1:8000  
- مدیریت: http://127.0.0.1:8000/admin  
- سوپرادمین از `SUPER_ADMIN_*` در `.env`

## اسناد

- [INSTALLATION.md](INSTALLATION.md)
- [DEPLOYMENT.md](DEPLOYMENT.md)
- [DATABASE.md](DATABASE.md)

## پشته

Laravel 12 · Livewire 4.4.8 · Tailwind CSS 4 · Alpine (via Livewire) · MySQL · Vite · Verta (Jalali) · Intervention Image
