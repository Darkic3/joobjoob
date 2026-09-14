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
     * داشبورد اصلی مانیتورینگ.
     */
    public function index()
    {
        $errors = [
            'unresolved'    => SystemError::unresolved()->count(),
            'total_24h'     => SystemError::recent(24)->count(),
            'critical_24h'  => SystemError::recent(24)->byLevel('critical')->count(),
            'error_24h'     => SystemError::recent(24)->byLevel('error')->count(),
            'warning_24h'   => SystemError::recent(24)->byLevel('warning')->count(),
            'recent'        => SystemError::with('user')->recent(24)->latest()->take(10)->get(),
        ];

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

        $performance = [
            'total_24h'       => RequestPerformanceLog::recent(24)->count(),
            'slow_24h'        => RequestPerformanceLog::recent(24)->slow()->count(),
            'avg_response_ms' => round(RequestPerformanceLog::recent(24)->avg('response_time_ms') ?? 0),
            'recent_slow'     => RequestPerformanceLog::recent(24)->slow()->latest()->take(5)->get(),
        ];

        $failedJobs = [
            'total'  => DB::table('failed_jobs')->count(),
            'recent' => DB::table('failed_jobs')->latest()->take(5)->get(),
        ];

        $systemStatus = $this->determineSystemStatus($errors, $payments, $sms, $performance);

        return view('admin.monitoring.index', compact(
            'errors', 'payments', 'sms', 'performance', 'failedJobs', 'systemStatus'
        ));
    }

    /**
     * لیست خطاهای سیستمی با فیلتر.
     */
    public function errors(Request $request)
    {
        $query = SystemError::with('user');

        if ($request->filled('level')) {
            $query->byLevel($request->input('level'));
        }

        if ($request->filled('resolved')) {
            if ($request->input('resolved') === 'yes') {
                $query->where('is_resolved', true);
            } else {
                $query->unresolved();
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('exception_class', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $errors = $query->latest()->paginate(20)->withQueryString();

        return view('admin.monitoring.errors', compact('errors'));
    }

    /**
     * جزئیات یک خطا.
     */
    public function showError(SystemError $error)
    {
        $error->load('user');

        return view('admin.monitoring.error-show', compact('error'));
    }

    /**
     * حل دسته‌ای خطاهای حل‌نشده.
     */
    public function bulkResolveErrors(Request $request)
    {
        $note = $request->input('note', 'حل دسته‌ای توسط ادمین');
        SystemError::unresolved()->update([
            'is_resolved'   => true,
            'resolved_note' => $note,
        ]);

        return back()->with('success', 'همه خطاهای حل‌نشده علامت‌گذاری شدند.');
    }

    /**
     * حذف خطاهای حل‌شده قدیمی (بیشتر از ۳۰ روز).
     */
    public function deleteResolvedErrors()
    {
        $deleted = SystemError::where('is_resolved', true)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        return back()->with('success', "{$deleted} خطای حل‌شده قدیمی حذف شد.");
    }

    /**
     * لیست لاگ پرداخت‌ها با فیلتر.
     */
    public function payments(Request $request)
    {
        $query = PaymentLog::query();

        if ($request->filled('gateway')) {
            $query->byGateway($request->input('gateway'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhere('order_id', $search);
            });
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.monitoring.payments', compact('payments'));
    }

    /**
     * لیست لاگ SMS با فیلتر.
     */
    public function smsLogs(Request $request)
    {
        $query = SmsLog::query();

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('mobile', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhere('template', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $smsLogs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.monitoring.sms', compact('smsLogs'));
    }

    /**
     * لیست لاگ پرفورمانس با فیلتر.
     */
    public function performance(Request $request)
    {
        $query = RequestPerformanceLog::query();

        if ($request->boolean('slow_only')) {
            $query->slow();
        }

        if ($request->filled('method')) {
            $query->where('method', strtoupper($request->input('method')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('path', 'like', "%{$search}%");
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $performanceLogs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.monitoring.performance', compact('performanceLogs'));
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
