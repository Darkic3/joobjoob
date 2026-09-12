<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoLocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * روت‌های زیرساختی که عمداً دوقلوی locale.* ندارند — افزودن هر مورد جدید باید آگاهانه باشد.
     *
     * sitemap.* → محتوای غیرصفحه‌ای؛ alternates را خودش با xhtml:link اعلام می‌کند
     * robots    → خروجی متنی برای خزنده‌ها، نه صفحه
     * secret.*  → روت‌های عملیاتی استقرار، نه محتوای عمومی
     */
    private const INFRASTRUCTURE_ROUTES_WITHOUT_LOCALE_TWIN = [
        'sitemap',
        'robots',
        'secret.migrate',
        'secret.seed',
        'secret.clear-cache',
        'secret.fresh',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    private function homeHtml(string $locale = ''): string
    {
        return $this->get($locale ? "/{$locale}" : '/')->assertOk()->getContent();
    }

    public function test_home_without_prefix_serves_default_locale(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<html\s+lang="fa"/', $html);
        $this->assertMatchesRegularExpression('/<html[^>]*dir="rtl"/', $html);
    }

    public function test_locale_prefix_serves_that_language(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<html\s+lang="en"/', $html);
        $this->assertMatchesRegularExpression('/<html[^>]*dir="ltr"/', $html);
    }

    public function test_default_locale_prefix_redirects_to_canonical(): void
    {
        // /fa → 301 به / (پیش‌فرض پیشوند نمی‌گیرد)
        $this->get('/fa')->assertRedirect('/');
        $this->get('/fa/products')->assertRedirect('/products');
    }

    public function test_inactive_locale_prefix_redirects_to_canonical(): void
    {
        Language::where('code', 'en')->update(['is_active' => false]);
        app(LocaleService::class)->flush();

        // en در دیتابیس هست ولی غیرفعال است → روت match می‌شود و 301 به پیش‌فرض
        $this->get('/en')->assertRedirect('/');
        $this->get('/en/products')->assertRedirect('/products');
    }

    public function test_unknown_locale_prefix_returns_404(): void
    {
        // کدی که نه در دیتابیس است نه کاتالوگ → مسیر نامعتبر
        $this->get('/xx')->assertNotFound();
    }

    public function test_products_page_works_with_prefix(): void
    {
        $this->get('/en/products')->assertOk();

        $html = $this->get('/en/products')->getContent();
        $this->assertMatchesRegularExpression('/<html\s+lang="en"/', $html);
    }

    public function test_hreflang_tags_present_on_home(): void
    {
        $html = $this->homeHtml();

        // همه زبان‌های فعال + x-default
        foreach (['fa', 'en', 'ar', 'tr'] as $code) {
            $this->assertStringContainsString("hreflang=\"{$code}\"", $html);
        }

        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_hreflang_urls_point_to_prefixed_versions(): void
    {
        // در صفحهٔ پیشونددار، hreflang هر زبان باید به همان پیشوند اشاره کند
        $html = $this->homeHtml('en');

        $this->assertStringContainsString('href="http://localhost/en"', $html);
        $this->assertStringContainsString('href="http://localhost/tr"', $html);
        $this->assertStringContainsString('href="http://localhost"', $html); // fa پیش‌فرض → بدون پیشوند
    }

    public function test_canonical_tag_present(): void
    {
        $this->assertStringContainsString('rel="canonical"', $this->homeHtml());
        $this->assertStringContainsString('href="http://localhost/en"', $this->homeHtml('en'));
    }

    public function test_og_locale_tags_present(): void
    {
        $html = $this->homeHtml();

        $this->assertStringContainsString('property="og:locale" content="fa_IR"', $html);
        $this->assertStringContainsString('property="og:locale:alternate"', $html);
    }

    public function test_og_locale_follows_current_language(): void
    {
        $html = $this->homeHtml('en');

        $this->assertStringContainsString('property="og:locale" content="en_US"', $html);
    }

    public function test_localized_route_helper(): void
    {
        $this->assertSame('http://localhost', localizedRoute('home'));
        $this->assertSame('http://localhost/en', localizedRoute('home', [], 'en'));
        $this->assertSame('http://localhost/products', localizedRoute('products.index'));
        $this->assertSame('http://localhost/en/products', localizedRoute('products.index', [], 'en'));
    }

    public function test_localized_url_helper_uses_current_route(): void
    {
        $this->get('/en/products');

        // در صفحهٔ /en/products، localizedUrl('fa') باید به /products برود (پیش‌فرض بدون پیشوند)
        $this->assertSame('http://localhost/products', localizedUrl('fa'));
        $this->assertSame('http://localhost/en/products', localizedUrl('en'));
        $this->assertSame('http://localhost/tr/products', localizedUrl('tr'));
    }

    public function test_redirect_preserves_query_string(): void
    {
        // پیش‌فرضِ پیشونددار → بدون پیشوند + حفظ کوئری (ترتیب کلیدها مهم نیست)
        $r = $this->get('/fa/products?sort=price&page=2');
        $r->assertRedirect();
        $location = (string) $r->headers->get('Location');
        $this->assertStringContainsString('http://localhost/products', $location);
        $this->assertStringContainsString('sort=price', $location);
        $this->assertStringContainsString('page=2', $location);

        // زبان غیرفعال → پیش‌فرض + حفظ کوئری
        Language::where('code', 'en')->update(['is_active' => false]);
        app(LocaleService::class)->flush();
        $this->get('/en/products?q=x')->assertRedirect('/products?q=x');
    }

    public function test_legacy_lang_param_redirects_to_prefixed_url(): void
    {
        $this->get('/?lang=en')->assertRedirect('/en');
        $this->get('/products?lang=tr&sort=price')->assertRedirect('/tr/products?sort=price');
    }

    public function test_hreflang_exact_count_and_x_default(): void
    {
        $html = $this->homeHtml('en');

        // ۴ زبان فعال + x-default = ۵ لینک alternate
        $this->assertSame(5, substr_count($html, 'rel="alternate" hreflang="'));
        $this->assertStringContainsString('hreflang="x-default" href="http://localhost"', $html);

        // در صفحه‌ی عربی هم x-default به پیش‌فرضِ بدون پیشوند اشاره می‌کند
        $this->assertStringContainsString('hreflang="x-default" href="http://localhost"', $this->homeHtml('ar'));
    }

    public function test_every_site_route_has_a_locale_twin(): void
    {
        $names = collect(app('router')->getRoutes()->getRoutesByName())->keys();

        // هر روت سایت (غیر admin و غیر locale.*) باید دوقلوی locale.* داشته باشد
        $missing = $names
            ->reject(fn (string $n) => str_starts_with($n, 'admin.'))
            ->reject(fn (string $n) => str_starts_with($n, 'locale.'))
            ->reject(fn (string $n) => str_starts_with($n, 'ignition.'))
            ->reject(fn (string $n) => str_starts_with($n, 'sitemap.'))
            ->reject(fn (string $n) => in_array($n, self::INFRASTRUCTURE_ROUTES_WITHOUT_LOCALE_TWIN, true))
            ->reject(fn (string $n) => $names->contains('locale.'.$n))
            ->values();

        $this->assertSame([], $missing->all(), 'روت سایت بدون دوقلوی locale.*: '.implode(', ', $missing->all()));

        // برعکس — هر روت locale.* باید نسخه‌ی اصلی داشته باشد (جلوگیری از غلط املایی)
        $orphans = $names
            ->filter(fn (string $n) => str_starts_with($n, 'locale.'))
            ->reject(fn (string $n) => $names->contains(substr($n, strlen('locale.'))))
            ->values();

        $this->assertSame([], $orphans->all(), 'روت locale.* بدون نسخه‌ی اصلی: '.implode(', ', $orphans->all()));
    }

    /**
     * معافیت روت‌های زیرساختی صریح است، نه فراموشی:
     * هر نام در allowlist باید واقعاً وجود داشته باشد و نباید دوقلوی locale.* گرفته باشد.
     * اگر روزی روت زیرساختی ناخواسته دوقلوی locale بگیرد، این تست آن را می‌گیرد.
     */
    public function test_infrastructure_routes_are_explicitly_exempt_from_locale_twin(): void
    {
        $names = collect(app('router')->getRoutes()->getRoutesByName())->keys();

        foreach (self::INFRASTRUCTURE_ROUTES_WITHOUT_LOCALE_TWIN as $name) {
            $this->assertTrue(
                $names->contains($name),
                "روت زیرساختی «{$name}» وجود ندارد — allowlist این تست باید به‌روز شود."
            );

            $this->assertFalse(
                $names->contains('locale.'.$name),
                "روت زیرساختی «{$name}» نباید دوقلوی locale.* داشته باشد."
            );
        }
    }
}
