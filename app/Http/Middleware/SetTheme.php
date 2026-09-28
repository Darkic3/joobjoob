<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تعیین تم فعال صفحه (data-theme روی <html>).
 *
 * حالت قفل تم سایت عمومی (config/site-features.php):
 *   وقتی public_theme_locked=true باشد، سایت عمومی همیشه به یک تم ثابت
 *   (public_theme، پیش‌فرض: light) اجرا می‌شود و cookie/localStorage کاربر
 *   نادیده گرفته می‌شود. هیچ theme toggle در UI سایت نمایش داده نمی‌شود.
 *
 *   پنل ادمین از این قفل مستثنا است و می‌تواند از cookie خودش (site_theme)
 *   برای انتخاب تم استفاده کند — رفتار قبلی دست‌نخورده می‌ماند.
 */
class SetTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = str_starts_with($request->path(), 'admin');

        if (! $isAdmin && config('site-features.public_theme_locked', false)) {
            // سایت عمومی: تم قفل‌شده، مستقل از cookie/localStorage کاربر
            $activeTheme = (string) config('site-features.public_theme', 'light-neumorphism');
        } else {
            // پنل ادمین یا سایت بدون قفل: cookie کاربر (fallback به light)
            $activeTheme = $request->cookie('site_theme') ?? 'light-neumorphism';
        }

        view()->share('activeTheme', $activeTheme);

        return $next($request);
    }
}
