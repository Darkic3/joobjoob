<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تشخیص و اعمال زبان فعال کاربر — منبع واحد حقیقت: LocaleService (جدول languages).
 *
 * SEO: روت‌های سایت نسخه‌ی پیشونددار دارند (routes/site.php → locale.*):
 *   - /en/...        → زبان از پیشوند مسیر می‌آید (اولویت اول)
 *   - /fa/... (پیش‌فرض پیشونددار) → 301 به نسخه‌ی بدون پیشوند (canonical)
 *   - /xx/... (غیرفعال/ناموجود)    → 301 به نسخه‌ی بدون پیشوند (پیش‌فرض)
 *   - /...  (بدون پیشوند)          → زبان پیش‌فرض (canonical — مستقل از کاربر)
 *   - /...?lang=en (بوکمارک قدیمی) → 301 به /en/... (سازگاری با عقب — SEO)
 *
 * پنل ادمین (بدون پیشوند) زنجیره‌ی قدیمی را حفظ می‌کند:
 *   ?lang= → کاربر لاگین‌شده → session → پیش‌فرض
 *
 * حالت قفل زبان عمومی (config/site-features.php):
 *   وقتی public_locale_locked=true باشد، سایت عمومی فقط به یک زبان اجرا می‌شود.
 *   هر URL پیشونددار یا ?lang= با 301 به نسخه‌ی canonical بدون پیشوند برمی‌گردد.
 *   پنل ادمین از این قفل مستثنا است و رفتار چندزبانه‌اش دست‌نخورده می‌ماند.
 *
 * ⚠️ این میدلور در گروه وب است و پیش از resolve شدن روت اجرا می‌شود —
 * پس `$request->route('locale')` در دسترس نیست و پیشوند را از `$request->path()` می‌خوانیم.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = app(LocaleService::class);
        $default = $locales->defaultCode();
        $isAdmin = str_starts_with($request->path(), 'admin');

        // ── حالت قفل زبان سایت عمومی ─────────────────────────────────────────
        // فقط سایت عمومی قفل می‌شود؛ ادمین دست‌نخورده می‌ماند.
        if (! $isAdmin && config('site-features.public_locale_locked', false)) {
            $publicLocale = (string) config('site-features.public_locale', $default);

            // هر مسیر پیشونددار (/en/...، /xx/...) → 301 به نسخه‌ی بدون پیشوند
            $segments = explode('/', $request->path());
            $first = $segments[0] ?? '';

            if ($first !== '' && $locales->has($first)) {
                return redirect()->to($this->canonicalPath($request, $segments), 301);
            }

            // هر ?lang=xx → 301 به همان مسیر بدون پارامتر
            if ($request->query->has('lang')) {
                $query = $request->query->all();
                unset($query['lang']);

                $clean = '/'.ltrim($request->path(), '/');

                return redirect()->to(
                    $clean.(count($query) ? '?'.http_build_query($query) : ''),
                    301
                );
            }

            // زبان عمومی قفل‌شده را اعمال کن
            app()->setLocale($publicLocale);
            session(['locale' => $publicLocale]);

            view()->share('direction', $locales->isRtl($publicLocale) ? 'rtl' : 'ltr');
            view()->share('currentLocale', $publicLocale);
            view()->share('publicLocaleLocked', true);

            return $next($request);
        }

        // ── رفتار عادی (پنل ادمین یا سایت عمومی بدون قفل) ─────────────────────

        // استخراج پیشوند زبان از مسیر (مثل en/products → en)
        $segments = explode('/', $request->path());
        $first = $segments[0] ?? '';
        $urlLocale = (! $isAdmin && $first !== '' && $locales->has($first)) ? $first : null;

        // ۱) پیشوند URL (فقط سایت) — اولویت اول
        if ($urlLocale !== null) {
            // زبان غیرفعال یا پیش‌فرضِ پیشونددار → ریدایرکت 301 به نسخه‌ی متعارف
            if (! $locales->isActive($urlLocale) || $urlLocale === $default) {
                return redirect()->to($this->canonicalPath($request, $segments), 301);
            }

            $locale = $urlLocale;
            session(['locale' => $locale]);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }
        // ۲) سازگاری با عقب: ?lang= روی سایتِ بدون پیشوند → 301 به نسخه‌ی پیشونددار
        elseif (! $isAdmin
            && ! $locales->singleLanguageMode()
            && $request->has('lang')
            && $locales->isActive((string) $request->get('lang'))
            && (string) $request->get('lang') !== $default) {
            return redirect()->to($this->prefixedPath($request, (string) $request->get('lang')), 301);
        }
        // ۳) حالت تک‌زبانه — درخواست ?lang نادیده
        elseif ($locales->singleLanguageMode()) {
            $locale = $default;
        }
        // ۴) پنل: پارامتر URL ?lang=xx — فقط اگر فعال باشد
        elseif ($isAdmin && $request->has('lang') && $locales->isActive((string) $request->get('lang'))) {
            $locale = (string) $request->get('lang');
            session(['locale' => $locale]);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }
        // ۵) پنل: کاربر لاگین با ترجیح ذخیره‌شده — فقط اگر فعال باشد
        elseif ($isAdmin && $request->user() && $locales->isActive((string) $request->user()->locale)) {
            $locale = $request->user()->locale;
            session(['locale' => $locale]);
        }
        // ۶) پنل: session — فقط اگر فعال باشد
        elseif ($isAdmin && session()->has('locale') && $locales->isActive((string) session('locale'))) {
            $locale = session('locale');
        }
        // ۷) پیش‌فرض (سایت بدون پیشوند و پنل بدون ترجیح معتبر)
        else {
            $locale = $default;
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        // جهت متن برای استفاده در ویوها — از سرویس
        view()->share('direction', $locales->isRtl($locale) ? 'rtl' : 'ltr');
        view()->share('currentLocale', $locale);
        view()->share('publicLocaleLocked', false);

        return $next($request);
    }

    /**
     * URL متعارف (پیش‌فرض، بدون پیشوند) برای همان مسیر — برای ریدایرکت 301.
     * مثال: /fa/products?q=x → /products?q=x
     */
    private function canonicalPath(Request $request, array $segments): string
    {
        $rest = implode('/', array_slice($segments, 1));
        $url = $rest === '' ? url('/') : url($rest);

        return $request->getQueryString() ? $url.'?'.$request->getQueryString() : $url;
    }

    /**
     * URL پیشونددار برای همان مسیر — برای ریدایرکت 301 سازگاری ?lang=.
     * مثال: /products?lang=en&q=x → /en/products?q=x
     */
    private function prefixedPath(Request $request, string $code): string
    {
        $rest = ltrim($request->getPathInfo(), '/');
        $path = $rest === '' ? "/{$code}" : "/{$code}/{$rest}";

        $query = $request->query();
        unset($query['lang']);

        return $path.(count($query) ? '?'.http_build_query($query) : '');
    }
}
