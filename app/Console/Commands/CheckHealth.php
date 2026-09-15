<?php

namespace App\Console\Commands;

use App\Models\PaymentLog;
use App\Models\RequestPerformanceLog;
use App\Models\SmsLog;
use App\Models\SystemError;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * بررسی سلامت سیستم و ارسال اعلان در صورت وجود مشکل بحرانی.
 *
 * Usage: php artisan monitoring:check-health
 * Schedule: هر ساعت اجرا شود
 */
class CheckHealth extends Command
{
    protected $signature = 'monitoring:check-health';

    protected $description = 'بررسی سلامت سیستم و ارسال اعلان در صورت وجود مشکل';

    public function handle(): int
    {
        // واچ‌داگ ساعتی نباید خودش crash کند — هر خطای غیرمنتظره لاگ و FAILURE می‌شود.
        try {
            return $this->runChecks();
        } catch (\Throwable $e) {
            Log::error('monitoring:check-health failed', ['message' => $e->getMessage()]);
            $this->error('❌ خطا در اجرای بررسی سلامت: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function runChecks(): int
    {
        $issues = [];
        $config = config('monitoring.alerts', []);

        // ۱. بررسی خطاهای اخیر
        $errorsPerHour = SystemError::recent(1)->count();
        $threshold = $config['errors_per_hour'] ?? 10;
        if ($errorsPerHour >= $threshold) {
            $issues[] = "تعداد خطاهای سیستمی در ساعت اخیر: {$errorsPerHour} (آستانه: {$threshold})";
        }

        // ۲. بررسی نرخ شكست پرداخت
        $paymentTotal = PaymentLog::recent(1)->count();
        $paymentFailed = PaymentLog::recent(1)->failed()->count();
        if ($paymentTotal > 0) {
            $failRate = ($paymentFailed / $paymentTotal) * 100;
            $threshold = $config['payment_failure_rate'] ?? 30;
            if ($failRate >= $threshold) {
                $issues[] = "نرخ شكست پرداخت: {$failRate}% (آستانه: {$threshold}%) — {$paymentFailed}/{$paymentTotal}";
            }
        }

        // ۳. بررسی نرخ شكست SMS
        $smsTotal = SmsLog::recent(1)->count();
        $smsFailed = SmsLog::recent(1)->failed()->count();
        if ($smsTotal > 0) {
            $failRate = ($smsFailed / $smsTotal) * 100;
            $threshold = $config['sms_failure_rate'] ?? 50;
            if ($failRate >= $threshold) {
                $issues[] = "نرخ شكست SMS: {$failRate}% (آستانه: {$threshold}%) — {$smsFailed}/{$smsTotal}";
            }
        }

        // ۴. بررسی درخواست‌های کند
        $slowCount = RequestPerformanceLog::recent(1)->slow()->count();
        $threshold = $config['slow_requests_per_hour'] ?? 10;
        if ($slowCount >= $threshold) {
            $issues[] = "درخواست‌های کند در ساعت اخیر: {$slowCount} (آستانه: {$threshold})";
        }

        // ۵. بررسی Failed Jobs — جدول ممکن است روی بعضی هاست‌ها نباشد
        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
            Log::warning('monitoring:check-health: failed_jobs query failed', ['error' => $e->getMessage()]);
            $failedJobs = 0;
        }
        if ($failedJobs > 0) {
            $issues[] = "تعداد Failed Jobs: {$failedJobs}";
        }

        // نتیجه
        if (empty($issues)) {
            $this->info('✅ وضعیت سیستم نرمال است.');
            Log::info('monitoring:check-health — وضعیت نرمال');
            return Command::SUCCESS;
        }

        $message = "⚠️ مشکلات شناسایی شده:\n" . implode("\n", array_map(fn($i) => "• {$i}", $issues));

        $this->warn($message);
        Log::warning('monitoring:check-health', ['issues' => $issues]);

        // ارسال اعلان به ادمین‌ها
        try {
            $notifService = app(NotificationService::class);
            $notifService->notifyAdmins(
                'system.health_warning',
                'هشدار سلامت سیستم',
                $message,
                route('admin.monitoring.index'),
                ['issues' => $issues]
            );
        } catch (\Throwable $e) {
            Log::error('Health check notification failed', ['message' => $e->getMessage()]);
        }

        return Command::FAILURE;
    }
}
