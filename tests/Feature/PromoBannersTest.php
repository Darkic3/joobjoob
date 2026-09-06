<?php

namespace Tests\Feature;

use App\Models\PromoBanner;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * بنرهای میانی صفحهٔ اصلی — مدل مستقل PromoBanner (۱ تا ۶ بنر با سبک/رنگ دلخواه،
 * در سایت به‌صورت اسلایدر نمایش داده می‌شود و تک‌بنر تمام‌عرض می‌ماند).
 */
class PromoBannersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    }

    private function makeBanner(array $overrides = []): PromoBanner
    {
        return PromoBanner::create(array_merge([
            'icon' => 'megaphone',
            'title' => ['fa' => 'ارسال رایگان', 'en' => 'Free Shipping'],
            'subtitle' => ['fa' => 'برای خرید بالای ۵۰۰ هزار تومان', 'en' => 'On orders over 500K'],
            'url' => null,
            'style' => 'gradient',
            'tone' => 'accent',
            'sort_order' => (int) PromoBanner::max('sort_order') + 1,
            'is_active' => true,
        ], $overrides));
    }

    // ── نمایش در سایت ──────────────────────────────────────────

    public function test_home_shows_only_active_banners_in_order(): void
    {
        $this->makeBanner(['title' => ['fa' => 'بنر اول'], 'sort_order' => 2]);
        $this->makeBanner(['title' => ['fa' => 'بنر دوم'], 'sort_order' => 1]);
        $this->makeBanner(['title' => ['fa' => 'بنر مخفی'], 'is_active' => false, 'sort_order' => 3]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('promo-banner', false);
        // ترتیب نمایش: بنر دوم (sort 1) قبل از بنر اول (sort 2)
        $this->assertLessThan(
            strpos($response->getContent(), 'بنر اول'),
            strpos($response->getContent(), 'بنر دوم'),
            'بنر با sort_order کمتر باید زودتر نمایش داده شود'
        );
        $response->assertDontSee('بنر مخفی');
    }

    public function test_each_banner_uses_its_own_style_and_tone(): void
    {
        $this->makeBanner(['style' => 'gradient', 'tone' => 'accent']);
        $this->makeBanner(['style' => 'coupon', 'tone' => 'gold']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('promo-banner--gradient promo-banner--accent', $html);
        $this->assertStringContainsString('promo-banner--coupon promo-banner--gold', $html);
    }

    public function test_multiple_banners_render_inside_the_swiper_slider(): void
    {
        $this->makeBanner(['title' => ['fa' => 'بنر اسلایدر ۱']]);
        $this->makeBanner(['title' => ['fa' => 'بنر اسلایدر ۲']]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-promo-swiper', $html);
        // فقط داخل بخش بنرها بشمار (صفحه اسلایدهای سوییپرهای دیگر هم دارد)
        $promoSlice = substr($html, strpos($html, 'data-promo-swiper'));
        $this->assertSame(2, substr_count($promoSlice, 'class="swiper-slide"'));
        $this->assertStringContainsString('promo-swiper__pagination', $html);
    }

    public function test_banner_without_url_is_not_clickable(): void
    {
        $this->makeBanner();

        $html = $this->get('/')->assertOk()->getContent();

        // بدون لینک: به جای <a> یک <div> رندر می‌شود (کلاس promo-banner--static) و لینک پیش‌فرض به محصولات ندارد
        $this->assertStringContainsString('promo-banner--static', $html);
        $this->assertStringNotContainsString('<a href="'.localizedRoute('products.index').'" class="promo-banner', $html);
        // تک‌بنر: اسلایدر ساخته نمی‌شود (تمام‌عرض بدون کروم)
        $this->assertStringNotContainsString('data-promo-swiper', $html);
    }

    public function test_banner_uses_custom_url(): void
    {
        $this->makeBanner(['url' => 'https://example.com/sale']);

        $this->get('/')->assertOk()->assertSee('https://example.com/sale', false);
    }

    public function test_title_translates_per_locale(): void
    {
        $this->makeBanner(['title' => ['fa' => 'عبارت فارسی یکتا', 'en' => 'Unique English Phrase']]);

        $this->get('/en')->assertOk()->assertSee('Unique English Phrase')->assertDontSee('عبارت فارسی یکتا');
    }

    public function test_missing_locale_falls_back_to_fa(): void
    {
        $this->makeBanner(['title' => ['fa' => 'عبارت فارسی یکتا', 'en' => '']]);

        $this->get('/en')->assertOk()->assertSee('عبارت فارسی یکتا');
    }

    public function test_banner_renders_its_icon(): void
    {
        $this->makeBanner(['icon' => 'gift']);

        $html = $this->get('/')->assertOk()->getContent();

        // آیکون باید در کامپوننت SVG رندر شود — مسیر مخصوص آیکون هدیه
        $this->assertStringContainsString('stroke-linecap="round"', $html);
        $this->assertStringContainsString('rect x="3" y="8" width="18"', $html);
    }

    public function test_site_shows_max_six_banners(): void
    {
        foreach (range(1, 7) as $i) {
            $this->makeBanner(['title' => ['fa' => "بنر $i"], 'sort_order' => $i]);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(6, substr_count($html, 'promo-banner promo-banner--'));
    }

    // ── CRUD ادمین ─────────────────────────────────────────────

    public function test_admin_can_create_banner_with_icon_and_tone(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.promo-banners.store'), [
            'icon' => 'fire',
            'title' => ['fa' => 'جشنواره پاییز', 'en' => 'Autumn Sale'],
            'subtitle' => ['fa' => 'تا ۴۰٪ تخفیف', 'en' => 'Up to 40% off'],
            'url' => 'https://example.com/sale',
            'style' => 'dark',
            'tone' => 'violet',
            'is_active' => '1',
        ])->assertRedirect(route('admin.promo-banners.index'));

        $this->assertDatabaseHas('promo_banners', [
            'icon' => 'fire',
            'style' => 'dark',
            'tone' => 'violet',
            'url' => 'https://example.com/sale',
            'is_active' => 1,
        ]);
    }

    public function test_admin_create_rejects_invalid_icon(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.promo-banners.store'), [
            'icon' => 'spaceship',
            'title' => ['fa' => 'بنر تست'],
            'style' => 'gradient',
            'tone' => 'accent',
        ])->assertSessionHasErrors('icon');
    }

    public function test_admin_create_rejects_invalid_tone(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.promo-banners.store'), [
            'icon' => 'megaphone',
            'title' => ['fa' => 'بنر تست'],
            'style' => 'gradient',
            'tone' => 'rainbow',
        ])->assertSessionHasErrors('tone');
    }

    public function test_all_new_tones_are_accepted(): void
    {
        $this->actingAsSuperAdmin();

        foreach (['emerald', 'rose', 'blue', 'orange', 'violet'] as $tone) {
            $this->post(route('admin.promo-banners.store'), [
                'icon' => 'truck',
                'title' => ['fa' => "بنر $tone"],
                'style' => 'gradient',
                'tone' => $tone,
            ])->assertRedirect(route('admin.promo-banners.index'));
        }

        $this->assertSame(5, PromoBanner::whereIn('tone', ['emerald', 'rose', 'blue', 'orange', 'violet'])->count());
    }

    public function test_admin_create_requires_fa_title(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.promo-banners.store'), [
            'title' => ['fa' => '', 'en' => 'Autumn Sale'],
            'style' => 'gradient',
            'tone' => 'accent',
        ])->assertSessionHasErrors('title.fa');
    }

    public function test_admin_can_update_banner(): void
    {
        $this->actingAsSuperAdmin();
        $banner = $this->makeBanner();

        $this->put(route('admin.promo-banners.update', $banner), [
            'icon' => 'star',
            'title' => ['fa' => 'بنر به‌روز شده', 'en' => 'Updated Banner'],
            'subtitle' => ['fa' => 'زیرنویس جدید', 'en' => 'New subtitle'],
            'url' => '',
            'style' => 'minimal',
            'tone' => 'gold',
            'is_active' => '0',
        ])->assertRedirect(route('admin.promo-banners.index'));

        $banner->refresh();
        $this->assertSame('بنر به‌روز شده', $banner->title['fa']);
        $this->assertSame('minimal', $banner->style);
        $this->assertSame('gold', $banner->tone);
        $this->assertFalse($banner->is_active);
        $this->assertNull($banner->url);
    }

    public function test_admin_can_delete_banner(): void
    {
        $this->actingAsSuperAdmin();
        $banner = $this->makeBanner();

        $this->delete(route('admin.promo-banners.destroy', $banner))
            ->assertRedirect(route('admin.promo-banners.index'));

        $this->assertDatabaseMissing('promo_banners', ['id' => $banner->id]);
    }

    public function test_admin_can_reorder_banners(): void
    {
        $this->actingAsSuperAdmin();
        $b1 = $this->makeBanner(['sort_order' => 1]);
        $b2 = $this->makeBanner(['sort_order' => 2]);
        $b3 = $this->makeBanner(['sort_order' => 3]);

        $this->postJson(route('admin.promo-banners.reorder'), [
            'ids' => [$b3->id, $b1->id, $b2->id],
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(1, (int) PromoBanner::find($b3->id)->sort_order);
        $this->assertSame(2, (int) PromoBanner::find($b1->id)->sort_order);
        $this->assertSame(3, (int) PromoBanner::find($b2->id)->sort_order);
    }

    public function test_admin_index_renders_banners(): void
    {
        $this->actingAsSuperAdmin();
        $this->makeBanner(['title' => ['fa' => 'بنر تست']]);

        $this->get(route('admin.promo-banners.index'))
            ->assertOk()
            ->assertSee('بنر تست');
    }

    public function test_settings_page_has_no_banner_fields_anymore(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'general'))->assertOk()->getContent();

        $this->assertStringNotContainsString('promo_banners_enabled', $html);
        $this->assertStringNotContainsString('promo_banner_1_title', $html);
    }
}
