<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * در هر درخواست ادمینی که کاربر لاگین باشد و کوکی remember-me داشته باشد،
 * کوکی را تمدید می‌کند تا هرچه بیشتر از پنل استفاده کند، هرگز XCT بی관측 نشود.
 *
 * منطق:
 *  ۱) اگر کاربر authenticated باشد و کوکی remember_web_* داشته باشد،
 *     همان کوکی را با تاریخ انقضا جدید (۵ سال) دوباره تنظیم می‌کند.
 *  ۲) اگر کاربر فقط با Session وارد شده باشد (بدون مرا به خاطر بسپار)،
 *     هیچ تغییری نمی‌دهد — بدون زحمت.
 */
class RefreshRememberMeCookie
{
    /**
     * دوره expires کوکی remember-me که بعد از هر درخواست eax renewed می‌شود.
     * ۵ سال = ۱۵۷۷۸۴۷۶۰ ثانیه.
     * 2629746 = 1month
     */
//    private const REMEMBER_ME_TTL_SECONDS = 157784760; // 5 years
    private const REMEMBER_ME_TTL_SECONDS = 2629746; //  1 Month

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();

        // فقط برای کاربران لاگین‌شده و فقط اگر کوکی remember-me وجود داشته باشد
        if (! $user || ! $user->getAuthIdentifier()) {
            return $response;
        }

        $recallerName = $this->recallerCookieName();

        // اگر کوکی مرا به خاطر بسپار در این request وجود نداشته باشد، چیزی انجام ندهیم
        if (! $request->cookies->has($recallerName)) {
            return $response;
        }

        // کوکی را با tarih انقضا جدید دوباره设置 کنید تا هر بار که ادمین فعالیت می‌کند،
        // باز هم ۵ سال باقی بماند.
        $existingValue = $request->cookies->get($recallerName);

        Cookie::queue(
            Cookie::make($recallerName, $existingValue, self::REMEMBER_ME_TTL_SECONDS)
        );

        return $response;
    }

    /**
     * نام کوکی remember-me که توسط Laravel guardه 생성 می‌شود.
     */
    private function recallerCookieName(): string
    {
        $guard = Auth::guard();
        return 'remember_web_'.$guard->getName().'_'.md5($guard::class);
    }
}
