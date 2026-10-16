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
 * پنل ادمین همیشه تک‌زبانه است (admin_locale) و فقط همان یک زبان را می‌بیند.
 */
class LocaleComposer
{
    public function __construct(protected LocaleService $locales) {}

    public function compose(View $view): void
    {
        $isAdmin = str_starts_with(request()->path(), 'admin');
        // پنل ادمین تک‌زبانه است — مستقل از SetLocale، همیشه زبان قفل‌شده
        $locale = $isAdmin
            ? (string) config('site-features.admin_locale', 'fa')
            : app()->getLocale();
        $isPublic = ! $isAdmin;
        $locked = $isPublic && (bool) config('site-features.public_locale_locked', false);

        // زبان‌های قابل نمایش در سوییچرها — ادمین و حالت قفل: فقط همان یک زبان
        if ($isAdmin || $locked) {
            $one = $this->locales->find($locale);
            $locales = $one ? [$locale => $one] : [];
        } else {
            $locales = $this->locales->active();
        }

        $view->with([
            'currentLocale' => $locale,
            'direction' => $this->locales->isRtl($locale) ? 'rtl' : 'ltr',
            'digitFamily' => $this->locales->digitFamily($locale),
            'locales' => $locales,
            // پرچم قفل برای مصرف ویوها (مثلاً seo-head)
            'publicLocaleLocked' => $locked,
        ]);
    }
}
