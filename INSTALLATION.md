# نصب و راه‌اندازی AALE Store

## پیش‌نیازها

- PHP 8.3+ (تست‌شده با PHP 8.5)
- Composer 2
- Node.js 20+ و npm
- MySQL 8 / MariaDB
- افزونه‌های PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd` یا `imagick`

## نصب محلی (Windows / XAMPP)

```bash
cd C:\xamppnew\htdocs\aale-store\aale-store
copy .env.example .env
php artisan key:generate
```

دیتابیس را بسازید و `.env` را تنظیم کنید:

```env
APP_NAME="AALE Store"
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa
APP_TIMEZONE=Asia/Tehran
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aale_store
DB_USERNAME=root
DB_PASSWORD=
SMS_DRIVER=log
ZARINPAL_SANDBOX=true
SUPER_ADMIN_MOBILE=09120000000
SUPER_ADMIN_PASSWORD=your-secure-password
```

سپس:

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

فروشگاه: `http://127.0.0.1:8000`  
پنل مدیریت: `http://127.0.0.1:8000/admin`

## متغیرهای مهم

| متغیر | توضیح |
|---|---|
| `SMS_DRIVER` | `log` برای توسعه، `melipayamak` برای تولید |
| `MELIPAYAMAK_*` | اعتبارنامه و الگوی OTP ملی‌پیامک |
| `ZARINPAL_MERCHANT_ID` | مرچنت زرین‌پال |
| `ZARINPAL_SANDBOX` | `true` برای درگاه آزمایشی |
| `STOCK_RESERVATION_MINUTES` | مهلت رزرو موجودی هنگام چک‌اوت |

هرگز فایل `.env` را به Git commit نکنید.
