<?php

namespace App\Services;

use App\Models\SystemError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * سرویس ثبت خطاهای سیستمی — به صورت SYNC اجرا می‌شود.
 *
 * مهم‌ترین لاگ سیستم است چون اگر خطا ثبت نشود، نمی‌توان مشکل را پیدا کرد.
 * هم به دیتابیس و هم به فایل لاگ می‌نویسد (dual-write).
 */
class ErrorLogService
{
    /**
     * ثبت یک خطا در دیتابیس و فایل لاگ.
     *
     * @param  Throwable  $e
     * @param  Request|null  $request
     * @param  string  $level  error, warning, critical
     */
    public static function record(Throwable $e, ?Request $request = null, string $level = 'error'): SystemError
    {
        if (! config('monitoring.enabled', true)) {
            // Even when monitoring is disabled, always log critical errors to file
            Log::channel('daily')->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile() . ':' . $e->getLine(),
            ]);

            return new SystemError(); // Return empty model to prevent null issues
        }

        $context = [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ];

        if ($request) {
            $context['url']    = $request->fullUrl();
            $context['method'] = $request->method();
            $context['ip']     = $request->ip();
        }

        // Log to file as well (dual-write)
        Log::channel('daily')->error($e->getMessage(), [
            'exception' => get_class($e),
            'file'      => $e->getFile() . ':' . $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'context'   => $context,
        ]);

        try {
            return SystemError::create([
                'level'           => $level,
                'exception_class' => get_class($e),
                'message'         => mb_substr($e->getMessage(), 0, 2000),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'trace'           => self::truncateTrace($e->getTraceAsString()),
                'context'         => $context,
                'url'             => $request?->fullUrl(),
                'method'          => $request?->method(),
                'ip'              => $request?->ip(),
                'user_id'         => Auth::id(),
            ]);
        } catch (Throwable $dbError) {
            // If DB write fails, at least the file log has the error
            Log::channel('emergency')->error('SystemError DB write failed', [
                'original_error' => $e->getMessage(),
                'db_error'       => $dbError->getMessage(),
            ]);

            return new SystemError();
        }
    }

    /**
     * ثبت خطای سطح 404 (warning نه error).
     */
    public static function recordNotFound(Request $request): SystemError
    {
        return static::record(
            new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Not found'),
            $request,
            'warning'
        );
    }

    /**
     * آمار خطاهای اخیر.
     */
    public static function unresolvedCount(): int
    {
        return SystemError::unresolved()->count();
    }

    public static function recentErrors(int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return SystemError::recent($hours)->with('user')->latest()->get();
    }

    public static function errorRateByHour(int $hours = 24): \Illuminate\Support\Collection
    {
        return SystemError::recent($hours)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Truncate stack trace to prevent DB bloat.
     */
    private static function truncateTrace(string $trace): array
    {
        $truncated = mb_substr($trace, 0, 2000);

        return explode("\n", $truncated);
    }
}
