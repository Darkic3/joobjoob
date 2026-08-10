<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // زبان‌های واقعی (پیش‌فرض fa) تا localizedRoute پیشوند زبان را درست تولید کند
        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    // ==========================================
    // URL اسلاید برای هر نوع لینک
    // ==========================================

    public function test_custom_url_uses_the_url_directly(): void
    {
        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => 'url',
            'linkable_id' => 'https://example.com',
            'is_active' => true,
        ]);

        $this->assertSame('https://example.com', $slide->url);
    }

    public function test_product_link_goes_to_product_page(): void
    {
        app()->setLocale('fa');

        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'slug' => 'cat',
            'is_active' => true,
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول', 'en' => 'Product'],
            'slug' => 'my-product',
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => Product::class,
            'linkable_id' => $product->id,
            'is_active' => true,
        ]);

        // زبان پیش‌فرض (fa) پیشوند نمی‌گیرد
        $this->assertSame(route('products.show', ['product' => 'my-product']), $slide->url);
        $this->assertStringEndsWith('/products/my-product', $slide->url);
    }

    public function test_product_link_gets_locale_prefix_in_other_languages(): void
    {
        app()->setLocale('en');

        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'slug' => 'cat',
            'is_active' => true,
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول', 'en' => 'Product'],
            'slug' => 'my-product',
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => Product::class,
            'linkable_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('/en/products/my-product', $slide->url);
    }

    public function test_product_category_link_goes_to_category_page(): void
    {
        app()->setLocale('fa');

        $category = ProductCategory::create([
            'name' => ['fa' => 'موبایل', 'en' => 'Mobile'],
            'slug' => 'mobile',
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => ProductCategory::class,
            'linkable_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('/product-categories/mobile', $slide->url);
    }

    public function test_blog_link_goes_to_blog_page(): void
    {
        app()->setLocale('fa');

        $blog = Blog::create([
            'title' => 'راهنما',
            'slug' => 'guide',
            'content' => '<p>م</p>',
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => Blog::class,
            'linkable_id' => $blog->id,
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('/blogs/guide', $slide->url);
    }

    public function test_blog_category_link_goes_to_blog_category_page(): void
    {
        app()->setLocale('fa');

        $category = BlogCategory::create([
            'title' => 'تکنولوژی',
            'slug' => 'tech',
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => BlogCategory::class,
            'linkable_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('/blog-categories/tech', $slide->url);
    }

    public function test_static_page_link_goes_to_page_route(): void
    {
        app()->setLocale('fa');

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'linkable_type' => 'page',
            'linkable_id' => 'contact',
            'is_active' => true,
        ]);

        $this->assertStringEndsWith('/page/contact', $slide->url);
    }

    public function test_slide_without_valid_link_returns_hash(): void
    {
        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'is_active' => true,
        ]);

        $this->assertSame('#', $slide->url);
    }

    // ==========================================
    // صفحات جدید سایت کار می‌کنند
    // ==========================================

    public function test_blog_index_page_renders(): void
    {
        Blog::create([
            'title' => 'مقاله اول',
            'slug' => 'first-post',
            'content' => '<p>م</p>',
            'is_active' => true,
        ]);

        $this->get('/blogs')->assertOk()->assertSee('مقاله اول');
    }

    public function test_blog_show_page_renders_and_increments_views(): void
    {
        $blog = Blog::create([
            'title' => 'مقاله تست',
            'slug' => 'test-post',
            'content' => '<p>محتوای مقاله</p>',
            'is_active' => true,
        ]);

        $this->get('/blogs/test-post')
            ->assertOk()
            ->assertSee('مقاله تست')
            ->assertSee('محتوای مقاله');

        $this->assertSame(1, $blog->fresh()->views);
    }

    public function test_inactive_blog_returns_404(): void
    {
        Blog::create([
            'title' => 'مخفی',
            'slug' => 'hidden-post',
            'content' => '<p>م</p>',
            'is_active' => false,
        ]);

        $this->get('/blogs/hidden-post')->assertNotFound();
    }

    public function test_blog_category_page_shows_its_posts(): void
    {
        $category = BlogCategory::create([
            'title' => 'تکنولوژی',
            'slug' => 'tech',
            'is_active' => true,
        ]);
        Blog::create([
            'title' => 'مقاله دسته',
            'slug' => 'cat-post',
            'content' => '<p>م</p>',
            'blog_category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->get('/blog-categories/tech')->assertOk()->assertSee('مقاله دسته');
    }

    public function test_product_category_page_shows_its_products(): void
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'موبایل', 'en' => 'Mobile'],
            'slug' => 'mobile',
            'is_active' => true,
        ]);
        Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'گوشی', 'en' => 'Phone'],
            'slug' => 'phone',
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->get('/product-categories/mobile')->assertOk()->assertSee('گوشی');
    }

    public function test_static_page_renders_with_translated_title(): void
    {
        app()->setLocale('fa');

        $this->get('/page/about')->assertOk()->assertSee('درباره ما');
        $this->get('/page/contact')->assertOk()->assertSee('تماس با ما');
        $this->get('/page/unknown-page')->assertNotFound();
    }
}
