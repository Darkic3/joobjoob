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
}
