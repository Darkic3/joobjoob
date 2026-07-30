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
        $themeSlug = $request->cookie('theme')
            ?? ($request->user()?->theme)
            ?? (Theme::where('is_default', true)->value('slug') ?? 'light-neumorphism');

        $theme = Theme::where('slug', $themeSlug)->first();

        view()->share('activeTheme', $themeSlug);
        view()->share('themeVariables', $theme ? $theme->getMergedCssVariables() : []);

        return $next($request);
    }
}
