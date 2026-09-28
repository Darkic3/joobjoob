<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\LocaleService;
use App\Services\SettingService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * فاز ۳ — دادهٔ ساختاریافته (JSON-LD) و مسیر راهنما.
 *
 * این تست‌ها قراردادهای زیر را قفل می‌کنند:
 *   - هر صفحه دقیقاً یک بلوک JSON-LD دارد و خروجی JSON معتبر است
 *   - هیچ مقداری با string interpolation داخل JSON نوشته نمی‌شود (امنیت/اعتبار)
 *   - Organization/WebSite یک بار اعلام و با @id ارجاع می‌شوند
 *   - BreadcrumbList دقیقاً همان چیزی است که کاربر می‌بیند
 *   - aggregateRating فقط با دادهٔ واقعی ساخته می‌شود
 */
class SeoSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Test multi-lingual behavior: disable public locale lock
        config(['site-features.public_locale_locked' => false]);

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    // ==========================================
    // موجودیت‌های سراسری
    // ==========================================

    public function test_every_page_declares_organization_once(): void
    {
        $graph = $this->graph($this->get('/')->assertOk()->getContent());

        $this->assertSame(1, $this->countType($graph, 'Organization'));
        $this->assertSame(1, $this->countType($graph, 'WebSite'));

        $organization = $this->node($graph, 'Organization');
        $this->assertSame('http://localhost#organization', $organization['@id']);
        $this->assertSame('فروشگاه شاپورا', $organization['name']);
    }

    public function test_website_schema_declares_the_internal_search_action(): void
    {
        $website = $this->node($this->graph($this->get('/')->assertOk()->getContent()), 'WebSite');

        $this->assertNotNull($website);
        $this->assertSame('fa', $website['inLanguage']);
        $this->assertSame(['@id' => 'http://localhost#organization'], $website['publisher']);

        $target = $website['potentialAction']['target'];
        $this->assertSame('EntryPoint', $target['@type']);
        $this->assertStringContainsString('/products?q={search_term_string}', $target['urlTemplate']);
        $this->assertSame('required name=search_term_string', $website['potentialAction']['query-input']);
    }

    public function test_website_schema_is_only_declared_on_the_home_page(): void
    {
        $product = $this->product();
        $graph = $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent());

        $this->assertSame(0, $this->countType($graph, 'WebSite'));
    }

    // ==========================================
    // صفحهٔ محصول
    // ==========================================

    public function test_product_page_declares_a_complete_product_offer(): void
    {
        $product = $this->product(['stock' => 4, 'sku' => 'SKU-9001']);

        $graph = $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent());
        $schema = $this->node($graph, 'Product');

        $this->assertNotNull($schema);
        $this->assertSame($product->trans('name'), $schema['name']);
        $this->assertSame('http://localhost/products/'.$product->slug, $schema['url']);
        $this->assertSame('http://localhost/products/'.$product->slug.'#product', $schema['@id']);
        $this->assertSame('SKU-9001', $schema['sku']);
        $this->assertSame('IRR', $schema['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);
        $this->assertSame('https://schema.org/NewCondition', $schema['offers']['itemCondition']);
    }

    public function test_product_price_is_converted_to_the_declared_currency(): void
    {
        $product = $this->product(['price' => 1000]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        // قیمت‌های دیتابیس تومانی‌اند؛ IRR = تومان × ۱۰
        $this->assertSame('10000', $schema['offers']['price']);
    }

    public function test_changing_the_schema_currency_changes_the_price_multiplier(): void
    {
        $this->setSetting('seo_schema_currency', 'IRT', 'select');

        $product = $this->product(['price' => 1000]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame('IRT', $schema['offers']['priceCurrency']);
        $this->assertSame('1000', $schema['offers']['price']);
    }

    public function test_out_of_stock_product_is_marked_out_of_stock(): void
    {
        $product = $this->product(['stock' => 0]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame('https://schema.org/OutOfStock', $schema['offers']['availability']);
    }

    public function test_product_schema_includes_image_brand_and_category(): void
    {
        $brand = Brand::create([
            'title' => ['fa' => 'برند سونی'],
            'url' => 'sony-'.uniqid(),
            'active' => true,
        ]);
        $product = $this->product(['brand_id' => $brand->id]);
        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'schema-image.jpg',
            'is_primary' => true,
        ]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame(['@type' => 'Brand', 'name' => 'برند سونی'], $schema['brand']);
        $this->assertSame('دستهٔ تستی', $schema['category']);
        $this->assertMatchesRegularExpression('~^http://localhost/[^\s"]*schema-image\.jpg$~', $schema['image'][0]);
    }

    public function test_product_schema_reuses_the_meta_description(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['description' => 'توضیح متای دستی محصول']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();
        $schema = $this->node($this->graph($html), 'Product');

        $this->assertSame('توضیح متای دستی محصول', $schema['description']);
        $this->assertStringContainsString('<meta name="description" content="توضیح متای دستی محصول">', $html);
    }

    // ==========================================
    // امنیت / اعتبار JSON
    // ==========================================

    public function test_user_data_cannot_break_out_of_the_json_ld_block(): void
    {
        $payload = 'محصول "ویژه" <script>alert(1)</script>';
        $product = $this->product(['name' => ['fa' => $payload]]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        // اسکریپت تزریق‌شده هرگز به HTML خام نمی‌رسد
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);

        // ولی داده سالم و کامل در JSON-LD حاضر است
        $this->assertSame($payload, $this->node($this->graph($html), 'Product')['name']);
    }

    public function test_page_renders_exactly_one_json_ld_block(): void
    {
        $product = $this->product();

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<script type="application/ld+json">'));
        $this->assertSame(1, $this->countType($this->graph($html), 'Product'));
    }

    // ==========================================
    // امتیاز و نظرات
    // ==========================================

    public function test_aggregate_rating_is_omitted_without_reviews(): void
    {
        $product = $this->product();

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertArrayNotHasKey('aggregateRating', $schema);
        $this->assertArrayNotHasKey('review', $schema);
    }

    public function test_aggregate_rating_is_built_from_real_approved_reviews(): void
    {
        $product = $this->product();
        $user = User::create([
            'name' => 'خریدار تستی',
            'mobile' => '09120000000',
            'password' => bcrypt('secret'),
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'کیفیت خیلی خوب بود',
            'is_approved' => true,
        ]);

        // نظر تأییدنشده نباید در امتیاز شمرده شود
        $other = User::create([
            'name' => 'کاربر دیگر',
            'mobile' => '09120000001',
            'password' => bcrypt('secret'),
        ]);
        Review::create([
            'product_id' => $product->id,
            'user_id' => $other->id,
            'rating' => 1,
            'comment' => 'در انتظار تأیید',
            'is_approved' => false,
        ]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame('4', $schema['aggregateRating']['ratingValue']);
        $this->assertSame('1', $schema['aggregateRating']['reviewCount']);
        $this->assertCount(1, $schema['review']);
        $this->assertSame('خریدار تستی', $schema['review'][0]['author']['name']);
        $this->assertSame('کیفیت خیلی خوب بود', $schema['review'][0]['reviewBody']);
    }

    public function test_price_valid_until_is_added_for_a_running_deal_timer(): void
    {
        $product = $this->product([
            'timer_active' => true,
            'end_timer' => now()->addDays(2),
        ]);

        $schema = $this->node(
            $this->graph($this->get('/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame(now()->addDays(2)->toDateString(), $schema['offers']['priceValidUntil']);
    }

    // ==========================================
    // مسیر راهنما (breadcrumb)
    // ==========================================

    public function test_breadcrumb_list_matches_the_visible_breadcrumbs(): void
    {
        $category = $this->category('لوازم جانبی');
        $product = $this->product(['product_category_id' => $category->id]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();
        $breadcrumb = $this->node($this->graph($html), 'BreadcrumbList');

        $this->assertNotNull($breadcrumb);
        $this->assertSame(
            ['صفحه اصلی', 'محصولات', 'لوازم جانبی', $product->trans('name')],
            array_column($breadcrumb['itemListElement'], 'name')
        );
        $this->assertSame([1, 2, 3, 4], array_column($breadcrumb['itemListElement'], 'position'));

        // آخرین آیتم صفحهٔ جاری است ⇒ بدون URL
        $this->assertArrayNotHasKey('item', $breadcrumb['itemListElement'][3]);
        $this->assertSame('http://localhost', $breadcrumb['itemListElement'][0]['item']);
        $this->assertStringContainsString(
            'http://localhost/product-categories/'.$category->slug,
            $breadcrumb['itemListElement'][2]['item']
        );

        // نسخهٔ بصری همان ترتیب را دارد
        $visible = $this->visibleBreadcrumb($html);
        $this->assertNotSame([], $visible);

        $expected = array_column($breadcrumb['itemListElement'], 'name');
        $lastPosition = -1;
        foreach ($expected as $label) {
            $position = mb_strpos($visible, $label);
            $this->assertNotFalse($position, "برچسب «{$label}» در مسیر راهنمای بصری نیست.");
            $this->assertGreaterThan($lastPosition, $position, "ترتیب «{$label}» با اسکیمای BreadcrumbList یکی نیست.");
            $lastPosition = $position;
        }
    }

    public function test_home_page_has_no_breadcrumb(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(0, $this->countType($this->graph($html), 'BreadcrumbList'));
        $this->assertStringNotContainsString('site-breadcrumb"', $html);
    }

    public function test_nested_category_trail_includes_every_ancestor(): void
    {
        $parent = $this->category('الکترونیک');
        $child = $this->category('هدفون', $parent->id);

        $html = $this->get('/product-categories/'.$child->slug)->assertOk()->getContent();
        $breadcrumb = $this->node($this->graph($html), 'BreadcrumbList');

        $this->assertSame(
            ['صفحه اصلی', 'محصولات', 'الکترونیک', 'هدفون'],
            array_column($breadcrumb['itemListElement'], 'name')
        );
    }

    // ==========================================
    // صفحهٔ مقاله
    // ==========================================

    public function test_blog_post_declares_blog_posting(): void
    {
        $author = User::create([
            'name' => 'نویسندهٔ تستی',
            'mobile' => '09120000002',
            'password' => bcrypt('secret'),
        ]);

        $blog = Blog::create([
            'user_id' => $author->id,
            'title' => 'راهنمای خرید هدفون',
            'slug' => 'schema-blog-'.uniqid(),
            'content' => '<p>یک دو سه چهار پنج</p>',
            'excerpt' => 'خلاصهٔ مقاله',
            'is_active' => true,
        ]);

        $schema = $this->node(
            $this->graph($this->get('/blogs/'.$blog->slug)->assertOk()->getContent()),
            'BlogPosting'
        );

        $this->assertNotNull($schema);
        $this->assertSame('راهنمای خرید هدفون', $schema['headline']);
        $this->assertSame('http://localhost/blogs/'.$blog->slug, $schema['url']);
        $this->assertSame('نویسندهٔ تستی', $schema['author']['name']);
        $this->assertSame(['@id' => 'http://localhost#organization'], $schema['publisher']);
        $this->assertSame('خلاصهٔ مقاله', $schema['description']);
        $this->assertArrayHasKey('datePublished', $schema);
        // شمارش واژهٔ درست روی متن فارسی (str_word_count اینجا غلط می‌دهد)
        $this->assertSame(5, $schema['wordCount']);
    }

    // ==========================================
    // صفحه‌های فهرست
    // ==========================================

    public function test_products_index_declares_collection_page_and_item_list(): void
    {
        $first = $this->product(['name' => ['fa' => 'محصول الف']]);
        $second = $this->product(['name' => ['fa' => 'محصول ب']]);

        $graph = $this->graph($this->get('/products')->assertOk()->getContent());

        $collection = $this->node($graph, 'CollectionPage');
        $this->assertNotNull($collection);
        $this->assertSame('محصولات', $collection['name']);
        $this->assertSame('http://localhost/products', $collection['@id'] === 'http://localhost/products#collection'
            ? 'http://localhost/products'
            : $collection['url']);

        $list = $this->node($graph, 'ItemList');
        $this->assertNotNull($list);

        $urls = array_column($list['itemListElement'], 'url');
        $this->assertContains('http://localhost/products/'.$first->slug, $urls);
        $this->assertContains('http://localhost/products/'.$second->slug, $urls);
        $this->assertSame(range(1, count($urls)), array_column($list['itemListElement'], 'position'));
    }

    public function test_item_list_has_no_entries_on_an_empty_index(): void
    {
        $graph = $this->graph($this->get('/products')->assertOk()->getContent());

        $this->assertNull($this->node($graph, 'ItemList'));
        $this->assertNotNull($this->node($graph, 'CollectionPage'));
    }

    public function test_item_list_positions_account_for_pagination(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->product(['name' => ['fa' => 'محصول شمارهٔ '.$i]]);
        }

        $graph = $this->graph($this->get('/products?page=2')->assertOk()->getContent());
        $list = $this->node($graph, 'ItemList');

        $this->assertNotNull($list);
        // صفحهٔ دوم از موقعیت ۱۳ شروع می‌شود، نه از ۱
        $this->assertSame([13], array_values(array_unique(array_column($list['itemListElement'], 'position'))));
    }

    public function test_blog_index_declares_collection_page_and_item_list(): void
    {
        $blog = Blog::create([
            'title' => 'مقالهٔ فهرست',
            'slug' => 'schema-list-'.uniqid(),
            'content' => '<p>محتوا</p>',
            'is_active' => true,
        ]);

        $graph = $this->graph($this->get('/blogs')->assertOk()->getContent());

        $this->assertSame('مقالات', $this->node($graph, 'CollectionPage')['name']);
        $this->assertSame(
            ['http://localhost/blogs/'.$blog->slug],
            array_column($this->node($graph, 'ItemList')['itemListElement'], 'url')
        );
    }

    // ==========================================
    // صفحه‌های استاتیک
    // ==========================================

    public function test_faq_page_declares_faq_page_with_real_questions(): void
    {
        $schema = $this->node(
            $this->graph($this->get('/page/faq')->assertOk()->getContent()),
            'FAQPage'
        );

        $this->assertNotNull($schema);
        $this->assertNotEmpty($schema['mainEntity']);

        foreach ($schema['mainEntity'] as $question) {
            $this->assertSame('Question', $question['@type']);
            $this->assertNotSame('', $question['name']);
            $this->assertSame('Answer', $question['acceptedAnswer']['@type']);
            $this->assertNotSame('', $question['acceptedAnswer']['text']);
        }
    }

    public function test_contact_page_declares_contact_page_and_contact_point(): void
    {
        $this->setSetting('phone', '02112345678', 'text');

        $graph = $this->graph($this->get('/page/contact')->assertOk()->getContent());
        $schema = $this->node($graph, 'ContactPage');

        $this->assertNotNull($schema);
        $this->assertSame('02112345678', $schema['contactPoint'][0]['telephone']);
        $this->assertSame('customer support', $schema['contactPoint'][0]['contactType']);
    }

    public function test_about_page_declares_about_page(): void
    {
        $graph = $this->graph($this->get('/page/about')->assertOk()->getContent());

        $this->assertNotNull($this->node($graph, 'AboutPage'));
    }

    public function test_plain_static_page_declares_web_page(): void
    {
        $graph = $this->graph($this->get('/page/terms')->assertOk()->getContent());

        $this->assertNotNull($this->node($graph, 'WebPage'));
        $this->assertNull($this->node($graph, 'FAQPage'));
    }

    public function test_schema_is_rendered_per_locale(): void
    {
        $product = $this->product(['name' => ['fa' => 'نام فارسی', 'en' => 'English name']]);

        $en = $this->node(
            $this->graph($this->get('/en/products/'.$product->slug)->assertOk()->getContent()),
            'Product'
        );

        $this->assertSame('English name', $en['name']);
        $this->assertStringContainsString('/en/products/', $en['@id']);
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /**
     * همهٔ موجودیت‌های JSON-LD صفحه را برمی‌گرداند و معتبر بودن JSON را تضمین می‌کند.
     *
     * @return array<int, array<string, mixed>>
     */
    private function graph(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches);

        $this->assertNotEmpty($matches[1], 'هیچ بلوک JSON-LD در صفحه پیدا نشد.');

        $nodes = [];

        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            $this->assertIsArray($decoded, 'JSON-LD صفحه JSON معتبر نیست: '.json_last_error_msg());

            foreach ($decoded as $node) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /** @param array<int, array<string, mixed>> $graph */
    private function node(array $graph, string $type): ?array
    {
        foreach ($graph as $node) {
            if (($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        return null;
    }

    /** @param array<int, array<string, mixed>> $graph */
    private function countType(array $graph, string $type): int
    {
        return count(array_filter($graph, fn (array $node) => ($node['@type'] ?? null) === $type));
    }

    /** HTML خام نوار مسیر راهنمای بصری */
    private function visibleBreadcrumb(string $html): string
    {
        preg_match('~<nav class="site-breadcrumb".*?</nav>~s', $html, $matches);

        return $matches[0] ?? '';
    }

    private function setSetting(string $key, string $value, string $type): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => 'seo', 'label' => $key]
        );

        app(SettingService::class)->flush();
    }

    private function category(string $name, ?int $parentId = null): ProductCategory
    {
        return ProductCategory::create([
            'name' => ['fa' => $name, 'en' => $name],
            'slug' => 'schema-cat-'.uniqid(),
            'parent_id' => $parentId,
            'is_active' => true,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function product(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دستهٔ تستی', 'en' => 'Test category'],
            'slug' => 'schema-product-cat-'.uniqid(),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test product'],
            'slug' => 'schema-product-'.uniqid(),
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ], $overrides));
    }
}
