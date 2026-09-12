<?php

namespace App\Http\Middleware;

use App\Services\Seo\SeoResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * هدر `X-Robots-Tag` برای صفحه‌هایی که سیاست سخت مسیر آن‌ها را نوایندکس می‌کند.
 *
 * چرا هم تگ متا و هم هدر: تگ متا فقط داخل HTML اثر دارد. اگر روزی یکی از این
 * روت‌ها پارشال/فایلی برگرداند (مثلاً دانلود صورتحساب سفارش یا خروجی JSON)،
 * تگ متا اصلاً رندر نمی‌شود و صفحه می‌تواند ناخواسته ایندکس شود. هدر HTTP
 * مستقل از بدنه است و همان‌جا جلوی ایندکس را می‌گیرد.
 *
 * ⚠️ سیاست از `SeoResolver::routePolicy()` می‌آید — همان منبعی که تگ متا را
 * می‌سازد؛ پس هدر و تگ متا هرگز با هم اختلاف ندارند. ردیف‌های seo_meta اینجا
 * خوانده نمی‌شوند (بدون کوئری و پیش از اجرای کنترلر).
 */
class SeoHeaders
{
    public function __construct(private readonly SeoResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // فقط HTML — هدر robots روی XML/JSON/فایل بی‌اثر است و فقط خروجی را شلوغ می‌کند
        if (! $this->isHtml($response)) {
            return $response;
        }

        $policy = $this->resolver->routePolicy($request);

        if ($policy === null || $policy['indexable'] === true) {
            return $response;
        }

        $response->headers->set(
            'X-Robots-Tag',
            'noindex, '.($policy['followable'] ? 'follow' : 'nofollow')
        );

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        // در این مرحله (پیش از prepare) پاسخ‌های Blade معمولاً هدر ندارند ⇒ HTML فرض می‌شوند
        return $contentType === '' || str_contains($contentType, 'text/html');
    }
}
