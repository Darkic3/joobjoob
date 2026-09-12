<?php

namespace App\View\Components;

use App\Services\Seo\Schema\SchemaBuilder;
use App\Services\Seo\SeoResolver;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * تنها منبع تولید تگ‌های SEO در `<head>` سایت.
 *
 * استفاده: <x-seo-head />  (بدون هیچ attribute)
 *
 * مقادیر از `SeoResolver` می‌آیند که زنجیرهٔ fallback را حل می‌کند؛ بنابراین ویوها
 * و کنترلرها موجود زنده‌اند و صرفاً می‌توانند با `seo()->...` مقدار قطعی بدهند.
 *
 * نکتهٔ پیاده‌سازی: وابستگی در `render()` از کانتینر گرفته می‌شود (و نه در سازنده)
 * تا نحوهٔ نمونه‌سازی کامپوننت توسط Blade هیچ تأثیری روی آن نداشته باشد.
 */
class SeoHead extends Component
{
    public function render(): View
    {
        return view('components.seo-head', [
            'seo' => app(SeoResolver::class)->resolve(),
            // گراف JSON-LD جدا از SeoResolver ساخته می‌شود تا وابستگی حلقه‌ای
            // (SchemaBuilder ← SeoResolver) ایجاد نشود.
            'graph' => app(SchemaBuilder::class)->build(),
        ]);
    }
}
