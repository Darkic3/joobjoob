<?php

use App\Services\Seo\SeoManager;
use App\Services\Seo\SeoResolver;

if (! function_exists('seo')) {
    /**
     * دسترسی آسان به جمع‌کنندهٔ متای SEO درخواست جاری.
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
     * حل‌کنندهٔ متای نهایی صفحه — استفادهٔ عادی در کنترلر نیست؛
     * کامپوننت `<x-seo-head />` و میدل‌ور هدرها از آن استفاده می‌کنند.
     */
    function seo_resolver(): SeoResolver
    {
        return app(SeoResolver::class);
    }
}
