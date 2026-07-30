<?php

namespace App\Http\Middleware;

use App\Models\Theme;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $themeSlug = null;

        // ۱. اولویت با کوکی (انتخاب دستی کاربر)
        if ($request->cookie('theme')) {
            $themeSlug = $request->cookie('theme');
        }
        // ۲. سپس تنظیمات ذخیره‌شده برای کاربر لاگین‌کرده
        elseif ($request->user() && $request->user()->theme) {
            $themeSlug = $request->user()->theme;
        }

        // ۳. در نهایت، تم پیش‌فرض سایت از دیتابیس
        if (!$themeSlug) {
            $defaultTheme = Theme::where('is_default', true)->first();
            $themeSlug = $defaultTheme ? $defaultTheme->slug : 'light-neumorphism';
        }

        // دریافت اطلاعات تم از دیتابیس
        $theme = Theme::where('slug', $themeSlug)->first();

        // متغیرهای پیش‌فرض (Fallback)
        $defaultVars = [
            '--bg' => '#e0e5ec',
            '--bg-elevated' => '#e0e5ec',
            '--shadow-light' => '#ffffff',
            '--shadow-dark' => '#a3b1c6',
            '--text-primary' => '#2d3748',
            '--text-secondary' => '#6b7788',
            '--accent' => '#6c63ff',
            '--accent-contrast' => '#ffffff',
            '--danger' => '#e53e5c',
            '--success' => '#38a169',
        ];

        // ترکیب متغیرهای پیش‌فرض با متغیرهای سفارشی ذخیره‌شده در دیتابیس
        $mergedVars = $defaultVars;
        if ($theme && !empty($theme->css_variables)) {
            $customVars = is_array($theme->css_variables)
                ? $theme->css_variables
                : json_decode($theme->css_variables, true);

            if (is_array($customVars)) {
                $mergedVars = array_merge($defaultVars, $customVars);
            }
        }

        // اشتراک‌گذاری متغیرها با تمام ویوها (شامل app.blade.php شما)
        view()->share('activeTheme', $themeSlug);
        view()->share('themeVariables', $mergedVars);

        return $next($request);
    }
}
