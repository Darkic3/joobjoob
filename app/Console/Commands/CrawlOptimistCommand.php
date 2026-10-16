<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\CrawlLog;
use App\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use DOMDocument;
use DOMXPath;
use Carbon\Carbon;

class CrawlOptimistCommand extends Command
{
    protected $signature = 'products:crawl
                            {--limit=10 : Maximum number of products to crawl}
                            {--dry-run : Validate only, do not save to database}
                            {--pages=1-88 : Page range to crawl (e.g. 1-5 or 1-88)}';

    protected $description = 'Crawl optimist.style socks category and import products to database';

    protected const BASE_URL = 'https://optimist.style';
    protected const LIST_PATH = '/products/a/true?cat=%D8%AC%D9%88%D8%B1%D8%A7%D8%A8&page={page}';
    protected const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /** @var int */
    private int $successCount = 0;

    /** @var int */
    private int $failCount = 0;

    /** @var array */
    private array $errors = [];

    /** @var ProductCategory|null */
    private ?ProductCategory $defaultCategory = null;

    /** @var ProductService */
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $pages = $this->parsePages($this->option('pages'));

        $this->info('🕷 Optimist Socks Crawler');
        $this->info('Limit: ' . ($limit ?: 'unlimited') . ' | Pages: ' . $pages[0] . '–' . end($pages) . ' | Mode: ' . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        // Ensure default category exists
        $this->defaultCategory = $this->getOrCreateDefaultCategory();

        $count = 0;
        $totalFound = 0;
        $startedAt = Carbon::now();

        foreach ($pages as $page) {
            if ($limit > 0 && $count >= $limit) {
                break;
            }

            $this->info("📄 Crawling listing page {$page}...");
            $productUrls = $this->extractProductUrlsFromListing($page);

            if (empty($productUrls)) {
                $this->warn("   No products found on page {$page}, stopping.");
                break;
            }

            $totalFound += count($productUrls);
            $this->info("   Found " . count($productUrls) . " product links.");

            foreach ($productUrls as $productUrl) {
                if ($limit > 0 && $count >= $limit) {
                    break;
                }

                $count++;
                $this->info("   [{$count}] {$productUrl}");

                try {
                    $productData = $this->crawlProductDetail($productUrl);

                    if ($productData === null) {
                        $this->failCount++;
                        $this->warn("      ⏭ Skipped (no data)");
                        continue;
                    }

                    // Only import in-stock products
                    if (!$productData['in_stock']) {
                        $this->warn("      ⏭ Skipped (out of stock)");
                        continue;
                    }

                    if (!$dryRun) {
                        $this->saveProduct($productData);
                    }

                    $this->successCount++;
                    $this->info("      ✓ Imported: " . $productData['name']);

                    // Rate limiting — be polite to the source site
                    sleep(1);
                } catch (\Throwable $e) {
                    $this->failCount++;
                    $this->errors[] = [
                        'url' => $productUrl,
                        'message' => $e->getMessage(),
                    ];
                    $this->error("      ✗ Error: " . $e->getMessage());
                }
            }
        }

        $finishedAt = Carbon::now();
        $duration = $startedAt->diffInSeconds($finishedAt);

        // Save crawl log to database (unless dry run)
        if (!$dryRun) {
            $status = $this->failCount > 0 && $this->successCount === 0 ? 'failed'
                : ($this->failCount > 0 ? 'partial' : 'success');

            CrawlLog::create([
                'status' => $status,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'duration_seconds' => $duration,
                'user_id' => auth()->check() ? auth()->id() : null,
                'limit' => $limit > 0 ? $limit : null,
                'pages_crawled' => count($pages),
                'products_found' => $totalFound,
                'products_imported' => $this->successCount,
                'products_failed' => $this->failCount,
                'error_message' => $this->failCount > 0 && $this->successCount === 0 ? implode("\n", array_column($this->errors, 'message')) : null,
            ]);
        }

        $this->printSummary($dryRun);

        return $this->failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Parse page range like "1-5" or "1-88" or "3" into array of ints.
     */
    private function parsePages(string $pages): array
    {
        if (str_contains($pages, '-')) {
            $parts = explode('-', $pages);
            $start = (int) trim($parts[0]);
            $end = (int) trim($parts[1]);
            return range($start, $end);
        }

        return [(int) $pages];
    }

    /**
     * Get or create the default "جوراب" category.
     */
    private function getOrCreateDefaultCategory(): ProductCategory
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
            $this->info("   Created default category: جوراب");
        }

