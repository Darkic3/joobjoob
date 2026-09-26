<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImageCropperTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    }

    /**
     * کراپر تصویر باید در همهٔ صفحات ادمین در دسترس باشد (از لایهٔ سراسری).
     * اگر <x-admin.image-cropper /> از لایه حذف شود، این تست‌ها شکست می‌خورند
     * و آپلود تصویر در هیچ فرمی کراپ نخواهد شد.
     */
    public function test_settings_page_has_global_cropper(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.settings.edit', 'general'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);
    }

    public function test_product_category_form_has_cropper_and_self_crop_upload(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.product-categories.create'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);

        // کامپوننت image-upload کراپ داخلی دارد و نباید دوباره توسط هندلر سراسری کراپ شود
        $this->assertStringContainsString('data-no-crop', $html);

        // نسبت و ابعاد خروجی کراپ دسته‌بندی: مربع ۱:۱ @ ۸۰۰x۸۰۰
        $this->assertStringContainsString('cropAspectRatio: 1', $html);
        $this->assertStringContainsString('cropWidth: 800', $html);
        $this->assertStringContainsString('cropHeight: 800', $html);
    }

    public function test_brand_form_has_global_cropper_for_raw_file_input(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.brands.create'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);
        // برند حالا از کامپوننت image-upload با دراپ‌زون و پیش‌نمایش کراپ استفاده می‌کند
        $this->assertStringContainsString('dropzone-area', $html);
        $this->assertStringContainsString('name="image"', $html);

        // نسبت و ابعاد خروجی کراپ لوگوی برند: مربع ۱:۱ @ ۸۰۰x۸۰۰
        $this->assertStringContainsString('cropAspectRatio: 1', $html);
        $this->assertStringContainsString('cropWidth: 800', $html);
        $this->assertStringContainsString('cropHeight: 800', $html);
    }

    public function test_hero_slide_form_uses_image_upload_with_preview(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.hero-slides.index'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);
        $this->assertStringContainsString('dropzone-area', $html);

        // کراپ هیرو آزاد است — بدون نسبت/ابعاد اجباری (null یعنی سایز طبیعی کادر کراپ)
        $this->assertStringContainsString('cropAspectRatio: null', $html);
        $this->assertStringContainsString('cropWidth: null', $html);
        $this->assertStringContainsString('cropHeight: null', $html);

        // محدودیت حجم سمت کلاینت: ۲۰ مگابایت
        $this->assertStringContainsString('maxSize: 20971520', $html);
    }

    public function test_blog_form_uses_image_upload_with_preview(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.blogs.create'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);
        $this->assertStringContainsString('dropzone-area', $html);

        // نسبت و ابعاد خروجی کراپ تصویر شاخص بلاگ: ۱۶:۹ @ ۱۶۰۰x۹۰۰
        $this->assertStringContainsString('cropAspectRatio: 1.77777777777778', $html);
        $this->assertStringContainsString('cropWidth: 1600', $html);
        $this->assertStringContainsString('cropHeight: 900', $html);
    }
}
