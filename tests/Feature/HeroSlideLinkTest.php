<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideLinkTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    }

    private function createProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'slug' => 'cat-'.uniqid(),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول', 'en' => 'Product'],
            'slug' => 'product-'.uniqid(),
            'price' => 1000,
            'stock' => 5,
            'is_active' => true,
        ], $overrides));
    }

    // ==========================================
    // جستجوی مدل‌ها (issue 2)
    // ==========================================

    public function test_search_returns_products(): void
    {
        app()->setLocale('fa');
        $this->actingAsSuperAdmin();

        $this->createProduct([
            'name' => ['fa' => 'گوشی سامسونگ', 'en' => 'Samsung Phone'],
            'slug' => 'samsung-phone',
            'price' => 1000000,
        ]);
        // محصول غیرفعال نباید در نتیجه باشد
        $this->createProduct([
            'name' => ['fa' => 'گوشی قدیمی', 'en' => 'Old Phone'],
            'slug' => 'old-phone',
            'price' => 100000,
            'is_active' => false,
        ]);

        $this->getJson('/admin/hero-slides/search-model?type=product&q=سامسونگ')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'گوشی سامسونگ');
    }

    public function test_search_returns_product_categories(): void
    {
        app()->setLocale('fa');
        $this->actingAsSuperAdmin();

        ProductCategory::create([
            'name' => ['fa' => 'موبایل', 'en' => 'Mobile'],
            'slug' => 'mobile',
            'is_active' => true,
        ]);

        $this->getJson('/admin/hero-slides/search-model?type=product_category&q=موبایل')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'موبایل');
    }

    public function test_search_returns_blogs(): void
    {
        $this->actingAsSuperAdmin();

        Blog::create([
            'title' => 'راهنمای خرید گوشی',
            'slug' => 'phone-buying-guide',
            'content' => '<p>محتوا</p>',
            'is_active' => true,
        ]);

        $this->getJson('/admin/hero-slides/search-model?type=blog&q=راهنمای')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'راهنمای خرید گوشی');
    }

    public function test_search_returns_blog_categories(): void
    {
        $this->actingAsSuperAdmin();

        $category = BlogCategory::create([
            'title' => 'تکنولوژی',
            'slug' => 'tech',
            'is_active' => true,
        ]);
        Blog::create([
            'title' => 'مقاله تست',
            'slug' => 'test-post',
            'content' => '<p>م</p>',
            'blog_category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->getJson('/admin/hero-slides/search-model?type=blog_category&q=تکنولوژی')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.subtitle', '1 مقاله');
    }

    public function test_search_short_query_returns_empty(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/admin/hero-slides/search-model?type=product&q=a')
            ->assertOk()
            ->assertExactJson([]);
    }

    // ==========================================
    // fallback تصویر به مدل لینک‌شده (issue 3)
    // ==========================================

    public function test_slide_without_image_uses_linked_product_main_image(): void
    {
        $product = $this->createProduct(['main_image' => 'product-main.webp']);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'image' => null,
            'linkable_type' => Product::class,
            'linkable_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertSame(
            asset(config('paths.upload', 'uploads').'/products/product-main.webp'),
            $slide->imageUrl()
        );
    }

    public function test_slide_with_image_uses_its_own_image(): void
    {
        $product = $this->createProduct(['main_image' => 'product-main.webp']);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'image' => 'hero-own.webp',
            'linkable_type' => Product::class,
            'linkable_id' => $product->id,
            'is_active' => true,
        ]);

        $this->assertSame(
            asset(config('paths.upload', 'uploads').'/hero-slides/hero-own.webp'),
            $slide->imageUrl()
        );
    }

    public function test_slide_without_image_uses_linked_blog_image(): void
    {
        $blog = Blog::create([
            'title' => 'مقاله',
            'slug' => 'blog',
            'content' => '<p>م</p>',
            'image' => 'blog-cover.webp',
            'is_active' => true,
        ]);

        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'image' => null,
            'linkable_type' => Blog::class,
            'linkable_id' => $blog->id,
            'is_active' => true,
        ]);

        $this->assertSame(
            asset(config('paths.upload', 'uploads').'/blogs/blog-cover.webp'),
            $slide->imageUrl()
        );
    }

    public function test_slide_without_image_and_without_link_returns_empty(): void
    {
        $slide = HeroSlide::create([
            'title' => ['fa' => 'تست', 'en' => 'Test'],
            'image' => null,
            'is_active' => true,
        ]);

        $this->assertSame('', $slide->imageUrl());
    }

    // ==========================================
    // یک فیلد واحد link_value در فرم (issue 1)
    // ==========================================

    public function test_hero_form_has_single_link_value_field(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.create'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'name="link_value"'));
    }
}
