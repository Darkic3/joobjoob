<?php

namespace App\Http\Middleware;

use App\Models\Theme;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تشخیص و اعمال تم فعال (Light/Dark/سفارشی).
 * منبع تم: کوکی کاربر (که با JS از localStorage ست می‌شود) > کاربر لاگین > تم پیش‌فرض ادمین
 */
class SetTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $themeSlug = null;

        if ($request->cookie('theme')) {
            $themeSlug = $request->cookie('theme');
        } elseif ($request->user() && $request->user()->theme) {
            $themeSlug = $request->user()->theme;
        }

        if (!$themeSlug) {
            $default = Theme::where('is_default', true)->value('slug');
            $themeSlug = $default ?? 'light-neumorphism';
        }

        view()->share('activeTheme', $themeSlug);

        return $next($request);
    }
}
