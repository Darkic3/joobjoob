<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LocaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // حافظه‌ی استاتیک سرویس بین تست‌ها می‌ماند — هر تست از حالت تمیز شروع کند
        app(LocaleService::class)->flush();
    }

    private function seedLanguages(): void
    {
        $this->seed(LanguageSeeder::class);
    }

    public function test_all_returns_languages_keyed_by_code(): void
    {
        $this->seedLanguages();

        $codes = array_keys(app(LocaleService::class)->all());

        $this->assertSame(['fa', 'en', 'ar', 'tr'], $codes);
    }

    public function test_active_filters_out_inactive_languages(): void
    {
        $this->seedLanguages();
        Language::where('code', 'tr')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $active = app(LocaleService::class)->activeCodes();

        $this->assertSame(['fa', 'en', 'ar'], $active);
        $this->assertFalse(app(LocaleService::class)->isActive('tr'));
        $this->assertTrue(app(LocaleService::class)->isActive('en'));
    }

    public function test_default_returns_active_default_language(): void
    {
        $this->seedLanguages();

        $this->assertSame('fa', app(LocaleService::class)->defaultCode());
        $this->assertSame('fa', app(LocaleService::class)->default()?->code);
    }

    public function test_default_falls_back_to_first_active_when_default_is_inactive(): void
    {
        $this->seedLanguages();

        // داده‌ی خراب: پیش‌فرض غیرفعال → سرویس باید به اولین زبان فعال برسد
        Language::where('code', 'fa')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        $this->assertSame('en', app(LocaleService::class)->defaultCode());
    }

    public function test_dir_comes_from_db_row_then_catalog_then_default(): void
    {
        $this->seedLanguages();

        // از ردیف دیتابیس
        $this->assertSame('rtl', app(LocaleService::class)->dir('fa'));
        $this->assertSame('ltr', app(LocaleService::class)->dir('en'));

        // زبان‌های بدون ردیف → از کاتالوگ، و ناشناخته‌ها → _default (ltr)
        $this->assertSame('ltr', app(LocaleService::class)->dir('ur'));
        $this->assertSame('ltr', app(LocaleService::class)->dir('xx'));

        $this->assertTrue(app(LocaleService::class)->isRtl('fa'));
        $this->assertFalse(app(LocaleService::class)->isRtl('en'));
    }

    public function test_calendar_and_digits_from_catalog(): void
    {
        $this->seedLanguages();

        $this->assertTrue(app(LocaleService::class)->isJalali('fa'));
        $this->assertTrue(app(LocaleService::class)->isJalali('ar'));
        $this->assertFalse(app(LocaleService::class)->isJalali('en'));

        $this->assertSame('persian', app(LocaleService::class)->digitFamily('fa'));
        $this->assertSame('arabic', app(LocaleService::class)->digitFamily('ar'));
        $this->assertSame('latin', app(LocaleService::class)->digitFamily('en'));

        // زبان ناشناخته → _default (gregorian/latin)
        $this->assertFalse(app(LocaleService::class)->isJalali('xx'));
        $this->assertSame('latin', app(LocaleService::class)->digitFamily('xx'));
    }

    public function test_single_language_mode_when_at_most_one_active(): void
    {
        $this->seedLanguages();
        $service = app(LocaleService::class);

        $this->assertFalse($service->singleLanguageMode());

        // همه را غیرفعال کن جز fa
        Language::where('code', '!=', 'fa')->update(['is_active' => false]);
        $service->flush();

        $this->assertTrue($service->singleLanguageMode());
    }

    public function test_flush_clears_cache_and_memory(): void
    {
        $this->seedLanguages();
        $service = app(LocaleService::class);

        $this->assertCount(4, $service->all());
        $this->assertTrue(Cache::has('shopora_languages_all'));

        // یک زبان جدید اضافه کن و flush کن — باید در all() بعدی دیده شود
        Language::create([
            'code' => 'de',
            'name' => ['fa' => 'آلمانی', 'en' => 'German'],
            'native_name' => 'Deutsch',
            'dir' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
        ]);
        $service->flush();

        $this->assertTrue($service->has('de'));
        $this->assertCount(5, $service->all());
    }

    public function test_localized_name_falls_back_to_fa_then_native(): void
    {
        $this->seedLanguages();
        app()->setLocale('en');

        $fa = Language::where('code', 'fa')->firstOrFail();
        // name['en'] = 'Persian' — موجود است
        $this->assertSame('Persian', $fa->localizedName());

        // بدون ترجمه‌ی انگلیسی → fallback به fa → native_name
        Language::where('code', 'fa')->update(['name' => ['fa' => 'فارسی']]);
        $this->assertSame('فارسی', $fa->fresh()->localizedName());
    }
}