        return $category;
    }

    /**
     * Extract individual product URLs from a category listing page.
     */
    private function extractProductUrlsFromListing(int $page): array
    {
        $url = self::BASE_URL . str_replace('{page}', (string) $page, self::LIST_PATH);

        $html = $this->fetchHtml($url);
        if ($html === null) {
            return [];
        }

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $links = [];
        // Find all <a> tags whose href contains /product/ (individual product detail pages)
        $anchors = $xpath->query("//a[contains(@href, '/product/')]");

        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            if ($href === '') {
                continue;
            }

            // Normalize to absolute URL
            $absolute = $this->normalizeUrl($href);
            if ($absolute && $this->isProductDetailUrl($absolute)) {
                $links[] = $absolute;
            }
        }

        return array_values(array_unique($links));
    }

    /**
     * Crawl a single product detail page and extract structured data.
     */
    private function crawlProductDetail(string $url): ?array
    {
        $html = $this->fetchHtml($url);
        if ($html === null) {
            return null;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Product name from <h1>
        $name = '';
        $h1Nodes = $xpath->query("//h1");
        if ($h1Nodes->length > 0) {
            $name = trim($h1Nodes->item(0)->textContent);
        }

        if ($name === '') {
            return null;
        }

        // Slug from URL path
        $path = parse_url($url, PHP_URL_PATH);
        $slug = trim($path, '/');
        // Extract just the last segment: /product/{xyz} -> xyz
        if (preg_match('#/product/(.+)$#i', $path, $m)) {
            $slug = urldecode(trim($m[1], '/'));
        }

        // Price: search for "قیمت:" pattern in text content
        $price = $this->extractPrice($xpath);

        // Stock: check if "افزودن به سبد خرید" exists (means in stock)
        $inStock = $this->checkStock($xpath);

        // Images: collect product image URLs
        $images = $this->extractImages($xpath);

        // Description — try to find, otherwise null
        $description = $this->extractDescription($xpath);

        // Color — extract from name (e.g. "سبز" -> green, "مشکی" -> black)
        $color = $this->extractColor($name);

        return [
            'name' => $name,
            'slug' => $this->makeSlug($slug),
            'price' => $price,
            'discount_price' => null,
            'stock' => $inStock ? 10 : 0, // Default stock placeholder
            'description' => $description,
            'short_description' => null,
            'sku' => null,
            'weight' => null,
            'color' => $color,
            'images' => $images,
            'source_url' => $url,
            'source_price' => $price,
            'in_stock' => $inStock,
            'is_active' => true,
            'is_featured' => false,
            'is_best_seller' => false,
            'category_id' => $this->defaultCategory->id,
            'brand_id' => null,
        ];
    }

    /**
     * Extract price from DOM text.
     */
    private function extractPrice(DOMXPath $xpath): ?int
    {
        // Get all text and search for price pattern
        $text = $xpath->query("//body")->item(0) ? $xpath->query("//body")->item(0)->textContent : '';
        $text = preg_replace('/\s+/', ' ', $text);

        // Look for "قیمت: 698,000" pattern
        if (preg_match('/قیمت\s*[:：]?\s*([\d,]{4,})/u', $text, $m)) {
            return (int) str_replace(',', '', $m[1]);
        }

        // Fallback: look for Iranian price pattern (6-7 digits with commas)
        if (preg_match('/([\d,]{6,})/', $text, $m)) {
            return (int) str_replace(',', '', $m[1]);
        }

        return null;
    }

    /**
     * Check if product is in stock by looking for "افزودن به سبد خرید" button.
     */
    private function checkStock(DOMXPath $xpath): bool
    {
        $text = $xpath->query("//body")->item(0) ? $xpath->query("//body")->item(0)->textContent : '';
        return stripos($text, 'افزودن به سبد خرید') !== false;
    }

    /**
     * Extract product image URLs.
     */
    private function extractImages(DOMXPath $xpath): array
    {
        $images = [];
        $imgs = $xpath->query("//img[@src]");

        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            if ($src === '' || $src === null) {
                continue;
            }

            // Only product images from eCommerce Image folder
            if (str_contains($src, '/eCommerce/Image/')) {
                $absolute = $this->normalizeUrl($src);
                if ($absolute && !in_array($absolute, $images)) {
                    $images[] = $absolute;
                }
            }
        }

        return $images;
    }

    /**
     * Extract description — try common containers.
     */
    private function extractDescription(DOMXPath $xpath): ?string
    {
        // Try to find description in divs with description-related classes
        $selectors = [
            "//div[contains(@class, 'description') or contains(@class, 'desc') or contains(@class, 'product-description')]",
            "//div[contains(@id, 'description') or contains(@id, 'desc')]",
            "//div[@itemprop='description']",
        ];

        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                $text = preg_replace('/\s+/', ' ', $text);
                if (strlen($text) > 20) {
                    return $text;
                }
            }
        }

        return null;
    }

    /**
     * Extract color hint from product name.
     */
    private function extractColor(string $name): ?string
    {
        $colorMap = [
            'سفید' => 'white',
            'مشکی' => 'black',
            'سبز' => 'green',
            'قرمز' => 'red',
            'آبی' => 'blue',
            'زرد' => 'yellow',
            'کرمی' => 'cream',
            'رمزی' => 'pink',
            'بنفش' => 'purple',
            'طلایی' => 'gold',
            'نقره‌ای' => 'silver',
            'قهوه‌ای' => 'brown',
            'نارنجی' => 'orange',
        ];

        foreach ($colorMap as $fa => $en) {
            if (mb_stripos($name, $fa) !== false) {
                return $en;
            }
        }

        return null;
    }

    /**
     * Make a URL-safe slug.
     */
    private function makeSlug(string $slug): string
    {
        // If already URL-friendly (has dashes, Persian/latin chars), keep it
        $slug = trim($slug, '/');
        if ($slug === '') {
            $slug = 'product-' . uniqid();
        }

        // Convert to ASCII-ish slug but keep Persian chars as-is (Laravel handles Unicode)
        $slug = Str::slug($slug, '-');

        if ($slug === '') {
            $slug = 'product-' . uniqid();
        }

        return $slug;
    }

    /**
     * Save or update product in database.
     */
    private function saveProduct(array $data): void
    {
        $slug = $data['slug'];

        // Check for existing (including soft-deleted) to restore + update instead of duplicate insert
        $existing = Product::withTrashed()->where('slug', $slug)->first();

        if ($existing) {
            $existing->restore();
            $existing->update(array_merge($data, [
                'last_crawled_at' => now(),
                'is_crawled' => true,
            ]));
            $product = $existing;
        } else {
            $product = Product::create(array_merge($data, [
                'last_crawled_at' => now(),
                'is_crawled' => true,
            ]));
        }

        // Download and set main image
        if (!empty($data['images'])) {
            $mainImage = $this->downloadImage($data['images'][0], $slug);
            if ($mainImage) {
                $product->update(['main_image' => $mainImage]);
            }
        }
    }

    /**
     * Download an image from URL and store locally using ImageHandler.
     */
    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = Http::timeout(30)->withoutVerifying()->withHeaders([
                'User-Agent' => self::USER_AGENT,
            ])->get($url);

            if (!$response->successful()) {
                return null;
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');
            $extension = match (true) {
                str_contains($contentType, 'jpeg') => 'jpg',
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'webp') => 'webp',
                default => 'jpg',
            };

            $baseUpload = config('paths.upload', 'uploads');
            $folder = public_path("{$baseUpload}/products/");
            if (! is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $fileName = md5(microtime() . $slug) . '.' . $extension;
            $filePath = $folder . $fileName;
            file_put_contents($filePath, $response->body());

            return $fileName;
        } catch (\Throwable $e) {
            $this->warn("      ⚠ Image download failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch HTML from a URL with proper headers.
     */
    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
            ])->withoutVerifying()->timeout(30)->get($url);

            if (!$response->successful()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            $this->warn("      ⚠ Fetch failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize a URL to absolute form.
     */
    private function normalizeUrl(string $url): ?string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return self::BASE_URL . $url;
        }

        return self::BASE_URL . '/' . $url;
    }

    /**
     * Check if URL looks like an individual product detail page.
     */
    private function isProductDetailUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        return preg_match('#^/product/.+$#i', $path) === 1;
    }

    /**
     * Print import summary.
     */
    private function printSummary(bool $dryRun): void
    {
        $this->newLine();
        $this->info('════════════════════════════════════════════════');
        $this->info('📊 Crawl Summary');
        $this->info('════════════════════════════════════════════════');
        $this->info("✅ Success: {$this->successCount}");
        $this->info("❌ Failed:  {$this->failCount}");
        $this->info("🔧 Mode: " . ($dryRun ? 'DRY RUN (no DB changes)' : 'LIVE'));

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Errors (first 10):');
            foreach (array_slice($this->errors, 0, 10) as $err) {
                $this->line("  • {$err['url']}: {$err['message']}");
            }
        }

        $this->newLine();
    }
}
