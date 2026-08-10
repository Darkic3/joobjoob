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
    }

    public function test_brand_form_has_global_cropper_for_raw_file_input(): void
    {
        $this->actingAsSuperAdmin();

        $html = $this->get(route('admin.brands.create'))->assertOk()->getContent();

        $this->assertStringContainsString('window.ImageCropper', $html);
        $this->assertStringContainsString('name="image"', $html);
    }
}
