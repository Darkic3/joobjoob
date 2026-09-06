<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use Carbon\Carbon;
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

    private function makeBlog(string $slug, string $title, int $daysAgo = 0, bool $active = true, ?string $content = null, int $views = 0): Blog
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
            'content' => $content ?? 'متن کامل مقاله',
            'views' => $views,
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

    // ==========================================
    // بازدید و زمان مطالعه
    // ==========================================

    public function test_reading_minutes_estimates_from_content_length(): void
    {
        // متن کوتاه → حداقل ۱ دقیقه
        $short = $this->makeBlog('short-post', 'مقاله کوتاه');
        $this->assertSame(1, $short->readingMinutes());

        // ~۵۰۰ واژه → ۳ دقیقه (ceil(500/200))
        $longContent = implode(' ', array_fill(0, 500, 'واژه'));
        $long = $this->makeBlog('long-post', 'مقاله بلند', 0, true, $longContent);
        $this->assertSame(3, $long->readingMinutes());
    }

    public function test_home_blog_card_shows_views_and_reading_time(): void
    {
        $longContent = implode(' ', array_fill(0, 500, 'واژه'));
        $this->makeBlog('stats-post', 'مقاله با آمار', 0, true, $longContent, 12345);

        $this->get('/')
            ->assertOk()
            // با میدل‌ور LocalizeHtmlDigits خروجیِ HTML از همان ابتدا فارسی است
            ->assertSee(\App\Helpers\NumberHelper::localizeText('12,345'), false)
            ->assertSee(\App\Helpers\NumberHelper::localizeText(__('messages.reading_time', ['minutes' => 3])));
    }

    public function test_blog_date_is_localized_per_active_language(): void
    {
        $blog = $this->makeBlog('date-post', 'مقاله با تاریخ');
        $blog->created_at = Carbon::parse('2026-08-06 12:00:00');
        $blog->save();

        // فارسی (پیش‌فرض، بدون پیشوند) → تقویم جلالی با نام ماه فارسی
        $this->get('/')
            ->assertOk()
            ->assertSee('مرداد')
            ->assertSee('۱۴۰۵');

        // انگلیسی (پیشوند /en) → میلادی با نام ماه انگلیسی
        $this->get('/en')
            ->assertOk()
            ->assertSee('August 2026');

        // عربی (پیشوند /ar) → تقویم قمری با نام ماه عربی و ارقام عربی
        $this->get('/ar')
            ->assertOk()
            ->assertSee('٢١ صفر ١٤٤٨');
    }
}
