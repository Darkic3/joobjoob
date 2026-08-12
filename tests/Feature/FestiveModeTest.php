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

    public function test_home_hero_has_confetti_canvas_when_enabled(): void
    {
        $service = app(SettingService::class);
        $service->updateGroup('appearance', ['festive_mode' => '1'], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="festive-confetti"', false);
    }

    public function test_home_hero_has_no_confetti_canvas_when_disabled(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('festive-confetti', false);
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

    public function test_home_hero_canvas_carries_festive_style(): void
    {
        $service = app(SettingService::class);

        $service->updateGroup('appearance', ['festive_mode' => '1', 'festive_style' => 'colorful'], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="festive-confetti"', false)
            ->assertSee('data-style="colorful"', false);
    }

    public function test_settings_appearance_page_renders_festive_style_select(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'appearance'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[festive_style]"', $html);
        $this->assertStringContainsString('value="gold"', $html);
        $this->assertStringContainsString('value="colorful"', $html);
        $this->assertStringContainsString('value="snow"', $html);
    }

    public function test_home_hero_shows_festive_badge_when_enabled(): void
    {
        $service = app(SettingService::class);
        $service->updateGroup('appearance', ['festive_mode' => '1'], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('class="festive-badge"', false)
            ->assertSee(__('messages.festive_badge'));
    }

    public function test_home_hero_hides_festive_badge_when_disabled(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('festive-badge', false);
    }

    public function test_festive_badge_uses_custom_text_from_settings(): void
    {
        $service = app(SettingService::class);
        $service->updateGroup('appearance', ['festive_mode' => '1', 'festive_badge_text' => 'تخفیف نوروزی'], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('تخفیف نوروزی');
    }

    public function test_settings_appearance_page_renders_festive_badge_text_field(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'appearance'))->assertOk()->getContent();

        $this->assertStringContainsString('name="settings[festive_badge_text]"', $html);
    }
}
