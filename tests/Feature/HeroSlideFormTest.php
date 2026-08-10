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
