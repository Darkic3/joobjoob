<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrawlLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
     */
    public function run(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:1000',
            'pages' => 'nullable|string|max:50',
        ]);

        $limit = $validated['limit'] ?? null;
        $pages = $validated['pages'] ?? '1-88';

        try {
            $artisanArgs = [];
            if ($limit) {
                $artisanArgs['--limit'] = (string) $limit;
            }
            $artisanArgs['--pages'] = $pages;

            $exitCode = Artisan::call('products:crawl', $artisanArgs);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return redirect()
                    ->route('admin.crawl.index')
                    ->with('success', 'کرالر با موفقیت اجرا شد.');
            }

            return redirect()
                ->route('admin.crawl.index')
                ->with('warning', 'کرالر به صورت جزئی اجرا شد.');

        } catch (\Throwable $e) {
            Log::error('CrawlManager: run failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'خطا در اجرای کرالر: ' . $e->getMessage());
        }
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
