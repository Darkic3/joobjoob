<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\HeroSlideRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class HeroSlideFormTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    }

    public function test_sweetalert2_is_loaded_globally_in_admin(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.index'))->assertOk()->getContent();

        $this->assertStringContainsString('assets/admin/js/sweetalert2.all.min.js', $html);
    }

    public function test_hero_editor_is_single_form_without_slider_chrome(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.index'))->assertOk()->getContent();

        // متن‌ها، دکمه/لینک و تصویر — همه در یک فرم PUT
        $this->assertStringContainsString('name="title[fa]"', $html);
        $this->assertStringContainsString('name="badge[fa]"', $html);
        $this->assertStringContainsString('name="link_value"', $html);
        $this->assertStringContainsString('name="image"', $html);
        $this->assertStringContainsString('action="' . route('admin.hero-slides.update') . '"', $html);

        // فیچرهای حذف‌شدهٔ اسلایدر: قیمت/امتیاز/بازدید زنده/آیتم اعتماد
        $this->assertStringNotContainsString('name="price"', $html);
        $this->assertStringNotContainsString('name="rating"', $html);
        $this->assertStringNotContainsString('name="viewers"', $html);
        $this->assertStringNotContainsString('name="trusts', $html);
    }

    public function test_hero_form_xdata_attribute_is_intact_and_valid(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.index'))->assertOk()->getContent();

        // رگرسیون: اگر داخل x-data کوتیشن دوبل خام بیفتد (مثل @json یا کامنت حاوی :key="i")
        // attribute زودتر از موعد بسته می‌شود و کل اسکوپ Alpine از کار می‌افتد.
        // این‌جا مثل مرورگر عمل می‌کنیم: مقدار attribute = متن بین x-data=" تا اولین " خام بعدی.
        $open = strpos($html, "x-data=\"{\n    buttonActive:");
        $this->assertNotFalse($open, 'x-data attribute must start with the Alpine object');

        $valueStart = $open + strlen('x-data="');
        $close = strpos($html, '"', $valueStart); // اولین کوتیشن دوبل خام = پایان attribute
        $xData = substr($html, $valueStart, $close - $valueStart);

        // کل آبجکت باید داخل attribute باشد — اگر جایی کوتیشن خام بیفتد، این‌جا قطع می‌شود
        $this->assertStringEndsWith('}', $xData);
        $this->assertStringContainsString('buttonActive:', $xData);
        $this->assertStringContainsString('linkType:', $xData);
        $this->assertStringContainsString('selectedModel:', $xData);
        $this->assertStringContainsString('async searchModels()', $xData);
        // فیلدهای حذف‌شده نباید به x-data برگردند
        $this->assertStringNotContainsString('ratingActive:', $xData);
        $this->assertStringNotContainsString('viewersActive:', $xData);
        $this->assertStringNotContainsString('trusts:', $xData);
    }

    public function test_hero_form_shows_validation_errors_via_swal(): void
    {
        $this->actingAsSuperAdmin();

        // شبیه‌سازی ریدایرکت‌بک با خطاهای اعتبارسنجی — همان حالتی که بعد از سابمیت ناموفق رخ می‌دهد
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag([
            'title.fa' => 'عنوان فارسی الزامی است.',
            'image' => 'حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
        ]));

        $html = $this->withSession(['errors' => $bag])
            ->get(route('admin.hero-slides.index'))->assertOk()->getContent();

        $this->assertStringContainsString('SwalUI.errors', $html);
        $this->assertStringContainsString('sweetalert2', $html);
        $this->assertStringContainsString('خطا در ذخیره‌سازی', $html);
        $this->assertStringContainsString('عنوان فارسی الزامی است.', $html);
        $this->assertStringContainsString('حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.', $html);
    }

    public function test_hero_image_rule_allows_up_to_20mb(): void
    {
        $rules = (new HeroSlideRequest)->rules();

        $this->assertSame('nullable|image|mimes:jpg,jpeg,png,webp|max:20480', $rules['image']);
    }

    public function test_hero_image_validation_messages_are_persian(): void
    {
        $messages = (new HeroSlideRequest)->messages();

        $this->assertSame('حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.', $messages['image.max']);
        $this->assertSame('فرمت تصویر مجاز نیست (فقط jpg, jpeg, png, webp).', $messages['image.mimes']);
    }
}
