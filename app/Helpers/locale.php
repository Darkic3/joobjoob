<?php

use App\Services\LocaleService;

if (! function_exists('locale')) {
    /**
     * دسترسی آسان به سرویس زبان‌ها در ویوها/هلپرها:
     *     locale()->active()          // زبان‌های فعال
     *     locale()->defaultCode()     // کد زبان پیش‌فرض
     *     locale()->isJalali('fa')    // آیا تقویم شمسی؟
     *     locale()->singleLanguageMode()
     */
    function locale(): LocaleService
    {
        return app(LocaleService::class);
    }
}
