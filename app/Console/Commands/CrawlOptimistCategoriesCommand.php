<?php

namespace App\Console\Commands;

use App\Models\CrawlLog;
use App\Models\ProductCategory;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CrawlOptimistCategoriesCommand extends Command
{
    protected $signature = 'products:crawl-categories
                            {--dry-run : Validate only, do not save to database}
                            {--log-id= : Update an existing CrawlLog row instead of creating a new one}
                            {--root=جوراب : Root category slug on optimist.style to crawl as tree}';

    protected $description = 'Crawl optimist.style category tree (socks subcategories) and import as product categories';

    protected const BASE_URL = 'https://optimist.style';
    protected const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private int $successCount = 0;
    private int $failCount = 0;
    private array $errors = [];

    public function handle(): int
    {
        set_time_limit(0);

        $dryRun = (bool) $this->option('dry-run');
        $logId = $this->option('log-id') ? (int) $this->option('log-id') : null;
        $rootSlug = trim((string) $this->option('root')) ?: 'جوراب';

        $log = $logId ? CrawlLog::find($logId) : null;
        $startedAt = Carbon::now();

        $this->info('🌳 Optimist Category Crawler');
        $this->info('Root: /products/' . $rootSlug . ' | Mode: ' . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        $url = self::BASE_URL . '/products/' . $rootSlug;
        $html = $this->fetchHtml($url);

        if ($html === null) {
            $this->error('   ✗ Could not fetch root category page.');
            $this->finishLog($log, $dryRun, $startedAt, 0, 0, [['url' => $url, 'message' => 'Root page fetch failed']]);

            return self::FAILURE;
        }

        $tree = $this->parseMenuTree($html, $rootSlug);
        if (empty($tree['children']) && $tree['title'] === '') {
            $this->error('   ✗ No category tree found on page.');
            $this->finishLog($log, $dryRun, $startedAt, 0, 0, [['url' => $url, 'message' => 'No category tree parsed']]);

            return self::FAILURE;
        }

        $total = $this->countNodes($tree['children']);
        $this->info("   Root: {$tree['title']} — {$total} subcategories found.");
        $this->pushLiveStatus(1, 1, $url, $tree['title']);

        $rootLocal = $dryRun ? null : $this->getOrCreateRoot($tree['title'] ?: 'جوراب');

        $seen = [];
        $this->importChildren($tree['children'], $rootLocal, $dryRun, $url, $seen);

        $finishedAt = Carbon::now();
        $duration = $startedAt->diffInSeconds($finishedAt);

        if (!$dryRun) {
            $status = $this->failCount > 0 && $this->successCount === 0 ? 'failed'
                : ($this->failCount > 0 ? 'partial' : 'success');

            $logData = [
                'crawler' => 'categories',
                'status' => $status,
                'finished_at' => $finishedAt,
                'duration_seconds' => $duration,
                'pages_crawled' => 1,
                'products_found' => $total,
                'products_imported' => $this->successCount,
                'products_failed' => $this->failCount,
                'error_message' => !empty($this->errors) ? implode("\n", array_slice(array_column($this->errors, 'message'), 0, 50)) : null,
            ];

            if ($log) {
                $log->update($logData);
            } else {
                CrawlLog::create(array_merge($logData, [
                    'started_at' => $startedAt,
                    'limit' => null,
                    'user_id' => auth()->check() ? auth()->id() : null,
                ]));
            }

            Cache::forget('crawl_live_status');
        }

        $this->newLine();
        $this->info('════════════════════════════════════════════════');
        $this->info('📊 Category Crawl Summary');
        $this->info('════════════════════════════════════════════════');
        $this->info("✅ Imported: {$this->successCount}");
        $this->info("❌ Failed:  {$this->failCount}");

        return $this->failCount > 0 && $this->successCount === 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Parse the megamenu on a /products/{slug} page into a tree rooted at that slug.
     *
     * The megamenu is site-wide (it lists دورس، تیشرت، کلاه… too), so we scope
     * strictly to the root's subtree: top-level <li class="main-items" data-id="N">
     * gives the root id, <li data-parentid="N"> items form level 1, and items in
     * a nested <ul class="dropdown-menu"> inside a level-1 dropdown <li> form
     * level 2. This keeps the tree to جوراب + its real subcategories only.
     *
     * @return array{title: string, children: array}
     */
    private function parseMenuTree(string $html, string $rootSlug): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Root id + title: the main-items entry whose link matches /products/{rootSlug}.
        $rootId = null;
        $rootTitle = '';
        foreach ($xpath->query("//li[contains(@class,'main-items')][@data-id]") as $li) {
            /** @var \DOMElement $li */
            $a = $xpath->query(".//a[@href]", $li);
            if ($a->length === 0) {
                continue;
            }
            /** @var \DOMElement $anchor */
            $anchor = $a->item(0);
            if ($this->hrefSlug($anchor->getAttribute('href')) === $rootSlug) {
                $rootId = $li->getAttribute('data-id');
                $rootTitle = trim($anchor->getAttribute('title') ?: $anchor->textContent);
                break;
            }
        }

        if ($rootId === null || $rootId === '') {
            return ['title' => $rootTitle, 'children' => []];
        }

        $seen = [];
        $children = [];
        foreach ($xpath->query("//li[@data-parentid='{$rootId}']") as $li) {
            /** @var \DOMElement $li */
            $node = $this->nodeFromLi($xpath, $li);
            if ($node === null || isset($seen[$node['href']])) {
                continue;
            }
            $seen[$node['href']] = true;

            // Level 2: items nested inside this <li>'s own dropdown <ul>.
            foreach ($xpath->query("./ul[contains(@class,'dropdown-menu')]//li[@data-parentid]/a[@href]", $li) as $sub) {
                /** @var \DOMElement $sub */
                $title = trim($sub->getAttribute('title') ?: $sub->textContent);
                $href = trim($sub->getAttribute('href'));
                if ($title === '' || $href === '' || isset($seen[$href])) {
                    continue;
                }
                $seen[$href] = true;
                $node['children'][] = ['title' => $title, 'href' => $href, 'children' => []];
            }

            $children[] = $node;
        }

        return ['title' => $rootTitle, 'children' => $children];
    }

    /**
     * Build a tree node from an <li data-parentid> element's own direct link.
     */
    private function nodeFromLi(DOMXPath $xpath, \DOMElement $li): ?array
    {
        $a = $xpath->query("./a[@href]", $li);
        if ($a->length === 0) {
            return null;
        }
        /** @var \DOMElement $anchor */
        $anchor = $a->item(0);
        $title = trim($anchor->getAttribute('title') ?: $anchor->textContent);
        $href = trim($anchor->getAttribute('href'));
        if ($title === '' || $href === '') {
            return null;
        }

        return ['title' => $title, 'href' => $href, 'children' => []];
    }

    private function hrefSlug(string $href): string
    {
        $path = parse_url($href, PHP_URL_PATH) ?: $href;

        return urldecode(trim(basename(rtrim($path, '/'))));
    }

    private function countNodes(array $children): int
    {
        $n = 0;
        foreach ($children as $c) {
            $n += 1 + $this->countNodes($c['children'] ?? []);
        }

        return $n;
    }

    private function getOrCreateRoot(string $title): ProductCategory
    {
        $category = ProductCategory::where('slug', 'jorab')->first();

        if (!$category) {
            $category = ProductCategory::create([
                'name' => ['fa' => 'جوراب', 'en' => 'Socks'],
                'slug' => 'jorab',
                'description' => ['fa' => 'دسته‌بندی جوراب', 'en' => 'Socks category'],
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $this->info('   Created root category: جوراب');
        }

        return $category;
    }

    /**
     * @param array $children tree nodes
     * @param array $seen hrefs already processed (cycle guard)
     */
    private function importChildren(array $children, ?ProductCategory $parent, bool $dryRun, string $pageUrl, array &$seen, int $depth = 0): void
    {
        foreach ($children as $index => $node) {
            $title = trim($node['title']);
            $href = $node['href'];
            if ($title === '' || isset($seen[$href])) {
                continue;
            }
            $seen[$href] = true;

            $slug = $this->uniqueSlug($this->makeSlug($this->hrefSlug($href) ?: $title));

            try {
                if (!$dryRun && $parent) {
                    $category = ProductCategory::withTrashed()->where('slug', $slug)->first();
                    if ($category) {
                        if ($category->trashed()) {
                            $category->restore();
                        }
                        $category->update([
                            'parent_id' => $parent->id,
                            'name' => ['fa' => $title, 'en' => $title],
                            'is_active' => true,
                            'sort_order' => $index,
                        ]);
                    } else {
                        $category = ProductCategory::create([
                            'parent_id' => $parent->id,
                            'name' => ['fa' => $title, 'en' => $title],
                            'slug' => $slug,
                            'is_active' => true,
                            'sort_order' => $index,
                        ]);
                    }
                }

                $this->successCount++;
                $this->info(str_repeat('  ', $depth) . "✓ {$title}");
                $this->pushLiveStatus(1, 1, $href, $title);

                if (!empty($node['children'])) {
                    $childParent = $dryRun || !$parent ? null : ProductCategory::where('slug', $slug)->first();
                    $this->importChildren($node['children'], $childParent ?? $parent, $dryRun, $pageUrl, $seen, $depth + 1);
                }
            } catch (\Throwable $e) {
                $this->failCount++;
                $this->errors[] = ['url' => $href, 'message' => $e->getMessage()];
                $this->error("      ✗ {$title}: " . $e->getMessage());
                $this->pushLiveStatus(1, 1, $href, null);
            }
        }
    }

    private function makeSlug(string $slug): string
    {
        $slug = trim($slug, '/');
        if ($slug === '') {
            $slug = 'category-' . uniqid();
        }
        $slug = Str::slug($slug, '-');
        if ($slug === '') {
            $slug = 'category-' . uniqid();
        }

        return $slug;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (ProductCategory::withTrashed()->where('slug', $slug)->exists()) {
            // If the existing row has the same meaning (same slug base), reuse check
            // happens in importChildren via slug lookup — here just ensure novelty
            // for genuinely different titles colliding after transliteration.
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 50) {
                $slug = $base . '-' . uniqid();
                break;
            }
        }

        return $slug;
    }

    private function pushLiveStatus(int $currentPage, int $totalPages, ?string $url, ?string $name): void
    {
        try {
            Cache::put('crawl_live_status', [
                'crawler' => 'categories',
                'success' => $this->successCount,
                'failed' => $this->failCount,
                'current' => $url,
                'current_name' => $name,
                'total_pages' => $totalPages,
                'current_page' => $currentPage,
            ], 3600);
        } catch (\Throwable) {
        }
    }

    private function fetchHtml(string $url, int $retries = 1): ?string
    {
        $lastError = null;

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
                ])->withoutVerifying()->timeout(20)->get($url);

                if ($response->successful()) {
                    return $response->body();
                }

                $lastError = "HTTP {$response->status()}";
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }

            if ($i < $retries) {
                sleep(2);
            }
        }

        $this->warn("      ⚠ Fetch failed after " . ($retries + 1) . " attempts: $lastError");

        return null;
    }

    private function finishLog(?CrawlLog $log, bool $dryRun, Carbon $startedAt, int $found, int $pages, array $errors): void
    {
        if ($dryRun) {
            return;
        }

        $finishedAt = Carbon::now();
        $data = [
            'crawler' => 'categories',
            'status' => 'failed',
            'finished_at' => $finishedAt,
            'duration_seconds' => $startedAt->diffInSeconds($finishedAt),
            'pages_crawled' => $pages,
            'products_found' => $found,
            'products_imported' => 0,
            'products_failed' => count($errors),
            'error_message' => implode("\n", array_column($errors, 'message')),
        ];

        if ($log) {
            $log->update($data);
        } else {
            CrawlLog::create(array_merge($data, [
                'started_at' => $startedAt,
                'limit' => null,
                'user_id' => auth()->check() ? auth()->id() : null,
            ]));
        }

        Cache::forget('crawl_live_status');
    }
}
