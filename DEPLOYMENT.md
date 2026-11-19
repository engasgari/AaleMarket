# استقرار Production — AALE Store

## معماری پیشنهادی

```
Internet → Nginx Reverse Proxy (shop.aale.ir)
         → aale-store-nginx
         → aale-store-app (PHP-FPM)
         → aale-store-mysql (volume پایدار)
```

## استقرار با Docker Compose

1. روی سرور مخزن را clone کنید.
2. فایل `.env` production را بسازید (`APP_ENV=production`, `APP_DEBUG=false`, HTTPS URL، رمزهای واقعی).
3. بیلد و اجرا:

```bash
docker compose build
docker compose up -d
```

4. دارایی‌های Vite داخل `Dockerfile` بیلد می‌شوند (`public/build` داخل ایمیج).
5. حجم‌های پایدار: `mysql_data`, `app_storage` — هرگز حذف نکنید.

## اسکریپت استقرار پیشنهادی

```bash
#!/usr/bin/env bash
set -euo pipefail
git fetch origin
git checkout main
git pull --ff-only origin main
docker compose build app
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
curl -fsS https://shop.aale.ir/up >/dev/null
```

## نکات امنیتی

- MySQL را به اینترنت expose نکنید.
- Trusted proxies را برای Nginx reverse proxy تنظیم کنید.
- صف و Scheduler را در production با `queue:work` و cron `schedule:run` اجرا کنید.
- `inventory:release-expired` هر ۵ دقیقه در Scheduler ثبت شده است.
