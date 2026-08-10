<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CropperModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // حافظهٔ درخواستی SettingService بین تست‌ها می‌ماند — هر تست از حالت تمیز شروع کند
        app(SettingService::class)->flush();
    }

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    }

    /**
     * لایهٔ ادمین باید استک modals را yield کند؛ در غیر این صورت محتوای
     *
     * @push('modals') (مدال کراپر، سئو، FAQ، جستجوی پیشرفته) هرگز در DOM نوشته
     * نمی‌شود و دکمه‌ها بدون مدال می‌مانند. این تست باگ orphaned-stack را می‌گیرد.
     */
    public function test_cropper_modal_is_rendered_into_the_modals_stack(): void
    {
        $this->actingAsSuperAdmin();

        // هیچ فیلدی در کانفیگ واقعی cropper ندارد؛ برای رندر شاخهٔ مدال،
        // فیلد logo را موقتاً cropper می‌کنیم.
        config(['settings.fields.logo.cropper' => true]);

        // مقدار فعلی برای logo می‌سازیم تا بخش «تصویر فعلی» و مدال رندر شوند
        Setting::create([
            'key' => 'logo',
            'value' => 'logo.png',
            'type' => 'file',
            'group' => 'general',
            'label' => 'لوگو',
        ]);

        $html = $this->get(route('admin.settings.edit', 'general'))->assertOk()->getContent();

        // اگر @stack('modals') از لایه حذف شود یا نام صف عوض شود، این‌ها شکست می‌خورند:
        $this->assertStringContainsString('id="ac-modal-logo"', $html);
        $this->assertStringContainsString('id="ac-img-logo"', $html);
        $this->assertStringContainsString('id="ac-confirm-logo"', $html);
        $this->assertStringContainsString('aspectRatio: 0.67750677506775', $html);
        $this->assertStringContainsString('width: 500', $html);
    }
}
