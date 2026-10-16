<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrawlLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrawlManagerController extends Controller
{
    /**
     * داشبورد مدیریت کرالر — آمار و تاریخچه.
     */
    public function index(Request $request)
    {
        $stats = [
            'total_crawled' => \App\Models\Product::where('is_crawled', true)->count(),
            'total_in_db' => \App\Models\Product::count(),
            'last_crawl' => CrawlLog::latest()->first(),
            'avg_price' => \App\Models\Product::whereNotNull('price')->avg('price') ?? 0,
            'out_of_stock' => \App\Models\Product::where('stock', 0)->count(),
            'in_stock' => \App\Models\Product::where('stock', '>', 0)->count(),
        ];

        $recentCrawls = CrawlLog::with('user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.crawl.index', compact(
            'stats', 'recentCrawls'
        ));
    }

    /**
     * اجرای کرال از طریق پنل ادمین (دستی).
     *
     * کرالر در یک پروسس پس‌زمینه اجرا می‌شود تا ریکوئست HTTP تایم‌اوت نخورد؛
     * پاسخ بلافاصله برمی‌گردد و پیشرفت از طریق متد status پول می‌شود.
     */
    public function run(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:1000',
            'pages' => ['nullable', 'string', 'max:50', 'regex:/^\d{1,3}(-\d{1,3})?$/'],
        ]);

        $limit = $validated['limit'] ?? null;
        $pages = $validated['pages'] ?? '1-88';
        $isAjax = $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            // Prevent parallel runs — cache key doubles as a lock.
            if (Cache::has('crawl_live_status')) {
                $message = 'یک کرال در حال اجراست. صبر کنید تمام شود.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $message], 409)
                    : back()->with('error', $message);
            }

            $log = CrawlLog::create([
                'status' => 'running',
                'started_at' => now(),
                'limit' => $limit,
                'user_id' => auth()->id(),
                'products_found' => 0,
                'products_imported' => 0,
                'products_failed' => 0,
                'pages_crawled' => 0,
            ]);

            Cache::put('crawl_live_status', [
                'success' => 0,
                'failed' => 0,
                'current' => null,
                'current_name' => null,
                'total_pages' => 0,
                'current_page' => 0,
            ], 3600);

            $this->spawnCrawlProcess($log->id, $limit, $pages);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'کرالر در پس‌زمینه شروع شد.',
                    'log_id' => $log->id,
                ]);
            }

            return redirect()
                ->route('admin.crawl.index')
                ->with('success', 'کرالر در پس‌زمینه شروع شد. پیشرفت را همین‌جا ببینید.');
        } catch (\Throwable $e) {
            Log::error('CrawlManager: run failed', ['error' => $e->getMessage()]);
            Cache::forget('crawl_live_status');

            return $isAjax
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 500)
                : back()->with('error', 'خطا در اجرای کرالر: ' . $e->getMessage());
        }
    }

    /**
     * اجرای artisan در پروسس جدا (detached) تا ریکوئست وب بلاک نشود.
     */
    private function spawnCrawlProcess(int $logId, ?int $limit, string $pages): void
    {
        $parts = [
            PHP_BINARY,
            base_path('artisan'),
            'products:crawl',
            '--log-id=' . $logId,
            '--pages=' . $pages,
        ];

        if ($limit) {
            $parts[] = '--limit=' . $limit;
        }

        $command = implode(' ', array_map('escapeshellarg', $parts));

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B "" ' . $command . ' > NUL 2>&1', 'r'));
        } else {
            exec('nohup ' . $command . ' > /dev/null 2>&1 &');
        }
    }

    /**
     * دریافت وضعیت زنده کرالر (برای پنل ادمین — polling بدون رفرش).
     */
    public function status(Request $request)
    {
        $live = Cache::get('crawl_live_status', [
            'success' => 0,
            'failed' => 0,
            'current' => null,
            'current_name' => null,
            'total_pages' => 0,
            'current_page' => 0,
        ]);

        $log = null;
        if ($request->filled('log_id')) {
            $log = CrawlLog::find($request->integer('log_id'));
        }

        return response()->json([
            'success' => $live['success'] ?? 0,
            'failed' => $live['failed'] ?? 0,
            'current' => $live['current'] ?? null,
            'current_name' => $live['current_name'] ?? null,
            'total_pages' => $live['total_pages'] ?? 0,
            'current_page' => $live['current_page'] ?? 0,
            'is_running' => Cache::has('crawl_live_status'),
            'log' => $log ? [
                'id' => $log->id,
                'status' => $log->status,
                'products_found' => $log->products_found,
                'products_imported' => $log->products_imported,
                'products_failed' => $log->products_failed,
                'pages_crawled' => $log->pages_crawled,
                'error_message' => $log->error_message,
            ] : null,
        ]);
    }

    /**
     * نمایش جزئیات یک لاگ کرال.
     */
    public function show(CrawlLog $crawlLog)
    {
        $crawlLog->load('user');

        return view('admin.crawl.show', compact('crawlLog'));
    }

    /**
     * حذف تاریخچه کرال‌های قدیمی.
     */
    public function destroy(CrawlLog $crawlLog)
    {
        $crawlLog->delete();

        return redirect()->route('admin.crawl.index')->with('success', 'لاگ کرالر حذف شد.');
    }
}
