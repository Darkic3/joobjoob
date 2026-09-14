<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\RequestPerformanceLog;
use App\Models\SmsLog;
use App\Models\SystemError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * نمایش داشبورد مانیتورینگ سیستم.
     */
    public function index()
    {
        $hours24 = now()->subHours(24);
        $hours1 = now()->subHour();

        // آمار خطاهای سیستمی
        $errors = [
            'unresolved'    => SystemError::unresolved()->count(),
            'total_24h'     => SystemError::recent(24)->count(),
            'critical_24h'  => SystemError::recent(24)->byLevel('critical')->count(),
            'error_24h'     => SystemError::recent(24)->byLevel('error')->count(),
            'warning_24h'   => SystemError::recent(24)->byLevel('warning')->count(),
            'recent'        => SystemError::with('user')->recent(24)->latest()->take(10)->get(),
        ];

        // آمار پرداخت‌ها
        $payments = [
            'total_24h'     => PaymentLog::recent(24)->count(),
            'success_24h'   => PaymentLog::recent(24)->where('status', 'success')->count(),
            'failed_24h'    => PaymentLog::recent(24)->where('status', 'failed')->count(),
            'failure_rate'  => 0,
            'recent_failed' => PaymentLog::recent(24)->failed()->latest()->take(5)->get(),
        ];
        if ($payments['total_24h'] > 0) {
            $payments['failure_rate'] = round(($payments['failed_24h'] / $payments['total_24h']) * 100, 1);
        }

        // آمار SMS
        $sms = [
            'total_24h'     => SmsLog::recent(24)->count(),
            'sent_24h'      => SmsLog::recent(24)->where('status', 'sent')->count(),
            'failed_24h'    => SmsLog::recent(24)->where('status', 'failed')->count(),
            'skipped_24h'   => SmsLog::recent(24)->where('status', 'skipped')->count(),
            'failure_rate'  => 0,
            'recent_failed' => SmsLog::recent(24)->failed()->latest()->take(5)->get(),
        ];
        if ($sms['total_24h'] > 0) {
            $sms['failure_rate'] = round(($sms['failed_24h'] / $sms['total_24h']) * 100, 1);
        }

        // آمار پرفورمانس
        $performance = [
            'total_24h'        => RequestPerformanceLog::recent(24)->count(),
            'slow_24h'         => RequestPerformanceLog::recent(24)->slow()->count(),
            'avg_response_ms'  => round(RequestPerformanceLog::recent(24)->avg('response_time_ms') ?? 0),
            'recent_slow'      => RequestPerformanceLog::recent(24)->slow()->latest()->take(5)->get(),
        ];

        // آمار Failed Jobs
        $failedJobs = [
            'total'    => DB::table('failed_jobs')->count(),
            'recent'   => DB::table('failed_jobs')->latest()->take(5)->get(),
        ];

        // وضعیت کلی سیستم
        $systemStatus = $this->determineSystemStatus($errors, $payments, $sms, $performance);

        return view('admin.monitoring.index', compact(
            'errors',
            'payments',
            'sms',
            'performance',
            'failedJobs',
            'systemStatus'
        ));
    }

    /**
     * علامت‌گذاری یک خطا به عنوان حل‌شده.
     */
    public function resolveError(Request $request, int $errorId)
    {
        $error = SystemError::findOrFail($errorId);
        $error->markResolved($request->input('note', ''));

        return back()->with('success', 'خطا به عنوان حل‌شده علامت‌گذاری شد.');
    }

    /**
     * تعیین وضعیت کلی سیستم بر اساس آمار.
     */
    private function determineSystemStatus(array $errors, array $payments, array $sms, array $performance): array
    {
        $issues = [];

        if ($errors['critical_24h'] > 0) {
            $issues[] = 'خطاهای بحرانی ثبت شده';
        }

        if ($errors['unresolved'] > 10) {
            $issues[] = 'تعداد خطاهای حل‌نشده زیاد است';
        }

        if ($payments['failure_rate'] > 30) {
            $issues[] = 'نرخ شكست پرداخت بالاست';
        }

        if ($sms['failure_rate'] > 50) {
            $issues[] = 'نرخ شكست SMS بالاست';
        }

        if ($performance['slow_24h'] > 10) {
            $issues[] = 'درخواست‌های کند زیاد هستند';
        }

        if (empty($issues)) {
            return [
                'status'  => 'healthy',
                'label'   => 'سالم',
                'color'   => 'success',
                'icon'    => 'check-circle',
                'message' => 'همه چیز نرمال است',
            ];
        }

        $hasCritical = $errors['critical_24h'] > 0 || $payments['failure_rate'] > 50;

        return [
            'status'  => $hasCritical ? 'critical' : 'warning',
            'label'   => $hasCritical ? 'بحرانی' : 'هشدار',
            'color'   => $hasCritical ? 'danger' : 'warning',
            'icon'    => $hasCritical ? 'exclamation-circle' : 'exclamation-triangle',
            'message' => implode(' | ', $issues),
        ];
    }
}
