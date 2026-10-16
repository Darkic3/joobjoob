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
 * پنل ادمین تک‌زبانه است (config/site-features.php → admin_locale_locked):
 *   همیشه admin_locale (پیش‌فرض fa) اعمال می‌شود؛ ?lang= و user.locale و
 *   session برای زبان نادیده گرفته می‌شوند و چیزی در آن‌ها نوشته نمی‌شود.
 *
 * حالت قفل زبان عمومی (config/site-features.php):
 *   وقتی public_locale_locked=true باشد، سایت عمومی فقط به یک زبان اجرا می‌شود.
 *   هر URL پیشونددار یا ?lang= با 301 به نسخه‌ی canonical بدون پیشوند برمی‌گردد.
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

        // ── پنل ادمین: همیشه تک‌زبانه ─────────────────────────────────────────
        // ?lang= نادیده گرفته می‌شود (بدون ریدایرکت تا بوکمارک‌های قدیمی و
        // فرم‌ها نشکنند) و چیزی در user.locale / session نوشته نمی‌شود.
        if ($isAdmin) {
            $adminLocale = (string) config('site-features.admin_locale', $default);

            // اگر زبان قفل‌شده در دیتابیس نیست، به پیش‌فرض سرویس برگرد
            if (! $locales->has($adminLocale)) {
                $adminLocale = $default;
            }

            app()->setLocale($adminLocale);

            view()->share('direction', $locales->isRtl($adminLocale) ? 'rtl' : 'ltr');
            view()->share('currentLocale', $adminLocale);
            view()->share('publicLocaleLocked', false);

            return $next($request);
        }

        // ── حالت قفل زبان سایت عمومی ─────────────────────────────────────────
        if (config('site-features.public_locale_locked', false)) {
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

        // ── رفتار عادی سایت عمومی (بدون قفل) ─────────────────────────────────
        // (پنل ادمین بالاتر برمی‌گردد و هرگز به این‌جا نمی‌رسد.)

        // استخراج پیشوند زبان از مسیر (مثل en/products → en)
        $segments = explode('/', $request->path());
        $first = $segments[0] ?? '';
        $urlLocale = ($first !== '' && $locales->has($first)) ? $first : null;

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
        elseif (! $locales->singleLanguageMode()
            && $request->has('lang')
            && $locales->isActive((string) $request->get('lang'))
            && (string) $request->get('lang') !== $default) {
            return redirect()->to($this->prefixedPath($request, (string) $request->get('lang')), 301);
        }
        // ۳) حالت تک‌زبانه — درخواست ?lang نادیده
        elseif ($locales->singleLanguageMode()) {
            $locale = $default;
        }
        // ۴) پیش‌فرض (سایت بدون پیشوند و بدون ترجیح معتبر)
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
