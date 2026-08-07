<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تشخیص و اعمال زبان فعال کاربر.
 *
 * منبع واحد حقیقت: LocaleService (جدول languages).
 *
 * اولویت: حالت تک‌زبانه > پارامتر URL > کاربر لاگین‌شده > session > پیش‌فرض
 * - زبان غیرفعال/ناموجود در هر مرحله → fallback بی‌صدا به زبان پیش‌فرض
 * - در حالت تک‌زبانه (?lang=xx تعداد فعال ≤ ۱) → همیشه زبان پیش‌فرض
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = app(LocaleService::class);
        $default = $locales->defaultCode();

        // ۰) حالت تک‌زبانه — درخواست ?lang نادیده گرفته می‌شود
        if ($locales->singleLanguageMode()) {
            $locale = $default;
        }
        // ۱) اگر در URL کوئری استرینگ ?lang=xx آمده باشد — فقط اگر فعال باشد
        elseif ($request->has('lang') && $locales->isActive($request->get('lang'))) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }
        // ۲) کاربر لاگین با ترجیح ذخیره‌شده در دیتابیس — فقط اگر فعال باشد
        elseif ($request->user() && $locales->isActive((string) $request->user()->locale)) {
            $locale = $request->user()->locale;
            session(['locale' => $locale]);
        }
        // ۳) زبان ذخیره‌شده در session — فقط اگر فعال باشد
        elseif (session()->has('locale') && $locales->isActive((string) session('locale'))) {
            $locale = session('locale');
        }
        // ۴) پیش‌فرض
        else {
            $locale = $default;
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        // جهت متن برای استفاده در ویوها — از سرویس
        view()->share('direction', $locales->isRtl($locale) ? 'rtl' : 'ltr');
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
