<?php

namespace App\Console\Commands;

use App\Models\PaymentLog;
use App\Models\RequestPerformanceLog;
use App\Models\SmsLog;
use App\Models\SystemError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * پاکسازی لاگ‌های قدیمی بر اساس سیاست نگهداری.
 *
 * Usage: php artisan monitoring:cleanup
 * Schedule: روزانه ساعت ۳ صبح
 */
class CleanupLogs extends Command
{
    protected $signature = 'monitoring:cleanup
                    {--performance-days= : تعداد روز نگهداری لاگ پرفورمانس (پیش‌فرض: config)}
                    {--errors-days= : تعداد روز نگهداری خطاهای سیستمی (پیش‌فرض: config)}
                    {--payments-days= : تعداد روز نگهداری لاگ پرداخت (پیش‌فرض: config)}
                    {--sms-days= : تعداد روز نگهداری لاگ SMS (پیش‌فرض: config)}
                    {--request-logs-days= : تعداد روز نگهداری request_logs (پیش‌فرض: config)}';

    protected $description = 'پاکسازی لاگ‌های قدیمی بر اساس سیاست نگهداری';

    public function handle(): int
    {
        $retention = config('monitoring.retention', []);

        $totalDeleted = 0;

        // ۱. لاگ پرفورمانس (۷ روز)
        $days = (int) ($this->option('performance-days') ?? ($retention['performance_logs'] ?? 7));
        $deleted = RequestPerformanceLog::where('created_at', '<', now()->subDays($days))->delete();
        $totalDeleted += $deleted;
        $this->line("Performance logs (>{$days}d): {$deleted} حذف شد");

        // ۲. خطاهای سیستمی حل‌شده (۶۰ روز)
        $days = (int) ($this->option('errors-days') ?? ($retention['system_errors'] ?? 60));
        $deleted = SystemError::where('is_resolved', true)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
        $totalDeleted += $deleted;
        $this->line("System errors (resolved, >{$days}d): {$deleted} حذف شد");

        // ۳. لاگ پرداخت (۹۰ روز)
        $days = (int) ($this->option('payments-days') ?? ($retention['payment_logs'] ?? 90));
        $deleted = PaymentLog::where('created_at', '<', now()->subDays($days))->delete();
        $totalDeleted += $deleted;
        $this->line("Payment logs (>{$days}d): {$deleted} حذف شد");

        // ۴. لاگ SMS (۳۰ روز)
        $days = (int) ($this->option('sms-days') ?? ($retention['sms_logs'] ?? 30));
        $deleted = SmsLog::where('created_at', '<', now()->subDays($days))->delete();
        $totalDeleted += $deleted;
        $this->line("SMS logs (>{$days}d): {$deleted} حذف شد");

        // ۵. Request logs (۳۰ روز)
        $days = (int) ($this->option('request-logs-days') ?? ($retention['request_logs'] ?? 30));
        $deleted = DB::table('request_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
        $totalDeleted += $deleted;
        $this->line("Request logs (>{$days}d): {$deleted} حذف شد");

        $this->newLine();
        $this->info("✅ مجموع ردیف‌های حذف‌شده: {$totalDeleted}");

        Log::info('monitoring:cleanup completed', ['total_deleted' => $totalDeleted]);

        return Command::SUCCESS;
    }
}
