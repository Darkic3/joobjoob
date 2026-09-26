<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FestiveModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function actingAsSuperAdmin(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($user);
    }

    public function test_festive_mode_defaults_to_off(): void
    {
        $this->assertFalse((bool) setting('festive_mode'));
    }

    public function test_festive_mode_can_be_enabled_via_settings(): void
    {
        $service = app(SettingService::class);

        $service->updateGroup('appearance', ['festive_mode' => '1'], []);

        $this->assertTrue((bool) setting('festive_mode'));
        $this->assertDatabaseHas('settings', ['key' => 'festive_mode', 'value' => '1']);
    }

    public function test_home_hero_never_renders_festive_chrome(): void
    {
        // هیرو مینیمال شد: کانفتی و بج جشن هرگز در صفحهٔ اصلی رندر نمی‌شوند،
        // حتی وقتی festive_mode فعال است (تنظیمات فقط در ادمین کاربرد دارند)
        $this->get('/')
            ->assertOk()
            ->assertDontSee('festive-confetti', false)
            ->assertDontSee('class="festive-badge"', false);
    }

    public function test_settings_appearance_page_renders_festive_toggle(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'appearance'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[festive_mode]"', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
    }

    public function test_festive_style_defaults_to_gold(): void
    {
        $this->assertSame('gold', setting('festive_style'));
    }

    public function test_festive_style_can_be_saved_via_settings(): void
    {
        $service = app(SettingService::class);

        $service->updateGroup('appearance', ['festive_style' => 'snow'], []);

        $this->assertSame('snow', setting('festive_style'));
        $this->assertDatabaseHas('settings', ['key' => 'festive_style', 'value' => 'snow']);
    }

    public function test_home_hero_ignores_festive_mode_and_style(): void
    {
        $service = app(SettingService::class);

        $service->updateGroup('appearance', ['festive_mode' => '1', 'festive_style' => 'colorful'], []);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="festive-confetti"', false)
            ->assertDontSee('data-style="colorful"', false);
    }

    public function test_settings_appearance_page_renders_festive_style_select(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'appearance'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[festive_style]"', $html);
        $this->assertStringContainsString('value="gold"', $html);
        $this->assertStringContainsString('value="colorful"', $html);
        $this->assertStringContainsString('value="snow"', $html);
        $this->assertStringContainsString('value="hearts"', $html);
    }

    public function test_home_hero_never_shows_festive_badge_even_when_enabled(): void
    {
        $service = app(SettingService::class);
        $service->updateGroup('appearance', ['festive_mode' => '1'], []);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('class="festive-badge"', false);
    }

    public function test_festive_badge_text_saves_but_is_not_rendered_on_home(): void
    {
        $service = app(SettingService::class);
        $service->updateGroup('appearance', ['festive_mode' => '1', 'festive_badge_text' => 'تخفیف نوروزی'], []);

        $this->assertSame('تخفیف نوروزی', setting('festive_badge_text'));
        $this->get('/')
            ->assertOk()
            ->assertDontSee('تخفیف نوروزی');
    }

    public function test_settings_appearance_page_renders_festive_badge_text_field(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'appearance'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[festive_badge_text]"', $html);
    }
}
