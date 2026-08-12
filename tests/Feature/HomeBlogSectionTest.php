<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeBlogSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function makeBlog(string $slug, string $title, int $daysAgo = 0, bool $active = true): Blog
    {
        $category = BlogCategory::firstOrCreate(['slug' => 'blog-cat'], [
            'title' => 'دسته مقالات',
            'is_active' => true,
        ]);

        $blog = Blog::create([
            'blog_category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'خلاصه‌ای از این مقاله برای نمایش در کارت',
            'content' => 'متن کامل مقاله',
            'is_active' => $active,
        ]);

        // created_at در fillable نیست — بعد از ساخت تنظیم می‌شود
        $blog->created_at = now()->subDays($daysAgo);
        $blog->save();

        return $blog;
    }

    public function test_home_page_shows_latest_articles_section(): void
    {
        $this->makeBlog('post-one', 'مقاله اول');
        $this->makeBlog('post-two', 'مقاله دوم');

        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.latest_articles'))
            ->assertSee('مقاله اول')
            ->assertSee('مقاله دوم');
    }

    public function test_home_page_hides_section_when_no_blogs(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee(__('messages.latest_articles'), false);
    }

    public function test_home_page_shows_only_the_latest_three_blogs(): void
    {
        $this->makeBlog('oldest', 'قدیمی‌ترین مقاله', 10);
        $this->makeBlog('one', 'جدیدترین', 0);
        $this->makeBlog('two', 'میانی یک', 1);
        $this->makeBlog('three', 'میانی دو', 2);

        $this->get('/')
            ->assertOk()
            ->assertSee('جدیدترین')
            ->assertSee('میانی یک')
            ->assertSee('میانی دو')
            ->assertDontSee('قدیمی‌ترین مقاله');
    }

    public function test_inactive_blogs_are_not_shown_on_home_page(): void
    {
        $this->makeBlog('active-one', 'مقاله فعال', 1);
        $this->makeBlog('inactive', 'مقاله غیرفعال', 0, false);

        $this->get('/')
            ->assertOk()
            ->assertSee('مقاله فعال')
            ->assertDontSee('مقاله غیرفعال');
    }
}
