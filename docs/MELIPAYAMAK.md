# ملی‌پیامک — OTP فروشگاه آله

## افزونه PHP SOAP

الگوی فعلی دو متغیر دارد (`{0}` و `{1}`). SDK رسمی فقط در مسیر **SOAP** آرایه توکن‌ها را درست ارسال می‌کند:

```php
$api = new MelipayamakApi($username, $apiKey);
$sms = $api->sms('soap');
$sms->sendByBaseNumber([$otp, 'ورود امن'], $mobile, $bodyId);
```

### فعال‌سازی SOAP در XAMPP / PHP ویندوز

1. فایل `php.ini` فعال را پیدا کنید:

```bash
php --ini
```

2. خط زیر را از حالت توضیح خارج کنید:

```ini
extension=soap
```

3. وجود DLL را بررسی کنید: `ext/php_soap.dll`
4. ترمینال / Apache / `php-fpm` را restart کنید.
5. تأیید:

```bash
php -m | findstr soap
```

## فعال‌سازی ارسال واقعی پس از تأیید الگو

در `.env`:

```env
MELIPAYAMAK_ENABLED=true
MELIPAYAMAK_USERNAME=your-panel-username
MELIPAYAMAK_API_KEY=your-api-key
MELIPAYAMAK_PATTERN_CODE=156526
MELIPAYAMAK_PATTERN_APPROVED=true
MELIPAYAMAK_PATTERN_VARIABLE_COUNT=2
MELIPAYAMAK_PATTERN_SECOND_VALUE=ورود امن
MELIPAYAMAK_DRIVER=soap
MELIPAYAMAK_TEST_MODE=false
```

سپس:

```bash
php artisan config:clear
php artisan melipayamak:test 0912XXXXXXX
```

فقط وقتی `PATTERN_APPROVED=true` است ارسال واقعی انجام می‌شود.

## الگوی تک‌متغیره در آینده

```env
MELIPAYAMAK_PATTERN_VARIABLE_COUNT=1
```

در این حالت فقط OTP به‌عنوان `{0}` ارسال می‌شود.
