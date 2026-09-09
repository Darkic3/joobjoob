<?php

use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\LocalizeHtmlDigits;
use App\Http\Middleware\LogAdminRequests;
use App\Http\Middleware\RefreshRememberMeCookie;
use App\Http\Middleware\SecureHeadersMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTheme;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'adminPermission'  => AdminPermission::class,
            'log.admin'        => LogAdminRequests::class,
            'refreshRememberMe' => RefreshRememberMeCookie::class,
        ]);

        // SetTheme آخرین لایه‌ی درخواست است؛ LocalizeHtmlDigits باید بعد از آن اجرا شود
        // چون خانوادهٔ ارقام را از زبانی که SetLocale اعمال کرده می‌خواند.
        $middleware->web([
            SecureHeadersMiddleware::class, // Security headers (OWASP)
            SetLocale::class,
            SetTheme::class,
            LocalizeHtmlDigits::class,
        ]);

        // مهمان‌هایی که به روت‌های محافظت‌شده (auth) می‌روند → لاگین (ادمین یا سایت)
        $middleware->redirectGuestsTo(function (Request $request) {
            return str_starts_with($request->path(), 'admin')
                ? route('admin.login')
                : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 404 — صفحه یافت نشد
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'صفحه مورد نظر یافت نشد.'], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        // 429 — درخواست زیاد
        $exceptions->renderable(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'درخواست‌های شما بیش از حد مجاز است. لطفاً کمی صبر کنید.'], 429);
            }
            return response()->view('errors.429', [], 429);
        });

        // 500 — خطای داخلی سرور
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            \Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'خطای داخلی سرور رخ داد.'], 500);
            }
            return response()->view('errors.500', [], 500);
        });

        // 419 — CSRF token منقضی
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'جلسه شما منقضی شده. لطفاً صفحه را رفرش کنید.'], 419);
            }
            return redirect()->back()->with('error', 'جلسه شما منقضی شده. لطفاً دوباره تلاش کنید.');
        });
    })->create();
