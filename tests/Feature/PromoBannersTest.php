<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    public function test_promo_banners_visible_by_default_with_translated_fallbacks(): void
    {
        // پیش‌فرض: بنرها روشن و عنوان‌ها از کانفیگ → نمایش
        $this->get('/')
            ->assertOk()
            ->assertSee('promo-banner promo-banner--accent', false)
            ->assertSee('promo-banner promo-banner--gold', false)
            // fallback ترجمه‌شدهٔ زبان جاری
            ->assertSee(__('messages.promo_banner_1_title'))
            ->assertSee(__('messages.promo_banner_2_title'));
    }

    public function test_promo_banners_hidden_when_disabled(): void
    {
        app(SettingService::class)->updateGroup('banners', ['promo_banners_enabled' => '0'], []);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('promo-banner', false);
    }

    public function test_promo_banners_use_custom_text_and_link_from_settings(): void
    {
        app(SettingService::class)->updateGroup('banners', [
            'promo_banners_enabled' => '1',
            'promo_banner_1_title' => 'جشنواره پاییز',
            'promo_banner_1_subtitle' => 'تا ۴۰٪ تخفیف',
            'promo_banner_1_url' => 'https://example.com/sale',
        ], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('جشنواره پاییز')
            ->assertSee('تا ۴۰٪ تخفیف')
            ->assertSee('https://example.com/sale', false);
    }

    public function test_promo_banners_fallback_link_to_products_index(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(localizedRoute('products.index'), false);
    }

    public function test_settings_banners_page_renders_promo_fields(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'banners'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[promo_banners_enabled]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_1_title]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_1_subtitle]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_1_url]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_2_title]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_2_subtitle]"', $html);
        $this->assertStringContainsString('name="settings[promo_banner_2_url]"', $html);
    }
}
