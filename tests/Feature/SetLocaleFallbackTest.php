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

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    /**
     * رندر صفحه‌ی اصلی با زبان مشخص شده توسط میدلور.
     */
    private function htmlLangOf(array $params = []): string
    {
        $response = $this->get(route('home', $params));

        $response->assertOk();

        preg_match('/<html\s+lang="([^"]+)"/', $response->getContent(), $m);

        return $m[1] ?? '';
    }

    public function test_default_locale_is_fa(): void
    {
        $this->assertSame('fa', $this->htmlLangOf());
    }

    public function test_lang_param_switches_locale(): void
    {
        $this->assertSame('en', $this->htmlLangOf(['lang' => 'en']));
        $this->assertSame('tr', $this->htmlLangOf(['lang' => 'tr']));
    }

    public function test_unknown_lang_falls_back_to_default(): void
    {
        // de در جدول نیست → fallback بی‌صدا به پیش‌فرض (fa)
        $this->assertSame('fa', $this->htmlLangOf(['lang' => 'de']));
    }

    public function test_inactive_lang_falls_back_but_keeps_previous_valid_session(): void
    {
        // اول یک زبان معتبر را در session ذخیره کن
        $this->withSession(['locale' => 'en']);
        $this->assertSame('en', $this->htmlLangOf());

        // حالا en را غیرفعال کن → درخواست بعدی باید fallback کند
        Language::where('code', 'en')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $this->assertSame('fa', $this->htmlLangOf());
    }

    public function test_single_language_mode_ignores_lang_param(): void
    {
        // همه را غیرفعال کن جز fa → حالت تک‌زبانه
        Language::where('code', '!=', 'fa')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        // ?lang=en فعال نیست (غیرفعال شده) و حالت تک‌زبانه است → fa
        $this->assertSame('fa', $this->htmlLangOf(['lang' => 'en']));
    }

    public function test_session_locale_is_used_when_active(): void
    {
        $this->withSession(['locale' => 'tr']);

        $this->assertSame('tr', $this->htmlLangOf());
    }

    public function test_user_locale_wins_over_session(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_active' => true,
            'locale' => 'ar',
        ]);

        // session می‌گوید tr ولی کاربر لاگین‌شده ar دارد → ar برنده است
        $this->actingAs($user)->withSession(['locale' => 'tr']);

        $this->assertSame('ar', $this->htmlLangOf());
    }

    public function test_user_with_inactive_locale_falls_back(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_active' => true,
            'locale' => 'ar',
        ]);

        Language::where('code', 'ar')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $this->actingAs($user);

        // locale کاربر (ar) غیرفعال است → به session/session قبلی → پیش‌فرض fa
        $this->assertSame('fa', $this->htmlLangOf());
    }

    public function test_rtl_direction_for_fa_and_ltr_for_en(): void
    {
        $fa = $this->get(route('home'))->getContent();
        $this->assertMatchesRegularExpression('/<html\s+lang="fa"\s+dir="rtl"/', $fa);

        $en = $this->get(route('home', ['lang' => 'en']))->getContent();
        $this->assertMatchesRegularExpression('/<html\s+lang="en"\s+dir="ltr"/', $en);
    }

    public function test_app_locale_is_set_for_translations(): void
    {
        $this->get(route('home', ['lang' => 'en']));

        $this->assertSame('en', app()->getLocale());
    }
}
