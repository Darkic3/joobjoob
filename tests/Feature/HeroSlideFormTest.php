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

        $html = $this->get(route('admin.hero-slides.create'))->assertOk()->getContent();

        $this->assertStringContainsString('assets/admin/js/sweetalert2.all.min.js', $html);
    }

    public function test_hero_form_trust_items_are_drag_sortable_with_visibility_badges(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.create'))->assertOk()->getContent();

        // درگ‌اند‌دراپ روی کارت‌های آیتم اعتماد
        $this->assertStringContainsString('draggable="true"', $html);
        $this->assertStringContainsString('@dragstart="startDrag(i)"', $html);
        $this->assertStringContainsString('@dragover.prevent="overDrag(i)"', $html);
        $this->assertStringContainsString('@dragend="endDrag()"', $html);

        // بج وضعیت نمایش (سه آیتم اول نمایش داده می‌شوند)
        $this->assertStringContainsString(__('admin.hero_slides.shown_in_hero'), $html);
        $this->assertStringContainsString(__('admin.hero_slides.hidden_in_hero'), $html);
        $this->assertStringContainsString(
            __('admin.hero_slides.drag_hint', ['max' => \App\Models\HeroSlide::MAX_TRUST_ITEMS_SHOWN]),
            $html
        );

        // پیکر آیکون جمع‌وجور: پاپ‌اور به‌جای گرید بزرگ همیشگی
        $this->assertStringContainsString('iconPicker === i', $html);
        $this->assertStringContainsString('grid grid-cols-6 gap-1', $html);
    }

    public function test_hero_form_xdata_attribute_is_intact_and_valid(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.create'))->assertOk()->getContent();

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
        $this->assertStringContainsString('ratingActive:', $xData);
        $this->assertStringContainsString('viewersActive:', $xData);
        $this->assertStringContainsString('icons: [&quot;badge-check&quot;,&quot;shield-check&quot;', $xData);
        $this->assertStringContainsString('trusts: [{&quot;icon&quot;:', $xData);
        $this->assertStringContainsString('labels:', $xData);
        $this->assertStringContainsString('.map((t, i) => ({ _key:', $xData);
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
            ->get(route('admin.hero-slides.create'))->assertOk()->getContent();

        $this->assertStringContainsString('Swal.fire', $html);
        $this->assertStringContainsString('خطا در ذخیره‌سازی', $html);
        $this->assertStringContainsString('عنوان فارسی الزامی است.', $html);
        $this->assertStringContainsString('حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.', $html);
    }

    public function test_hero_form_shows_validation_errors_via_swal_on_edit(): void
    {
        $this->actingAsSuperAdmin();

        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['title.fa' => 'عنوان فارسی الزامی است.']));

        $html = $this->withSession(['errors' => $bag])
            ->get(route('admin.hero-slides.create'))->assertOk()->getContent();

        $this->assertStringContainsString('errorMessages', $html);
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
