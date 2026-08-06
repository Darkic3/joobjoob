<?php

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
            'adminPermission' => \App\Http\Middleware\AdminPermission::class,
            'log.admin' => \App\Http\Middleware\LogAdminRequests::class,
        ]);

        $middleware->prepend([
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetTheme::class,
        ]);

        // مهمان‌هایی که به روت‌های محافظت‌شده (auth) می‌روند → لاگین ادمین
        $middleware->redirectGuestsTo(function (Request $request) {
            return str_starts_with($request->path(), 'admin')
                ? route('admin.login')
                : url('/');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
