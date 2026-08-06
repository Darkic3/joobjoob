<?php

// اعتبارنامه ادمین اصلی (سیدر AdminOwnerSeeder از همین‌جا می‌خواند)
// ⚠️ سیدر هرگز مستقیم env() نمی‌خواند — بعد از config:cache مقدار env() خارج از کانفیگ null است
return [
    'owner' => [
        'name'     => env('ADMIN_OWNER_NAME', 'مدیر اصلی'),
        'email'    => env('ADMIN_OWNER_EMAIL', 'admin@example.com'),
        'mobile'   => env('ADMIN_OWNER_MOBILE', '09123456789'),
        'password' => env('ADMIN_OWNER_PASSWORD'), // null → رمز تصادفی یک‌باره در سیدر
    ],
];
