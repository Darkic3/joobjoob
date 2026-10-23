<?php

// تنها منبع اعتبارنامه ادمین اصلی — سیدر AdminOwnerSeeder فقط از همین‌جا می‌خواند.
// ⚠️ سیدر هرگز مستقیم env() نمی‌خواند — بعد از `php artisan config:cache`
// مقدار env() خارج از فایل‌های کانفیگ null است.
// خالی بودن یک کلید = پیش‌فرض سیدر + هشدار در خروجی سید (به‌جز password که تصادفی ساخته می‌شود).
return [
    'owner' => [
        'name' => env('ADMIN_OWNER_NAME'), // خالی → «مدیر اصلی»
        'email' => env('ADMIN_OWNER_EMAIL'), // خالی → admin@joobjoob.test
        'mobile' => env('ADMIN_OWNER_MOBILE'), // خالی → 09120000000
        'password' => env('ADMIN_OWNER_PASSWORD'), // خالی → رمز تصادفی یک‌باره در سیدر
    ],
];
