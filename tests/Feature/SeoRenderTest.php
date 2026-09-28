<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Services\LocaleService;
use App\Services\Seo\SeoResolver;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * فاز ۲ — رزولوشن و رندر متای SEO.
 *
 * این تست‌ها قراردادهای رندر را قفل می‌کنند: قالب عنوان، زنجیرهٔ اولویت،
 * canonical، سیاست noindex، و OG/Twitter.
 */
class SeoRenderTest extends TestCase
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
    // عنوان
    // ==========================================

    public function test_page_title_uses_the_configured_template(): void
    {
        $product = $this->product(['name' => ['fa' => 'هدفون سونی']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>هدفون سونی | فروشگاه شاپورا</title>', $html);
    }

    public function test_site_name_is_not_appended_twice(): void
    {
        // صفحهٔ اصلی عنوانش از قبل نام سایت را دارد ⇒ نباید پسوند تکراری بگیرد
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('فروشگاه شاپورا | فروشگاه شاپورا', $html);
        $this->assertStringContainsString('<title>', $html);
    }

    public function test_stored_meta_title_overrides_the_view_title(): void
    {
        $product = $this->product(['name' => ['fa' => 'نام محصول']]);
        $product->syncSeoMeta(['fa' => ['title' => 'عنوان متای دستی']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>عنوان متای دستی | فروشگاه شاپورا</title>', $html);
        $this->assertStringNotContainsString('<title>نام محصول', $html);
    }

    public function test_explicit_override_beats_stored_meta(): void
    {
        $product = $this->product(['name' => ['fa' => 'نام محصول']]);
        $product->syncSeoMeta(['fa' => ['title' => 'از دیتابیس']]);

        [$seo] = $this->resolveFor($product, function (): void {
            seo()->title('از کنترلر');
        });

        $this->assertSame('از کنترلر | فروشگاه شاپورا', $seo['title']);
    }

    public function test_per_locale_meta_title_is_used_for_each_language(): void
    {
        $product = $this->product(['name' => ['fa' => 'نام فارسی', 'en' => 'English name']]);
        $product->syncSeoMeta([
            'fa' => ['title' => 'عنوان فارسی'],
            'en' => ['title' => 'English title'],
        ]);

        $fa = $this->get('/products/'.$product->slug)->assertOk()->getContent();
        $en = $this->get('/en/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>عنوان فارسی | فروشگاه شاپورا</title>', $fa);
        $this->assertStringContainsString('<title>English title | فروشگاه شاپورا</title>', $en);
    }

    // ==========================================
    // توضیحات و کلمات کلیدی
    // ==========================================

    public function test_description_falls_back_to_the_model_short_description(): void
    {
        $product = $this->product(['short_description' => ['fa' => 'توضیح کوتاه محصول']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="description" content="توضیح کوتاه محصول">', $html);
    }

    public function test_stored_description_and_keywords_are_rendered(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => [
            'description' => 'توضیح ذخیره‌شده',
            'keywords' => 'هدفون, سونی, بی‌سیم',
        ]]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="description" content="توضیح ذخیره‌شده">', $html);
        $this->assertStringContainsString('<meta name="keywords" content="هدفون, سونی, بی‌سیم">', $html);
    }

    public function test_keywords_tag_is_omitted_when_empty(): void
    {
        $product = $this->product();

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('<meta name="keywords"', $html);
    }

    // ==========================================
    // canonical
    // ==========================================

    public function test_canonical_is_self_referencing_per_locale(): void
    {
        $product = $this->product();

        $fa = $this->get('/products/'.$product->slug)->assertOk()->getContent();
        $en = $this->get('/en/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString(
            '<link rel="canonical" href="http://localhost/products/'.$product->slug.'">',
            $fa
        );
        $this->assertStringContainsString(
            '<link rel="canonical" href="http://localhost/en/products/'.$product->slug.'">',
            $en
        );
    }

    public function test_filters_are_stripped_from_canonical(): void
    {
        $html = $this->get('/products?sort=price_asc&brand=sony&min_price=100')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/products">', $html);
        $this->assertStringNotContainsString('canonical" href="http://localhost/products?', $html);
    }

    public function test_pagination_keeps_self_canonical(): void
    {
        $html = $this->get('/products?page=2')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<link rel="canonical" href="http://localhost/products?page=2">',
            $html
        );
    }

    public function test_stored_canonical_override_is_used(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['canonical_url' => '/products/canonical-target']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString(
            '<link rel="canonical" href="http://localhost/products/canonical-target">',
            $html
        );
    }

    // ==========================================
    // سیاست robots
    // ==========================================

    public function test_public_pages_are_indexable_by_default(): void
    {
        $product = $this->product();

        foreach (['/', '/products', '/products/'.$product->slug, '/blogs'] as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            $this->assertStringContainsString(
                '<meta name="robots" content="index, follow">',
                $html,
                "صفحهٔ «{$uri}» باید index, follow باشد."
            );
        }
    }

    public function test_private_routes_are_noindex_nofollow(): void
    {
        foreach (['/cart', '/login', '/wishlist'] as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            $this->assertStringContainsString(
                '<meta name="robots" content="noindex, nofollow">',
                $html,
                "صفحهٔ «{$uri}» باید noindex, nofollow باشد."
            );
        }
    }

    public function test_internal_search_pages_are_noindex_but_followable(): void
    {
        $html = $this->get('/products?q=headphone')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        // canonical همچنان به صفحهٔ تمیز اشاره می‌کند تا سیگنال‌ها ادغام شوند
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/products">', $html);
    }

    public function test_stored_noindex_flag_is_respected(): void
    {
        $product = $this->product();
        // فقط ایندکس خاموش شده — دنبال‌کردن لینک‌ها همچنان روشن است
        $product->syncSeoMeta(['fa' => ['is_indexable' => false]]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
    }

    public function test_stored_nofollow_flag_is_respected(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['is_indexable' => false, 'is_followable' => false]]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    public function test_route_policy_beats_a_stored_indexable_row(): void
    {
        // ادمین نمی‌تواند صفحهٔ خصوصی را با ذخیرهٔ متا ایندکس‌پذیر کند
        \App\Models\SeoMeta::create([
            'path' => '/cart',
            'locale' => 'fa',
            'title' => 'سبد خرید',
            'is_indexable' => true,
        ]);

        $html = $this->get('/cart')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    // ==========================================
    // سیاست ترجمهٔ ناقص
    // ==========================================

    public function test_page_without_translation_for_the_locale_is_noindexed(): void
    {
        $product = $this->product(['name' => ['fa' => 'فقط فارسی']]);

        $fa = $this->get('/products/'.$product->slug)->assertOk()->getContent();
        $en = $this->get('/en/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $fa);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $en);
    }

    public function test_translated_page_stays_indexable(): void
    {
        $product = $this->product(['name' => ['fa' => 'نام', 'en' => 'Name']]);

        $en = $this->get('/en/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $en);
    }

    // ==========================================
    // Open Graph / Twitter
    // ==========================================

    public function test_open_graph_tags_are_rendered(): void
    {
        $product = $this->product(['name' => ['fa' => 'هدفون']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:type" content="product">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="هدفون | فروشگاه شاپورا">', $html);
        $this->assertStringContainsString(
            '<meta property="og:url" content="http://localhost/products/'.$product->slug.'">',
            $html
        );
        $this->assertStringContainsString('<meta property="og:site_name" content="فروشگاه شاپورا">', $html);
    }

    public function test_non_product_pages_use_website_og_type(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
    }

    public function test_og_image_is_absolute_and_comes_from_the_product_image(): void
    {
        $product = $this->product();
        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'seo-test-image.jpg',
            'is_primary' => true,
        ]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '~<meta property="og:image" content="http://localhost/[^"]*seo-test-image\.jpg">~',
            $html
        );
    }

    public function test_relative_stored_og_image_is_made_absolute(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['og_image' => '/uploads/settings/og.jpg']]);

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString(
            '<meta property="og:image" content="http://localhost/uploads/settings/og.jpg">',
            $html
        );
    }

    public function test_og_image_tag_is_omitted_when_no_image_exists(): void
    {
        $product = $this->product();

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('<meta property="og:image"', $html);
    }

    public function test_og_locale_alternates_include_all_other_active_locales_except_current(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:locale" content="en_US">', $html);

        // هر سه زبان دیگر باید به‌عنوان alternate بیایند
        foreach (['fa_IR', 'ar_SA', 'tr_TR'] as $locale) {
            $this->assertStringContainsString('og:locale:alternate" content="'.$locale.'"', $html);
        }

        // زبان جاری نباید در alternate تکرار شود (باگ نسخهٔ قبلی)
        $this->assertStringNotContainsString('og:locale:alternate" content="en_US"', $html);
    }

    public function test_og_locale_alternates_on_default_locale_exclude_default(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:locale" content="fa_IR">', $html);
        $this->assertStringNotContainsString('og:locale:alternate" content="fa_IR"', $html);
        $this->assertStringContainsString('og:locale:alternate" content="en_US"', $html);
    }

    public function test_twitter_card_tags_are_rendered(): void
    {
        $product = $this->product();

        $html = $this->get('/products/'.$product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<meta name="twitter:title"', $html);
    }

    // ==========================================
    // صفحه‌های دیگر
    // ==========================================

    public function test_product_category_page_uses_its_own_meta(): void
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دستهٔ هدفون', 'en' => 'Headphones'],
            'slug' => 'seo-headphones',
        ]);
        $category->syncSeoMeta(['fa' => ['title' => 'متای دسته', 'description' => 'توضیح دسته']]);

        $html = $this->get('/product-categories/'.$category->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>متای دسته | فروشگاه شاپورا</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="توضیح دسته">', $html);
    }

    public function test_blog_page_uses_its_own_meta(): void
    {
        $blog = Blog::create([
            'title' => 'مقالهٔ تست',
            'slug' => 'seo-blog-post',
            'content' => '<p>محتوا</p>',
            'excerpt' => 'خلاصهٔ مقاله',
        ]);

        $html = $this->get('/blogs/'.$blog->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<title>مقالهٔ تست | فروشگاه شاپورا</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="خلاصهٔ مقاله">', $html);
    }

    public function test_static_page_description_comes_from_the_language_file(): void
    {
        $html = $this->get('/page/contact')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="description" content="', $html);
    }

    public function test_guest_layout_is_hardcoded_noindex(): void
    {
        // صفحات OTP فقط با state سشن قابل دسترسی‌اند (به‌طور طبیعی ۳۰۲ می‌دهند)،
        // پس خودِ چیدمان را رندر می‌کنیم تا تگ سخت‌کد‌شدهٔ noindex تأیید شود.
        $html = view('layouts.site.guest', ['slot' => 'x'])->render();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /**
     * رزولوشن را در بستر یک درخواست قطعی اجرا می‌کند تا بتوان اولویت
     * overrideهای صریح را مستقل از plumbing تست HTTP سنجید.
     *
     * @return array{0: array<string, mixed>, 1: Product}
     */
    private function resolveFor(Product $product, callable $configure): array
    {
        $request = Request::create('/products/'.$product->slug);
        app()->instance('request', $request);

        $configure();

        return [app(SeoResolver::class)->resolve(), $product];
    }

    /** @param array<string, mixed> $overrides */
    private function product(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دستهٔ تستی', 'en' => 'Test category'],
            'slug' => 'seo-cat-'.uniqid(),
        ]);

        return Product::create(array_merge([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test product'],
            'slug' => 'seo-product-'.uniqid(),
            'price' => 1000,
            'is_active' => true,
        ], $overrides));
    }
}
