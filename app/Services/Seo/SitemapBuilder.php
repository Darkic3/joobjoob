<?php

namespace App\Services\Seo;

use App\Http\Controllers\Site\PageController;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * ساخت ورودی‌های sitemap — منبع واحد برای صفحهٔ ایندکس و همهٔ فایل‌های فرزند.
 *
 * ⚠️ دو تصمیم مهم:
 *
 * ۱) URLها **نسبی** کش می‌شوند و فقط در زمان رندر با `url()` مطلق می‌شوند.
 *    اگر URL مطلق را کش می‌کردیم، تغییر دامنه/پروتکل تا انقضای کش در sitemap
 *    می‌ماند — دقیقاً همان مشکلی که فایل استاتیک robots.txt داشت.
 *
 * ۲) کش «کلی هر نوع» است، نه هر صفحه: `Cache::remember($key, ...)` با کلید نام‌دار
 *    و پاک‌سازی صریح با `forget()`. درایور کش این پروژه database است و از
 *    tag پشتیبانی نمی‌کند؛ پس کلید نام‌دار + forget همان الگوی PromoBanner/TrustBadge است.
 *
 * ایندکس‌پذیری هر لینک از همان‌جایی می‌آید که رندر صفحه: ردیف `seo_meta` هر زبان
 * (`is_indexable`) و سیاست ترجمهٔ ناقص (`SeoResolver::hasTranslation`). پس اگر ادمین
 * صفحه‌ای را noindex کند، خودکار از sitemap هم حذف می‌شود.
 */
class SitemapBuilder
{
    /** انواع sitemap فرزند */
    public const TYPES = ['pages', 'products', 'product-categories', 'blogs', 'blog-categories'];

    private const CACHE_PREFIX = 'seo_sitemap_';

    /** فیلدهای لازم از seo_meta — فقط همان چیزی که sitemap می‌خواند */
    private const META_COLUMNS = [
        'id', 'seo_metable_type', 'seo_metable_id', 'path', 'locale',
        'is_indexable', 'sitemap_priority', 'sitemap_changefreq',
    ];

    /** @var array<string, array<int|string, array<string, SeoMeta>>> */
    private array $metaIndex = [];

    public function __construct(private readonly SeoResolver $resolver) {}

    // ==========================================
    // API عمومی
    // ==========================================

    /** @return array<int, string> */
    public static function cacheKeys(): array
    {
        return array_map(fn (string $type) => self::CACHE_PREFIX.$type, self::TYPES);
    }

