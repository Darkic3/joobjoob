<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetLocaleFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Test multi-lingual behavior: disable public locale lock
        config(['site-features.public_locale_locked' => false]);

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    /**
     * رندر صفحه‌ی اصلی با زبان مشخص شده توسط میدلور.
     */
    private function htmlLangOf(string $path = '/'): string
    {
        $response = $this->get($path);

        $response->assertOk();

        preg_match('/<html\s+lang="([^"]+)"/', $response->getContent(), $m);

        return $m[1] ?? '';
    }

    // ── سایت: پیشوند URL تعیین‌کننده است ──────────────────────────────

    public function test_default_locale_is_fa(): void
    {
        $this->assertSame('fa', $this->htmlLangOf('/'));
        $this->assertSame('fa', $this->htmlLangOf('/products'));
    }

    public function test_locale_prefix_switches_locale(): void
    {
        $this->assertSame('en', $this->htmlLangOf('/en'));
        $this->assertSame('tr', $this->htmlLangOf('/tr'));
        $this->assertSame('ar', $this->htmlLangOf('/ar'));
    }

    public function test_default_prefix_redirects_to_canonical(): void
    {
        $this->get('/fa')->assertRedirect('/');
        $this->get('/fa/products')->assertRedirect('/products');
    }

    public function test_inactive_locale_redirects_to_default(): void
    {
        Language::where('code', 'en')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $this->get('/en')->assertRedirect('/');
        $this->get('/en/products')->assertRedirect('/products');
    }

    public function test_single_language_mode_redirects_all_prefixes_to_default(): void
    {
        // فقط fa فعال است
        Language::where('code', '!=', 'fa')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        // en روت دارد (در دیتابیس هست) ولی غیرفعال است → 301 به پیش‌فرض
        $this->get('/en')->assertRedirect('/');
    }

    public function test_unprefixed_site_lang_param_redirects_to_prefixed_url(): void
    {
        // سازگاری با عقب: بوکمارک قدیمی ?lang= → 301 به نسخه‌ی پیشونددار (SEO)
        $this->get('/?lang=en')->assertRedirect('/en');
        $this->get('/products?lang=tr')->assertRedirect('/tr/products');
        $this->actingAs(User::factory()->create());
        $this->get('/cart?lang=ar')->assertRedirect('/ar/cart');

        // نکته: در صفحه‌های دارای مدل-بایندینگ (products/{slug})، اگر رکورد وجود
        // نداشته باشد بایندینگ (قبل از میدلور) 404 می‌دهد — همان رفتار درست است.
    }

    public function test_unprefixed_site_lang_param_of_default_or_invalid_is_ignored(): void
    {
        // پیش‌فرض — نیازی به ریدایرکت نیست (همین canonical است)
        $this->assertSame('fa', $this->htmlLangOf('/?lang=fa'));

        // کد ناموجود → نادیده → پیش‌فرض
        $this->assertSame('fa', $this->htmlLangOf('/?lang=xx'));
    }

    public function test_unprefixed_site_lang_param_of_inactive_locale_is_ignored(): void
    {
        Language::where('code', 'en')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        // en غیرفعال → نادیده → پیش‌فرض (بدون ریدایرکت)
        $this->assertSame('fa', $this->htmlLangOf('/?lang=en'));
    }

    public function test_unprefixed_site_ignores_session_and_user_locale(): void
    {
        // مسیر بدون پیشوند = canonical پیش‌فرض — سشن/کاربر آن را عوض نمی‌کند
        $this->withSession(['locale' => 'tr']);
        $this->assertSame('fa', $this->htmlLangOf('/'));

        $user = User::factory()->create(['is_admin' => false, 'is_active' => true, 'locale' => 'ar']);
        $this->actingAs($user);
        $this->assertSame('fa', $this->htmlLangOf('/'));
    }

    // ── پنل ادمین: زنجیره‌ی قدیمی حفظ شده است ─────────────────────────

    public function test_admin_login_respects_lang_param(): void
    {
        $html = $this->get('/admin/login?lang=en')->assertOk()->getContent();

        preg_match('/<html\s+lang="([^"]+)"/', $html, $m);

        $this->assertSame('en', $m[1] ?? '');
    }

    public function test_admin_respects_user_locale(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'locale' => 'ar',
        ]);

        $this->actingAs($user);

        $html = $this->get('/admin/dashboard')->assertOk()->getContent();
        preg_match('/<html\s+lang="([^"]+)"/', $html, $m);

        $this->assertSame('ar', $m[1] ?? '');
    }

    public function test_admin_respects_session_locale(): void
    {
        $this->withSession(['locale' => 'tr']);

        $html = $this->get('/admin/login')->assertOk()->getContent();
        preg_match('/<html\s+lang="([^"]+)"/', $html, $m);

        $this->assertSame('tr', $m[1] ?? '');
    }

    public function test_admin_user_with_inactive_locale_falls_back(): void
    {
        Language::where('code', 'ar')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $user = User::factory()->create(['is_admin' => true, 'is_active' => true, 'locale' => 'ar']);
        $this->actingAs($user);

        // ar غیرفعال است → fallback به پیش‌فرض
        $this->assertSame('fa', $this->htmlLangOf('/admin/dashboard'));
    }

    public function test_app_locale_is_set_for_translations(): void
    {
        $this->get('/en');

        $this->assertSame('en', app()->getLocale());
    }
}
