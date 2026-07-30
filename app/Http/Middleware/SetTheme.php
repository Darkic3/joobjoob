<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $activeTheme = $request->cookie('site_theme') ?? 'light-neumorphism';

        view()->share('activeTheme', $activeTheme);

        return $next($request);
    }
}
