<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تشخیص و اعمال زبان فعال کاربر.
 * اولویت: پارامتر URL > کاربر لاگین‌شده در دیتابیس > session > مرورگر > پیش‌فرض
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales', ['fa' => 'فارسی']));

        // ۱. اگر در URL کوئری استرینگ ?lang=xx آمده باشد (از سوییچر زبان)
        if ($request->has('lang') && in_array($request->get('lang'), $available)) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }
        // ۲. کاربر لاگین با ترجیح ذخیره‌شده در دیتابیس
        elseif ($request->user() && $request->user()->locale && in_array($request->user()->locale, $available)) {
            $locale = $request->user()->locale;
            session(['locale' => $locale]);
        }
        // ۳. زبان ذخیره‌شده در session
        elseif (session()->has('locale') && in_array(session('locale'), $available)) {
            $locale = session('locale');
        }
        // ۴. پیش‌فرض اپلیکیشن
        else {
            $locale = config('app.locale');
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        // جهت متن برای استفاده در ویوها
        $rtlLocales = config('app.rtl_locales', ['fa', 'ar']);
        view()->share('direction', in_array($locale, $rtlLocales) ? 'rtl' : 'ltr');
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
