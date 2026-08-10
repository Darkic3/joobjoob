<?php

namespace App\View\Composers;

use App\Services\LocaleService;
use Illuminate\View\View;

/**
 * داده‌های سراسری زبان، جهت و خانوادهٔ ارقام را در اختیار همهٔ ویوها قرار می‌دهد.
 *
 * منبع واحد حقیقت LocaleService است (جدول languages)؛ قبلاً این محاسبه به‌صورت
 * fallback دفاعی داخل لایه‌ها و صفحهٔ لاگین تکرار می‌شد — حالا یک‌جا و خارج از ویو.
 */
class LocaleComposer
{
    public function __construct(protected LocaleService $locales) {}

    public function compose(View $view): void
    {
        $locale = app()->getLocale();

        $view->with([
            'currentLocale' => $locale,
            'direction' => $this->locales->isRtl($locale) ? 'rtl' : 'ltr',
            'digitFamily' => $this->locales->digitFamily($locale),
            // زبان‌های فعال برای سوییچر زبان (هدر سایت/ادمین/لاگین) — در حالت تک‌زبانه خالی/تک‌عنصری
            'locales' => $this->locales->active(),
        ]);
    }
}
