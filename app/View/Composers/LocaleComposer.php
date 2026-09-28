<?php

namespace App\View\Composers;

use App\Services\LocaleService;
use Illuminate\View\View;

/**
 * تأمین متغیرهای سراسری زبان و جهت متن برای همه‌ی ویوها.
 *
 * وقتی قفل زبان سایت عمومی فعال باشد (config/site-features.php)، آرایه‌ی
 * `locales` فقط همان یک زبان قفل‌شده را برمی‌گرداند تا هیچ language switcher
 * در هیچ ویویی قابل رندر نباشد (حتی اگر ویویی به‌اشتباه switcher داشته باشد).
 * پنل ادمین همچنان همه‌ی زبان‌های فعال را می‌بیند.
 */
class LocaleComposer
{
    public function __construct(protected LocaleService $locales) {}

    public function compose(View $view): void
    {
        $locale = app()->getLocale();
        $isPublic = ! str_starts_with(request()->path(), 'admin');
        $locked = $isPublic && (bool) config('site-features.public_locale_locked', false);

        $view->with([
            'currentLocale' => $locale,
            'direction' => $this->locales->isRtl($locale) ? 'rtl' : 'ltr',
            'digitFamily' => $this->locales->digitFamily($locale),
            // زبان‌های فعال — در حالت قفل عمومی فقط زبان قفل‌شده
            'locales' => $locked
                ? array_filter([$locale => $this->locales->find($locale)])
                : $this->locales->active(),
            // پرچم قفل برای مصرف ویوها (مثلاً seo-head)
            'publicLocaleLocked' => $locked,
        ]);
    }
}
