<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Brand;
use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageRemovalTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function heroSlidePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => ['fa' => 'اسلاید تست', 'en' => 'Test Slide'],
            'desc' => ['fa' => '', 'en' => ''],
            'badge' => ['fa' => '', 'en' => ''],
            'button_active' => 0,
            'btn_text' => ['fa' => '', 'en' => ''],
            'is_active' => 1,
        ], $overrides);
    }

    private function blogPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'مقاله تست',
            'slug' => 'test-blog-post',
            'content' => '<p>محتوا</p>',
            'is_active' => 1,
        ], $overrides);
    }

    private function brandPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => ['fa' => 'برند تست', 'en' => 'Test Brand'],
            'url' => 'test-brand',
            'description' => ['fa' => '', 'en' => ''],
            'active' => 1,
        ], $overrides);
    }

    public function test_hero_slide_update_with_remove_image_clears_image(): void
    {
        $slide = HeroSlide::create([
            'title' => ['fa' => 'قدیمی', 'en' => 'Old'],
            'image' => 'old-hero.webp',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put(route('admin.hero-slides.update'), $this->heroSlidePayload(['remove_image' => '1']))
            ->assertRedirect(route('admin.hero-slides.index'));

        $this->assertNull($slide->refresh()->image);
    }

    public function test_hero_slide_update_without_image_and_without_remove_keeps_image(): void
    {
        $slide = HeroSlide::create([
            'title' => ['fa' => 'قدیمی', 'en' => 'Old'],
            'image' => 'keep-hero.webp',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put(route('admin.hero-slides.update'), $this->heroSlidePayload(['title' => ['fa' => 'جدید', 'en' => 'New']]))
            ->assertRedirect(route('admin.hero-slides.index'));

        $this->assertSame('keep-hero.webp', $slide->refresh()->image);
    }

    public function test_hero_prune_extras_removes_other_slides_only_when_requested(): void
    {
        $primary = HeroSlide::create(['title' => ['fa' => 'اصلی'], 'is_active' => true, 'sort_order' => 1]);
        $extra = HeroSlide::create(['title' => ['fa' => 'اضافی'], 'is_active' => true, 'sort_order' => 2]);

        // بدون تیک حذف → اسلاید اضافی می‌ماند
        $this->actingAs($this->superAdmin())
            ->put(route('admin.hero-slides.update'), $this->heroSlidePayload())
            ->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseCount('hero_slides', 2);

        // با تیک حذف → فقط هیرو اصلی می‌ماند
        $this->actingAs($this->superAdmin())
            ->put(route('admin.hero-slides.update'), $this->heroSlidePayload(['prune_extras' => '1']))
            ->assertRedirect(route('admin.hero-slides.index'));

        $this->assertDatabaseCount('hero_slides', 1);
        $this->assertDatabaseHas('hero_slides', ['id' => $primary->id]);
        $this->assertDatabaseMissing('hero_slides', ['id' => $extra->id]);
    }

    public function test_blog_update_with_remove_image_clears_image(): void
    {
        $blog = Blog::create([
            'title' => 'قدیمی',
            'slug' => 'old-post',
            'content' => '<p>محتوا</p>',
            'image' => 'old-blog.webp',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/blogs/{$blog->id}", $this->blogPayload(['remove_image' => '1']))
            ->assertRedirect(route('admin.blogs.index'));

        $this->assertNull($blog->refresh()->image);
    }

    public function test_blog_update_without_image_and_without_remove_keeps_image(): void
    {
        $blog = Blog::create([
            'title' => 'قدیمی',
            'slug' => 'old-post',
            'content' => '<p>محتوا</p>',
            'image' => 'keep-blog.webp',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/blogs/{$blog->id}", $this->blogPayload(['title' => 'جدید']))
            ->assertRedirect(route('admin.blogs.index'));

        $this->assertSame('keep-blog.webp', $blog->refresh()->image);
    }

    public function test_brand_update_with_remove_image_clears_image(): void
    {
        $brand = Brand::create([
            'title' => ['fa' => 'قدیمی', 'en' => 'Old'],
            'url' => 'old-brand',
            'image' => 'old-brand.webp',
            'active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/brands/{$brand->id}", $this->brandPayload(['remove_image' => '1']))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertNull($brand->refresh()->image);
    }

    public function test_brand_update_without_image_and_without_remove_keeps_image(): void
    {
        $brand = Brand::create([
            'title' => ['fa' => 'قدیمی', 'en' => 'Old'],
            'url' => 'old-brand',
            'image' => 'keep-brand.webp',
            'active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/brands/{$brand->id}", $this->brandPayload(['title' => ['fa' => 'جدید', 'en' => 'New']]))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertSame('keep-brand.webp', $brand->refresh()->image);
    }
}
