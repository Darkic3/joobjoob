<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_index_page_lists_languages(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.languages.index'))
            ->assertOk()
            ->assertSee('فارسی')
            ->assertSee('English')
            ->assertSee('العربية')
            ->assertSee('Türkçe');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.languages.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.languages.create'))
            ->assertOk()
            ->assertSee('name="code"', false);
    }

    public function test_store_creates_language_and_flushes_cache(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.languages.store'), [
                'code' => 'de',
                'name' => ['fa' => 'آلمانی', 'en' => 'German'],
                'native_name' => 'Deutsch',
                'dir' => 'ltr',
                'sort_order' => 5,
                'is_active' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.languages.index'));

        $this->assertDatabaseHas('languages', ['code' => 'de']);
        $this->assertTrue(app(LocaleService::class)->has('de'));
    }

    public function test_store_rejects_invalid_code(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.languages.store'), [
                'code' => 'D3', // حروف بزرگ + رقم
                'name' => ['fa' => 'تست'],
                'native_name' => 'Test',
                'dir' => 'ltr',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('languages', ['code' => 'D3']);
    }

    public function test_store_rejects_duplicate_code(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.languages.store'), [
                'code' => 'en', // تکراری
                'name' => ['fa' => 'انگلیسی'],
                'native_name' => 'English',
                'dir' => 'ltr',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_update_works_without_code_field(): void
    {
        // regression: فیلد code در فرم ویرایش disabled است و سابمیت نمی‌شود
        // — ریکوئست نباید در ویرایش به code نیاز داشته باشد
        $en = Language::where('code', 'en')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.languages.update', $en), [
                'native_name' => 'English Updated',
                'name' => ['fa' => 'انگلیسی', 'en' => 'English'],
                'dir' => 'ltr',
                'sort_order' => 2,
                'is_active' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.languages.edit', $en));

        $this->assertSame('English Updated', $en->fresh()->native_name);
        // کد دست‌نخورده بماند
        $this->assertSame('en', $en->fresh()->code);
    }

    public function test_making_language_default_unsets_others(): void
    {
        $en = Language::where('code', 'en')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.languages.update', $en), [
                'native_name' => 'English',
                'name' => ['fa' => 'انگلیسی', 'en' => 'English'],
                'dir' => 'ltr',
                'sort_order' => 2,
                'is_active' => 1,
                'is_default' => 1,
            ])
            ->assertRedirect();

        $this->assertTrue(Language::where('code', 'en')->value('is_default'));
        $this->assertFalse(Language::where('code', 'fa')->value('is_default'));
    }

    public function test_deactivating_default_via_update_moves_default_to_next_active(): void
    {
        // زبان پیش‌فرض (fa) از مسیر update غیرفعال می‌شود
        // → ensureActiveDefault باید پیش‌فرض را به اولین زبان فعال دیگر منتقل کند
        $fa = Language::where('code', 'fa')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.languages.update', $fa), [
                'native_name' => 'فارسی',
                'name' => ['fa' => 'فارسی', 'en' => 'Persian'],
                'dir' => 'rtl',
                'sort_order' => 1,
                'is_active' => 0,
                'is_default' => 0,
            ])
            ->assertRedirect();

        $this->assertFalse(Language::where('code', 'fa')->value('is_active'));
        // پیش‌فرض به اولین زبان فعال (en) منتقل شده است
        $this->assertTrue(Language::where('code', 'en')->value('is_default'));
        $this->assertTrue(Language::where('is_default', true)->count() === 1);
    }

    public function test_toggle_deactivates_language(): void
    {
        $en = Language::where('code', 'en')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.languages.toggle', $en))
            ->assertRedirect();

        $this->assertFalse(Language::where('code', 'en')->value('is_active'));
    }

    public function test_cannot_deactivate_last_active_language(): void
    {
        Language::where('code', '!=', 'fa')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $fa = Language::where('code', 'fa')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.languages.toggle', $fa))
            ->assertRedirect();

        // هنوز فعال است
        $this->assertTrue(Language::where('code', 'fa')->value('is_active'));
    }

    public function test_inactive_language_cannot_become_default(): void
    {
        $en = Language::where('code', 'en')->firstOrFail();
        $en->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $this->actingAs($this->superAdmin())
            ->patch(route('admin.languages.make-default', $en))
            ->assertForbidden();

        $this->assertFalse(Language::where('code', 'en')->value('is_default'));
    }

    public function test_default_language_cannot_be_destroyed(): void
    {
        $fa = Language::where('code', 'fa')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.languages.destroy', $fa))
            ->assertRedirect();

        $this->assertDatabaseHas('languages', ['code' => 'fa']);
    }

    public function test_non_default_language_can_be_destroyed(): void
    {
        Language::create([
            'code' => 'de',
            'name' => ['fa' => 'آلمانی'],
            'native_name' => 'Deutsch',
            'dir' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
        ]);
        app(LocaleService::class)->flush();

        $de = Language::where('code', 'de')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.languages.destroy', $de))
            ->assertRedirect(route('admin.languages.index'));

        $this->assertDatabaseMissing('languages', ['code' => 'de']);
        $this->assertFalse(app(LocaleService::class)->has('de'));
    }

    public function test_sidebar_hides_languages_for_user_without_permission(): void
    {
        // کاربر عادی بدون هیچ نقشی — لاگین ادمین او را راه نمی‌دهد،
        // پس مستقیم دسترسی به index باید 403 باشد (غیر از داشبورد که free است)
        $editor = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $editor->roles()->detach();

        $this->actingAs($editor)
            ->get(route('admin.languages.index'))
            ->assertForbidden();
    }
}
