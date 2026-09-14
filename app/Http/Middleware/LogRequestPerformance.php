<?php

namespace App\Http\Middleware;

use App\Jobs\LogPerformanceJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware برای ثبت زمان پاسخ هر درخواست HTTP.
 *
 * - درخواست‌های کند (> slow_threshold_ms) همیشه ثبت می‌شوند
 * - در حالت strict فقط درخواست‌های بالای ۱ ثانیه ثبت می‌شوند
 * - فایل‌های استاتیک و مسیرهای غیرفعال هرگز ثبت نمی‌شوند
 */
class LogRequestPerformance
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        $slowThreshold = config('monitoring.slow_threshold_ms', 2000);
        $verySlowThreshold = config('monitoring.very_slow_threshold_ms', 5000);
        $strict = config('monitoring.strict', false);

        // آیا این درخواست باید ثبت شود؟
        if ($this->shouldLog($request, $responseTimeMs, $strict)) {
            $this->dispatchLog($request, $response, $responseTimeMs);
        }

        // هشدار برای درخواست‌های خیلی کند
        if ($responseTimeMs >= $verySlowThreshold) {
            Log::warning("Very slow request detected", [
                'path'             => $request->path(),
                'method'           => $request->method(),
                'response_time_ms' => $responseTimeMs,
                'status_code'      => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * تعیین اینکه آیا درخواست باید ثبت شود.
     */
    private function shouldLog(Request $request, int $responseTimeMs, bool $strict): bool
    {
        if (! config('monitoring.log_performance', true)) {
            return false;
        }

        // مسیرهای حذف‌شده هرگز ثبت نمی‌شوند
        $excludedPaths = config('monitoring.excluded_paths', []);
        foreach ($excludedPaths as $pattern) {
            if (Str::is($pattern, $request->path())) {
                return false;
            }
        }

        // فایل‌های استاتیک
        if ($this->isStaticAsset($request)) {
            return false;
        }

        // درخواست‌های کند همیشه ثبت می‌شوند
        $slowThreshold = config('monitoring.slow_threshold_ms', 2000);
        if ($responseTimeMs >= $slowThreshold) {
            return true;
        }

        // در حالت strict، درخواست‌های بالای ۱ ثانیه
        if ($strict && $responseTimeMs >= 1000) {
            return true;
        }

        return false;
    }

    /**
     * بررسی اینکه آیا درخواست فایل استاتیک است.
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        $extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];

        foreach ($extensions as $ext) {
            if (Str::endsWith($path, ".{$ext}")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dispatch لاگ به صورت غیرهمزمان.
     */
    private function dispatchLog(Request $request, Response $response, int $responseTimeMs): void
    {
        try {
            $slowThreshold = config('monitoring.slow_threshold_ms', 2000);

            LogPerformanceJob::dispatch([
                'method'            => $request->method(),
                'path'              => mb_substr($request->path(), 0, 500),
                'status_code'       => $response->getStatusCode(),
                'response_time_ms'  => $responseTimeMs,
                'ip'                => $request->ip(),
                'user_id'           => $request->user()?->id,
                'is_slow'           => $responseTimeMs >= $slowThreshold,
            ]);
        } catch (\Throwable $e) {
            // If queue dispatch fails, don't break the request
            Log::error('LogPerformanceJob dispatch failed', [
                'path'    => $request->path(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
