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
