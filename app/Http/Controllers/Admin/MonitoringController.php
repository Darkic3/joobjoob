<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\RequestPerformanceLog;
use App\Models\SmsLog;
use App\Models\SystemError;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MonitoringController extends Controller
{
    /**
     * آیا جداول مانیتورینگ در دیتابیس موجودند؟
     *
     * ⚠️ روی محیط production ممکن است مایگریشن‌ها هنوز اجرا نشده باشند
     * (دیپلوی فقط FTP است). مانیتورینگ نباید باعث ۵۰۰ شود —
     * در نبود جداول، داشبورد با مقادیر صفر و یک بنر هشدار نمایش داده می‌شود.
     */
    private function monitoringTablesReady(): bool
    {
        try {
            return Schema::hasTable('system_errors')
                && Schema::hasTable('payment_logs')
                && Schema::hasTable('sms_logs')
                && Schema::hasTable('request_performance_logs');
        } catch (\Throwable $e) {
            Log::warning('Monitoring: table check failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * داشبورد اصلی مانیتورینگ.
     */
    public function index()
    {
        $tablesReady = $this->monitoringTablesReady();

        $errorStats = [
            'unresolved'    => 0,
            'total_24h'     => 0,
            'critical_24h'  => 0,
            'error_24h'     => 0,
            'warning_24h'   => 0,
            'recent'        => collect(),
        ];

        $payments = [
            'total_24h'     => 0,
            'success_24h'   => 0,
            'failed_24h'    => 0,
            'failure_rate'  => 0,
            'recent_failed' => collect(),
        ];

        $sms = [
            'total_24h'     => 0,
            'sent_24h'      => 0,
            'failed_24h'    => 0,
            'skipped_24h'   => 0,
            'failure_rate'  => 0,
            'recent_failed' => collect(),
        ];

        $performance = [
            'total_24h'       => 0,
            'slow_24h'        => 0,
            'avg_response_ms' => 0,
            'recent_slow'     => collect(),
        ];

        $failedJobs = [
            'total'  => 0,
            'recent' => collect(),
        ];

        if ($tablesReady) {
            $errorStats = [
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
                // ⚠️ failed_jobs در اسکیمای قدیمی ستون created_at ندارد — failed_at استاندارد است
                'recent' => Schema::hasColumn('failed_jobs', 'failed_at')
                    ? DB::table('failed_jobs')->orderByDesc('failed_at')->take(5)->get()
                    : DB::table('failed_jobs')->take(5)->get(),
            ];
        }

        $systemStatus = $this->determineSystemStatus($errorStats, $payments, $sms, $performance);

        return view('admin.monitoring.index', compact(
            'errorStats', 'payments', 'sms', 'performance', 'failedJobs', 'systemStatus', 'tablesReady'
        ));
    }

    /**
     * لیست خطاهای سیستمی با فیلتر.
     */
    public function errors(Request $request)
    {
        $tablesReady = $this->monitoringTablesReady();

        if ($tablesReady) {
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

            $errorLogs = $query->latest()->paginate(20)->withQueryString();
        } else {
            $errorLogs = new LengthAwarePaginator(collect(), 0, 20);
        }

        return view('admin.monitoring.errors', compact('errorLogs', 'tablesReady'));
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
        $tablesReady = $this->monitoringTablesReady();

        if ($tablesReady) {
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
        } else {
            $payments = new LengthAwarePaginator(collect(), 0, 20);
        }

        return view('admin.monitoring.payments', compact('payments', 'tablesReady'));
    }

    /**
     * لیست لاگ SMS با فیلتر.
     */
    public function smsLogs(Request $request)
    {
        $tablesReady = $this->monitoringTablesReady();

        if ($tablesReady) {
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
        } else {
            $smsLogs = new LengthAwarePaginator(collect(), 0, 20);
        }

        return view('admin.monitoring.sms', compact('smsLogs', 'tablesReady'));
    }

    /**
     * لیست لاگ پرفورمانس با فیلتر.
     */
    public function performance(Request $request)
    {
        $tablesReady = $this->monitoringTablesReady();

        if ($tablesReady) {
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
        } else {
            $performanceLogs = new LengthAwarePaginator(collect(), 0, 20);
        }

        return view('admin.monitoring.performance', compact('performanceLogs', 'tablesReady'));
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
