<?php

use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\LogAdminRequests;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTheme;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'adminPermission' => AdminPermission::class,
            'log.admin' => LogAdminRequests::class,
        ]);

        $middleware->web([
            SetLocale::class,
            SetTheme::class,
        ]);

        // مهمان‌هایی که به روت‌های محافظت‌شده (auth) می‌روند → لاگین (ادمین یا سایت)
        $middleware->redirectGuestsTo(function (Request $request) {
            return str_starts_with($request->path(), 'admin')
                ? route('admin.login')
                : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