    /**
     * پاک‌سازی کش sitemap — بعد از هر تغییر محتوایی صدا زده می‌شود.
     * (چند کلید ثابت است، پس forget صریح کاملاً قابل پیش‌بینی است.)
     */
    public static function forget(): void
    {
        foreach (self::cacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    /** همهٔ لینک‌های یک نوع (بدون تفکیک صفحه) */
    public function all(string $type): array
    {
        if (! in_array($type, self::TYPES, true)) {
            return [];
        }

        return Cache::remember(
            self::CACHE_PREFIX.$type,
            now()->addHours($this->cacheHours()),
            fn (): array => $this->build($type),
        );
    }

    /** لینک‌های یک صفحه از یک نوع — شمارهٔ صفحه از ۱ شروع می‌شود */
    public function entries(string $type, int $page = 1): array
    {
        $page = max(1, $page);

        return array_slice($this->all($type), ($page - 1) * $this->chunkSize(), $this->chunkSize());
    }

    public function chunkCount(string $type): int
    {
        return max(1, (int) ceil(count($this->all($type)) / $this->chunkSize()));
    }

    /**
     * فهرست فایل‌های فرزند برای sitemap index.
     *
     * @return array<int, array{loc: string, lastmod: string|null}>
     */
    public function index(): array
    {
        $children = [];

        foreach (self::TYPES as $type) {
            $entries = $this->all($type);

            if ($entries === []) {
                continue;
            }

            $lastmod = $this->latestLastmod($entries);
            $chunks = $this->chunkCount($type);

            for ($page = 1; $page <= $chunks; $page++) {
                $children[] = [
                    // طولانی‌ترین موارد اول نمی‌آید؛ ترتیب نوع‌ها ثابت است تا دیف خروجی قابل پیش‌بینی بماند
                    'loc' => $chunks === 1
                        ? route('sitemap.child', ['type' => $type])
                        : route('sitemap.chunk', ['type' => $type, 'page' => $page]),
                    'lastmod' => $lastmod,
                ];
            }
        }

        return $children;
    }

    // ==========================================
    // ساخت هر نوع
    // ==========================================

    private function build(string $type): array
    {
        return match ($type) {
            'pages' => $this->buildPages(),
            'products' => $this->buildProducts(),
            'product-categories' => $this->buildProductCategories(),
            'blogs' => $this->buildBlogs(),
            'blog-categories' => $this->buildBlogCategories(),
            default => [],
        };
    }

    /**
     * صفحه‌های بدون مدل: اصلی، فهرست‌ها و صفحات استاتیک.
     * این‌ها ردیف متای مسیرمحور دارند (`seo_meta.path`)، پس noindex ادمین رویشان اثر می‌گذارد.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildPages(): array
    {
        $pages = [
            ['route' => 'home', 'params' => [], 'path' => '/', 'policy' => 'home'],
            ['route' => 'products.index', 'params' => [], 'path' => '/products', 'policy' => 'products.index'],
            ['route' => 'blogs.index', 'params' => [], 'path' => '/blogs', 'policy' => 'blogs.index'],
        ];

        foreach (PageController::PAGES as $page) {
            $pages[] = [
                'route' => 'site.page',
                'params' => ['page' => $page],
                'path' => '/page/'.$page,
                'policy' => 'site.page',
            ];
        }

        $entries = [];

        foreach ($pages as $page) {
            $entry = $this->entry(
                $page['route'],
                $page['params'],
                null,
                $page['policy'],
                $page['path'],
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildProducts(): array
    {
        $metas = $this->modelMetaIndex(Product::class);

        $products = Product::query()
            ->where('is_active', true)
            ->select(['id', 'slug', 'name', 'updated_at'])
            ->orderBy('id')
            ->get();

        $entries = [];

        foreach ($products as $product) {
            $entry = $this->entry(
                'products.show',
                ['product' => $product->slug],
                $product,
                'products.show',
                null,
                $metas[$product->id] ?? [],
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildProductCategories(): array
    {
        $metas = $this->modelMetaIndex(ProductCategory::class);

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->select(['id', 'slug', 'name', 'updated_at'])
            ->orderBy('id')
            ->get();

        $entries = [];

        foreach ($categories as $category) {
            $entry = $this->entry(
                'products.category',
                ['category' => $category->slug],
                $category,
                'products.category',
                null,
                $metas[$category->id] ?? [],
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildBlogs(): array
    {
        $metas = $this->modelMetaIndex(Blog::class);

        $blogs = Blog::query()
            ->active()
            ->select(['id', 'slug', 'title', 'updated_at'])
            ->orderBy('id')
            ->get();

        $entries = [];

        foreach ($blogs as $blog) {
            $entry = $this->entry(
                'blogs.show',
                ['blog' => $blog->slug],
                $blog,
                'blogs.show',
                null,
                $metas[$blog->id] ?? [],
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildBlogCategories(): array
    {
        $metas = $this->modelMetaIndex(BlogCategory::class);

        $categories = BlogCategory::query()
            ->active()
            ->select(['id', 'slug', 'title', 'updated_at'])
            ->orderBy('id')
            ->get();

        $entries = [];

        foreach ($categories as $category) {
            $entry = $this->entry(
                'blogs.category',
                ['category' => $category->slug],
                $category,
                'blogs.category',
                null,
                $metas[$category->id] ?? [],
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    // ==========================================
    // ساخت یک ورودی
    // ==========================================

    /**
     * یک ورودی sitemap می‌سازد یا `null` برمی‌گرداند اگر هیچ زبانی ایندکس‌پذیر نباشد.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, SeoMeta>  $metas  ردیف‌های متای همین موجودیت به تفکیک زبان
     * @return array<string, mixed>|null
     */
    private function entry(
        string $route,
        array $params,
        ?Model $model,
        string $policyKey,
        ?string $path,
        array $metas = [],
    ): ?array {
        $active = array_keys(locale()->active());

        if ($active === []) {
            return null;
        }

        if ($path !== null) {
            $metas = $this->pathMetaIndex()[$path] ?? [];
        }

        $locales = [];

        foreach ($active as $code) {
            // زبان بدون ترجمهٔ واقعی ⇒ در sitemap هم نمی‌آید (هم‌قاعده با noindex رندر)
            if ($model !== null && ! $this->resolver->hasTranslation($model, $code)) {
                continue;
            }

            if (($metas[$code] ?? null)?->is_indexable === false) {
                continue;
            }

            $locales[] = $code;
        }

        if ($locales === []) {
            return null;
        }

        // زبان پایهٔ لینک: زبان پیش‌فرض اگر ایندکس‌پذیر بود، وگرنه اولین زبان ایندکس‌پذیر
        $baseLocale = in_array(locale()->defaultCode(), $locales, true) ? locale()->defaultCode() : $locales[0];

        $alternates = [];

        foreach ($locales as $code) {
            $alternates[$code] = $this->relativePath(localizedRoute($route, $params, $code));
        }

        $baseMeta = $metas[$baseLocale] ?? null;

        return [
            'path' => $this->relativePath(localizedRoute($route, $params, $baseLocale)),
            'lastmod' => $model?->updated_at?->toAtomString(),
            'changefreq' => $baseMeta?->sitemap_changefreq
                ?? config("seo.sitemap_changefreq.{$policyKey}"),
            'priority' => $baseMeta?->sitemap_priority
                ?? config("seo.sitemap_priority.{$policyKey}"),
            'alternates' => $alternates,
        ];
    }

    // ==========================================
    // ایندکس متا
    // ==========================================

    /**
     * ردیف‌های متای یک کلاس مدل، گروه‌بندی‌شده بر اساس شناسه و سپس زبان.
     * یک کوئری برای کل نوع — نه یکی برای هر ردیف.
     *
     * @return array<int, array<string, SeoMeta>>
     */
    private function modelMetaIndex(string $class): array
    {
        if (isset($this->metaIndex[$class])) {
            return $this->metaIndex[$class];
        }

        $index = SeoMeta::query()
            ->where('seo_metable_type', $class)
            ->get(self::META_COLUMNS)
            ->groupBy('seo_metable_id')
            ->map(fn ($rows) => $rows->keyBy('locale')->all())
            ->all();

        return $this->metaIndex[$class] = $index;
    }

    /**
     * ردیف‌های متای مسیرمحور (صفحات بدون مدل)، گروه‌بندی‌شده بر اساس مسیر و سپس زبان.
     *
     * @return array<string, array<string, SeoMeta>>
     */
    private function pathMetaIndex(): array
    {
        if (isset($this->metaIndex['_paths'])) {
            return $this->metaIndex['_paths'];
        }

        $index = SeoMeta::query()
            ->whereNotNull('path')
            ->get(self::META_COLUMNS)
            ->groupBy('path')
            ->map(fn ($rows) => $rows->keyBy('locale')->all())
            ->all();

        return $this->metaIndex['_paths'] = $index;
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /** آدرس مطلق را به مسیر نسبی تبدیل می‌کند (کش مستقل از دامنه می‌ماند) */
    private function relativePath(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return $path === '' ? '/' : $path;
    }

    /** @param array<int, array<string, mixed>> $entries */
    private function latestLastmod(array $entries): ?string
    {
        $dates = array_filter(array_column($entries, 'lastmod'));

        return $dates === [] ? null : max($dates);
    }

    private function chunkSize(): int
    {
        return max(1, (int) config('seo.sitemap_chunk_size', 5000));
    }

    private function cacheHours(): int
    {
        return max(1, (int) config('seo.sitemap_cache_hours', 6));
    }
}
