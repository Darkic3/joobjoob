<?php

use App\Services\LocaleService;

if (! function_exists('locale')) {
    /**
     * دسترسی آسان به سرویس زبان‌ها در ویوها/هلپرها:
     *     locale()->active()          // زبان‌های فعال
     *     locale()->defaultCode()     // کد زبان پیش‌فرض
     *     locale()->isJalali('fa')    // آیا تقویم شمسی؟
     *     locale()->isHijri('ar')     // آیا تقویم قمری؟
     *     locale()->singleLanguageMode()
     */
    function locale(): LocaleService
    {
        return app(LocaleService::class);
    }
}

if (! function_exists('localizedRoute')) {
    /**
     * URL یک روت با پیشوند زبان (SEO):
     *   localizedRoute('products.show', ['product' => $p])          // زبان جاری
     *   localizedRoute('products.show', ['product' => $p], 'en')    // زبان مشخص
     *
     * زبان پیش‌فرض پیشوند نمی‌گیرد (canonical) → /products/foo
     * بقیه → /en/products/foo (روت locale.*)
     */
    function localizedRoute(string $name, array $params = [], ?string $code = null): string
    {
        // Public locale lock: always generate canonical unprefixed URL for public site.
        // Admin routes use route() directly and are unaffected by this helper.
        if (config('site-features.public_locale_locked', false)) {
            return route($name, $params);
        }

        $code ??= app()->getLocale();

        if ($code === locale()->defaultCode()) {
            return route($name, $params);
        }

        return route("locale.{$name}", $params + ['locale' => $code]);
    }
}

if (! function_exists('localizedUrl')) {
    /**
     * URL متعارفِ مسیرِ جاری برای یک زبان — برای hreflang / سوییچر:
     *   localizedUrl()            // زبان جاری (canonical این صفحه)
     *   localizedUrl('en')        // همان مسیر به انگلیسی
     */
    function localizedUrl(?string $code = null): string
    {
        $route = app('router')->current();

        if (! $route || ! $route->getName()) {
            return url('/');
        }

        // نام روت می‌تواند locale.home باشد (صفحه‌ی پیشونددار) → به نام پایه برگردان
        $name = preg_replace('/^locale\./', '', $route->getName());
        $params = $route->parameters();
        unset($params['locale']); // خودِ هلپر پیشوند زبان را اضافه می‌کند

        return localizedRoute($name, $params, $code);
    }
}

if (! function_exists('ogLocale')) {
    /**
     * کد locale استاندارد برای Open Graph (og:locale):
     *   fa → fa_IR | en → en_US | ar → ar_SA | tr → tr_TR
     *   بقیه → کد_کد (مثل de_DE)
     */
    function ogLocale(?string $code = null): string
    {
        $code ??= app()->getLocale();

        $map = [
            'fa' => 'fa_IR',
            'en' => 'en_US',
            'ar' => 'ar_SA',
            'tr' => 'tr_TR',
        ];

        return $map[$code] ?? strtoupper($code).'_'.strtoupper($code);
    }
}
