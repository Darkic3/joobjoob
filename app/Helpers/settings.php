<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * دریافت مقدار یک تنظیم سایت با کش (مقدار پیش‌فرض از کانفیگ یا آرگومان)
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('settingOverride')) {
    /**
     * مقدار تنظیم فقط اگر ادمین صریحاً ذخیره کرده باشد؛ وگرنه پیش‌فرض آرگومان.
     * برای متن‌هایی که باید به زبان جاری ترجمه شوند (مثل عنوان بخش‌های صفحه اصلی) —
     * دیفالت فارسی کانفیگ را نادیده می‌گیرد.
     */
    function settingOverride(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->getStored($key, $default);
    }
}
