<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * محافظ RBAC پنل ادمین — طبق بلوپرینت documents/AUTH_ADMIN_REPORT.md
 *
 * ترتیب منطق:
 *  ۱) لاگین نیست → ریدایرکت به لاگین (یا 401 برای AJAX)
 *  ۲) سوپرادمین (is_admin) → عبور بدون چک (هرگز قفل نمی‌شود)
 *  ۳) روت‌های لیست سفید (فقط احراز هویت) → عبور
 *  ۴) در غیر این صورت → وجود نام روت در دسترسی‌های نقش‌های کاربر (با کش)
 */
class AdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => __('admin.login.restricted')], 401)
                : redirect()->guest(route('admin.login'));
        }

        if ($user->is_admin) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, config('permissions.free', []), true)) {
            return $next($request);
        }

        abort_unless($routeName && $user->canAccess($routeName), 403);

        return $next($request);
    }
}
