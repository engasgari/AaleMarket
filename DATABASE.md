# پایگاه داده AALE Store

## اتصال

- موتور: MySQL 8 / MariaDB سازگار
- Charset: `utf8mb4` / `utf8mb4_unicode_ci`
- قیمت‌ها: عدد صحیح به **ریال**
- تاریخ‌ها در DB: timestamp میلادی؛ نمایش کاربر: جلالی

## دامنه جداول

- هویت: `users`, `roles`, `permissions`, `role_permission`, `user_role`, `otp_codes`, `login_histories`
- مشتری: `customer_profiles`, `customer_addresses`
- کاتالوگ: `brands`, `categories`, `products`, `product_*`, `attributes`, `attribute_values`
- موجودی: `warehouses`, `inventory_stocks`, `inventory_movements`, `stock_reservations`, `stock_adjustments`
- خرید: `carts`, `cart_items`, `wishlists`, `wishlist_items`
- سفارش/پرداخت: `orders`, `order_items`, `order_addresses`, `order_status_histories`, `payments`, `payment_transactions`, `payment_logs`
- تخفیف/ارسال: `coupons`, `coupon_usages`, `shipping_methods`, `shipping_rates`
- سیستم: `settings`, `activity_logs`, `sms_logs`, `notification_logs`

## ایندکس‌های کلیدی

- `products.sku`, `products.slug`, `products.status`
- `orders.order_number`, `orders.status`
- `users.mobile`
- `payments.authority`, `payments.reference_id`
- `inventory_stocks.stock_key` (یکتا برای انبار+محصول+واریانت)

## Seeders

```bash
php artisan migrate --seed
```

شامل نقش‌ها، تنظیمات، سوپرادمین (از env)، انبار مرکزی، روش‌های ارسال، و کاتالوگ دمو.
