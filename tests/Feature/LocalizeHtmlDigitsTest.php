<?php

namespace Tests\Feature;

use App\Helpers\NumberHelper;
use App\Http\Middleware\LocalizeHtmlDigits;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * بومی‌سازی ارقامِ سمت سرور (میدل‌ور LocalizeHtmlDigits).
 *
 * تضمین «قانون طلایی»: رقم‌ها باید از همان لحظهٔ ساختِ متن در خانوادهٔ درست باشند
 * (بدون چشمک انگلیسی→فارسی). برای جزئیات معماری: docs/digit-localization.md
 */
class LocalizeHtmlDigitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    // ─────────────────── NumberHelper::localizeHtmlText ───────────────────

    public function test_converts_visible_text_but_never_attributes_code_or_form_data(): void
    {
        $html = '<div id="t">قیمت: 1,250 تومان — سفارش #456</div>'
            .'<input value="999"><textarea>123</textarea>'
            .'<select><option>1</option></select>'
            .'<script>var n = 42;</script>'
            .'<pre>12 34</pre><code>0x1F</code>'
            .'<span data-digit-raw>789</span>'
            .'<span class="hero-num">2024</span>'
            .'<p>&#65; &amp; 12</p>';

        $converted = NumberHelper::localizeHtmlText($html, 'persian');

        $this->assertStringContainsString('قیمت: ۱٬۲۵۰ تومان — سفارش #۴۵۶', $converted);
        $this->assertStringContainsString('value="999"', $converted);
        $this->assertStringContainsString('<textarea>123</textarea>', $converted);
        $this->assertStringContainsString('<option>1</option>', $converted);
        $this->assertStringContainsString('<script>var n = 42;</script>', $converted);
        $this->assertStringContainsString('<pre>12 34</pre>', $converted);
        $this->assertStringContainsString('<code>0x1F</code>', $converted);
        $this->assertStringContainsString('<span data-digit-raw>789</span>', $converted);
        $this->assertStringContainsString('<span class="hero-num">2024</span>', $converted);
        // موجودیت‌های HTML دست نمی‌خورند، رقمِ خارج از موجودیت تبدیل می‌شود
        $this->assertStringContainsString('<p>&#65; &amp; ۱۲</p>', $converted);
        $this->assertStringNotContainsString('سفارش #456', $converted);
    }

    public function test_arabic_family_uses_arabic_indic_digits(): void
    {
        $converted = NumberHelper::localizeHtmlText('<p>12,5 و 789</p>', 'arabic');

        $this->assertStringContainsString('١٢٬٥ و ٧٨٩', $converted);
    }

    public function test_latin_family_converts_persian_and_arabic_digits_to_latin(): void
    {
        $converted = NumberHelper::localizeHtmlText('<p>۱٬۰۰۰ + ٣٤٥ = ٥۰٪ تخفیف</p>', 'latin');

        $this->assertStringContainsString('1,000 + 345 = 50% تخفیف', $converted);
    }

    public function test_conversion_is_idempotent_per_family(): void
    {
        $once = NumberHelper::localizeHtmlText('<p>تومان 5,000</p>', 'persian');
        $twice = NumberHelper::localizeHtmlText($once, 'persian');

        $this->assertSame($once, $twice);
    }

    public function test_raw_content_with_angle_brackets_is_not_corrupted(): void
    {
        $html = '<p>قیمت: 12</p>'
            .'<script>if (a < b && c > 5) { x = "12"; }</script>'
            .'<p>شماره 34</p>';

        $converted = NumberHelper::localizeHtmlText($html, 'persian');

        $this->assertStringContainsString('قیمت: ۱۲', $converted);
        $this->assertStringContainsString('if (a < b && c > 5) { x = "12"; }', $converted);
        $this->assertStringContainsString('شماره ۳۴', $converted);
    }

    // ─────────────────── میدل‌ور در خط لولهٔ واقعی HTTP ───────────────────

    public function test_html_response_is_localized_through_the_web_pipeline(): void
    {
        \Illuminate\Support\Facades\Route::middleware('web')->get('/_digit-check', function () {
            return response(
                '<div id="t">قیمت: 1,250 تومان — سفارش #456</div>'
                .'<input value="999"><textarea>123</textarea>'
                .'<select><option>1</option></select>'
                .'<script>var n = 42;</script>'
                .'<span data-digit-raw>789</span>'
            );
        });

        $content = $this->get('/_digit-check')->getContent();

        // زبان پیش‌فرض (fa) → ارقام فارسی از همان HTMLِ سمت سرور
        $this->assertStringContainsString('قیمت: ۱٬۲۵۰ تومان — سفارش #۴۵۶', $content);
        // داده‌ها و کدها دست نمی‌خورند
        $this->assertStringContainsString('value="999"', $content);
        $this->assertStringContainsString('<textarea>123</textarea>', $content);
        $this->assertStringContainsString('<option>1</option>', $content);
        $this->assertStringContainsString('<script>var n = 42;</script>', $content);
        $this->assertStringContainsString('<span data-digit-raw>789</span>', $content);
    }

    public function test_json_responses_are_never_converted(): void
    {
        $response = (new LocalizeHtmlDigits)->handle(Request::create('/'), function () {
            return response()->json(['price' => 1234, 'text' => 'تخفیف ۱۰٪']);
        });

        // دقیقاً همان خروجی json_encode — یعنی میدل‌ور هیچ تغییری در JSON نداده
        $this->assertSame(json_encode(['price' => 1234, 'text' => 'تخفیف ۱۰٪']), $response->getContent());
        $this->assertStringNotContainsString('۱۳۴٥', $response->getContent());
    }

    public function test_latin_locale_middleware_converts_to_latin(): void
    {
        app()->setLocale('en');

        $response = (new LocalizeHtmlDigits)->handle(Request::create('/'), function () {
            return response('<p>کد ۱۲۳ و ٥۰٪ — مبلغ ۱٬۰۰۰</p>');
        });

        $this->assertSame('<p>کد 123 و 50% — مبلغ 1,000</p>', $response->getContent());
    }

    public function test_arabic_locale_middleware_converts_to_arabic_indic(): void
    {
        app()->setLocale('ar');

        $response = (new LocalizeHtmlDigits)->handle(Request::create('/'), function () {
            return response('<p>السعر 1,250 ريال</p>');
        });

        $this->assertSame('<p>السعر ١٬٢٥٠ ريال</p>', $response->getContent());
    }
}
