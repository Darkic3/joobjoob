<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($user);
    }

    public function test_default_values_come_from_config(): void
    {
        Cache::flush();
        $service = app(SettingService::class);

        $this->assertSame('فروشگاه شاپورا', $service->get('site_name'));
        $this->assertSame('ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان', $service->get('announce_1'));
        $this->assertNull($service->get('social_instagram'));
    }

    public function test_service_saves_and_reads_back_with_cache_invalidation(): void
    {
        Cache::flush();
        $service = app(SettingService::class);

        $service->updateGroup('general', [
            'site_name' => 'فروشگاه تست',
            'site_slogan' => 'اسلوگان تست',
        ], []);

        $this->assertSame('فروشگاه تست', $service->get('site_name'));
        $this->assertSame('فروشگاه تست', Setting::where('key', 'site_name')->value('value'));
    }

    public function test_fields_for_group_filters_by_tab(): void
    {
        $service = app(SettingService::class);

        $general = array_keys($service->fieldsForGroup('general'));
        $home = array_keys($service->fieldsForGroup('home'));

        $this->assertContains('site_name', $general);
        $this->assertContains('logo', $general);
        $this->assertContains('home_deals_title', $home);
        $this->assertNotContains('home_deals_title', $general);
        $this->assertNotContains('site_name', $home);
    }

    public function test_super_admin_can_update_settings_via_http(): void
    {
        $this->actingAsSuperAdmin();

        $this->put(route('admin.settings.update', 'general'), [
            'settings' => [
                'site_name' => 'فروشگاه اینترنتی تست',
                'site_slogan' => 'شعار جدید',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'فروشگاه اینترنتی تست']);
        $this->assertSame('فروشگاه اینترنتی تست', app(SettingService::class)->get('site_name'));
    }

    public function test_settings_edit_page_renders_group_fields(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'general'))->assertOk()->getContent();

        $this->assertStringContainsString('site_name', $html);
        $this->assertStringContainsString('site_slogan', $html);
    }
}
