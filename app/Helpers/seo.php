<?php

declare(strict_types=1);

use App\Services\Seo\SeoManager;
use App\Services\Seo\SeoResolver;

if (! function_exists('seo')) {
    /**
     * دسترسی آسان و روان (Fluent) به مدیریت متای SEO درخواست جاری.
     *
     * استفاده در کنترلر (بالاترین اولویت زنجیرهٔ fallback):
     *     seo()->title($product->trans('name'))->description(...)->image(...);
     *     seo()->forModel($product);            // منبع متای اختصاصی و مقادیر مشتق‌شده
     *     seo()->forModel($product)->noIndex(); // صفحه‌ای که نباید ایندکس شود
     *     seo()->forPath('/products');          // صفحه‌ای که مدل ندارد
     *
     * استفاده در ویو (override موضعی):
     *     @php seo()->title(__('messages.cart')); @endphp
     */
    function seo(): SeoManager
    {
        return app(SeoManager::class);
    }
}

if (! function_exists('seo_resolver')) {
    /**
     * حل‌کنندهٔ نهایی متا تگ‌ها.
     * این تابع معمولاً مستقیماً در کنترلر صدا زده نمی‌شود و توسط
     * کامپوننت <x-seo-head /> یا Blade Directive ها استفاده می‌شود.
     *
     * @return SeoResolver
     */
    function seo_resolver(): SeoResolver
    {
        return app(SeoResolver::class);
    }
}
