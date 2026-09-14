<?php

namespace App\Services;

use App\Jobs\LogSmsJob;
use Illuminate\Support\Facades\Log;

/**
 * سرویس ثبت لاگ SMS — به صورت غیرهمزمان (Queue) اجرا می‌شود.
 *
 * نرخ موفقیت/شكست ارسال پیامک‌ها را قابل بررسی می‌کند.
 */
class SmsLogService
{
    /**
     * ثبت لاگ ارسال SMS.
     *
     * @param  string  $type  otp, notification, welcome, bulk
     * @param  string  $mobile
     * @param  string  $status  sent, failed, skipped
     * @param  string|null  $template  kavenegar template name
     * @param  string|null  $errorMessage
     * @param  int|null  $userId
     * @param  int|null  $relatedId  order_id, etc.
     * @param  string|null  $relatedType
     */
    public static function record(
        string $type,
        string $mobile,
        string $status,
        ?string $template = null,
        ?string $errorMessage = null,
        ?int $userId = null,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): void {
        try {
            if (! config('monitoring.log_sms', true)) {
                return;
            }

            LogSmsJob::dispatch([
                'type'           => $type,
                'template'       => $template,
                'mobile'         => $mobile,
                'status'         => $status,
                'error_message'  => $errorMessage,
                'user_id'        => $userId,
                'related_id'     => $relatedId,
                'related_type'   => $relatedType,
            ]);
        } catch (\Throwable $e) {
            // If queue dispatch fails, log to file as fallback
            Log::error('SmsLog dispatch failed', [
                'type'     => $type,
                'mobile'   => $mobile,
                'status'   => $status,
                'message'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * ثبت موفقیت ارسال.
     */
    public static function recordSuccess(
        string $type,
        string $mobile,
        ?string $template = null,
        ?int $userId = null
    ): void {
        static::record($type, $mobile, 'sent', $template, null, $userId);
    }

    /**
     * ثبت شكست ارسال.
     */
    public static function recordFailure(
        string $type,
        string $mobile,
        ?string $errorMessage = null,
        ?string $template = null,
        ?int $userId = null
    ): void {
        static::record($type, $mobile, 'failed', $template, $errorMessage, $userId);
    }

    /**
     * ثبت رد شدن (مثلاً در محیط local).
     */
    public static function recordSkipped(
        string $type,
        string $mobile,
        ?string $template = null
    ): void {
        static::record($type, $mobile, 'skipped', $template);
    }
}
