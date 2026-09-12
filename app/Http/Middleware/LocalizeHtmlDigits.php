<?php

namespace App\Http\Middleware;

use App\Helpers\NumberHelper;
use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * بومی‌سازی سراسری ارقام در خروجی HTML — «قبل از اولین paint».
 *
 * چرا لازم است؟
 *   بومی‌سازی فقط با JS (MutationObserver بعد از بارگذاری صفحه) یعنی رقم‌های
 *   متنِ استاتیکِ سرور ابتدا با ارقام لاتین paint می‌شوند و چند لحظه بعد
 *   فارسی/عربی می‌شوند (چشمک انگلیسی→فارسی). این میدلور همان تبدیل را
 *   سمت سرور و روی کلِ خروجیِ HTML اعمال می‌کند تا مرورگر از همان فریمِ اول
 *   رقم‌ها را در خانوادهٔ درست ببیند.
 *
 * قواعد (کاملاً هماهنگ با localize-digits.js و NumberHelper::localizeHtmlText):
 *   - فقط متنِ قابل مشاهدهٔ بین تگ‌ها تبدیل می‌شود؛ صفت‌ها، URLها، مقادیر فرم‌ها
 *     و محتوای script/style/textarea/pre/code/select دست نمی‌خورند.
 *   - خانوادهٔ ارقام از LocaleService::digitFamily(app()->getLocale()) خوانده
 *     می‌شود — همان منبعِ data-digits روی <html>.
 *   - برای خانوادهٔ لاتین (en/tr) هم ارقام فارسی/عربیِ داخلِ متن به لاتین برمی‌گردند
 *     تا خروجی با حالت نهاییِ JS یکسان باشد.
 *   - روی پاسخ‌های غیر-HTML (JSON، تصویر، PDF، دانلود، ریدایرکت) اعمال نمی‌شود.
 *
 * @see docs/digit-localization.md
 */
class LocalizeHtmlDigits
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // پاسخ‌های فایلی/استریم/ریدایرکت را رد کن
        if ($response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || $response->isRedirection()) {
            return $response;
        }

        // فقط HTML را بومی‌سازی کن. پاسخ‌های معمول Blade هنوز هدر ندارند (prepare
        // بعداً انجام می‌شود) و در این مرحله HTML فرض می‌شوند.
        //
        // ⚠️ عمداً `text/*` هم رد می‌شود، نه فقط انواع غیرمتن: محتوای
        // `text/plain` (robots.txt) و `text/xml` هرگز نباید ارقامشان عوض شود —
        // robots.txt با ارقام فارسی عملاً برای خزنده‌ها بی‌معنا می‌شود و sitemap
        // با تاریخ فارسی نامعتبر است. فقط HTML واقعی تبدیل می‌شود.
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || trim($content) === '') {
            return $response;
        }

        // خانوادهٔ ارقام زبانِ این درخواست (SetLocale قبلاً زبان را اعمال کرده)
        $family = app(LocaleService::class)->digitFamily(app()->getLocale());

        $localized = NumberHelper::localizeHtmlText($content, $family);

        if ($localized !== $content) {
            $response->setContent($localized);
        }

        return $response;
    }
}
