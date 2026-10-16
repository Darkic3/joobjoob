<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * تضمین: پیام‌های فلش، swal و اعتبارسنجی ادمین در هر ۴ زبان ترجمه می‌شوند
 * (هیچ کلید خام یا فارسی هاردکد باقی نمی‌ماند).
 */
class AdminMessagesLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_admin_flash_keys_resolve_in_all_languages(): void
    {
        $keys = [
            'admin.blogs.created', 'admin.blogs.updated', 'admin.blogs.deleted',
            'admin.products.created', 'admin.products.updated', 'admin.products.deleted',
            'admin.users.created', 'admin.users.updated', 'admin.users.last_super_admin_error',
            'admin.settings.saved', 'admin.roles.created', 'admin.roles.deleted',
            'admin.trust_badges.created', 'admin.brands.updated', 'admin.specifications.deleted',
            'admin.hero_slides.updated', 'admin.blog_categories.has_blogs_error',
            'admin.blog_categories.self_parent_error', 'admin.product_categories.has_products_error',
            'admin.product_categories.has_children_error',
        ];

        foreach (['fa', 'en', 'ar', 'tr'] as $locale) {
            app()->setLocale($locale);

            foreach ($keys as $key) {
                $value = __($key);

                $this->assertNotSame($key, $value, "[{$locale}] کلید خام برگشت داده شد: {$key}");
                $this->assertNotEmpty($value, "[{$locale}] مقدار خالی برای: {$key}");
            }
        }
    }

    public function test_swal_common_keys_resolve_in_all_languages(): void
    {
        $keys = [
            'admin.common.delete_confirm_title', 'admin.common.delete_confirm_text',
            'admin.common.delete_confirm_button', 'admin.common.delete_yes',
            'admin.common.deleted', 'admin.common.error', 'admin.common.delete_failed',
            'admin.common.system_item_protected', 'admin.common.order_saved',
            'admin.common.session_expired', 'admin.common.unauthorized',
            'admin.common.forbidden', 'admin.common.internal_server_error',
            'admin.common.no_connection', 'admin.common.request_error',
            'admin.common.security_token_missing', 'admin.common.invalid_id',
            'admin.common.read_image_error', 'admin.common.confirm_continue',
            'admin.common.index_confirm_text', 'admin.common.delete_faq_title',
            'admin.common.delete_faq_text', 'admin.common.upload_failed',
            'admin.common.invalid_file', 'admin.common.crop_cancelled',
            'admin.common.discount_delete_confirm', 'admin.common.bulk_resolve_confirm',
            'admin.common.delete_resolved_confirm', 'admin.common.status_change_error',
            'admin.common.server_communication_error', 'admin.common.reply_send_error',
        ];

        foreach (['fa', 'en', 'ar', 'tr'] as $locale) {
            app()->setLocale($locale);

            foreach ($keys as $key) {
                $value = __($key);

                $this->assertNotSame($key, $value, "[{$locale}] کلید خام برگشت داده شد: {$key}");
                $this->assertNotEmpty($value, "[{$locale}] مقدار خالی برای: {$key}");
            }
        }
    }

    public function test_swal_ui_shared_keys_resolve_in_all_languages(): void
    {
        // رشته‌های مشترک دیالوگ‌ها (window.SwalUI_I18N) باید در هر ۴ زبان کامل باشند
        $keys = [
            'confirm_title', 'confirm_text', 'confirm_button',
            'delete_title', 'delete_text', 'delete_button',
            'delete_many_title', 'delete_many_text',
            'cancel', 'ok', 'close',
            'success_title', 'error_title', 'warning_title', 'info_title',
        ];

        foreach (['fa', 'en', 'ar', 'tr'] as $locale) {
            app()->setLocale($locale);

            $strings = trans('swal');

            $this->assertIsArray($strings, "[{$locale}] فایل swal.php بارگذاری نشد.");

            foreach ($keys as $key) {
                $value = $strings[$key] ?? null;

                $this->assertIsString($value, "[{$locale}] کلید swal.{$key} وجود ندارد.");
                $this->assertNotSame("swal.{$key}", $value, "[{$locale}] کلید خام برگشت داده شد: swal.{$key}");
                $this->assertNotEmpty($value, "[{$locale}] مقدار خالی برای: swal.{$key}");
            }
        }
    }

    public function test_bulk_product_action_keys_resolve_in_all_languages(): void
    {
        $keys = [
            'admin.products.delete_named',
            'admin.products.delete_selected_confirm',
            'admin.products.delete_selected_confirm_text',
            'admin.products.bulk_delete_button',
            'admin.products.deleted_count',
        ];

        foreach (['fa', 'en', 'ar', 'tr'] as $locale) {
            app()->setLocale($locale);

            foreach ($keys as $key) {
                $value = __($key, ['count' => 3, 'name' => 'X']);

                $this->assertNotSame($key, $value, "[{$locale}] کلید خام برگشت داده شد: {$key}");
                $this->assertNotEmpty($value, "[{$locale}] مقدار خالی برای: {$key}");
                $this->assertStringNotContainsString(':count', $value, "[{$locale}] جایگزینی :count انجام نشد: {$key}");
            }
        }
    }

    public function test_request_messages_localize_per_language(): void
    {
        $expectations = [
            'fa' => ['blog' => 'فیلد عنوان الزامی است.', 'product' => 'نام فارسی محصول الزامی است.'],
            'en' => ['blog' => 'The title field is required.', 'product' => 'The Persian product name is required.'],
            'ar' => ['blog' => 'حقل العنوان مطلوب.', 'product' => 'الاسم الفارسي للمنتج مطلوب.'],
            'tr' => ['blog' => 'Başlık alanı zorunludur.', 'product' => 'Farsça ürün adı zorunludur.'],
        ];

        foreach ($expectations as $locale => $checks) {
            app()->setLocale($locale);

            $this->assertSame($checks['blog'], __('validation.requests.blog.title_required'));
            $this->assertSame($checks['product'], __('validation.requests.product.name_fa_required'));
        }
    }

    public function test_validation_error_is_localized(): void
    {
        // انگلیسی → پیام عمومی + نام attribute ترجمه‌شده
        app()->setLocale('en');
        $validator = Validator::make(['title' => ''], ['title' => 'required|string']);
        $this->assertTrue($validator->fails());
        $this->assertSame('The title field is required.', $validator->errors()->first('title'));

        // فارسی → «فیلد عنوان الزامی است.»
        app()->setLocale('fa');
        $validator = Validator::make(['title' => ''], ['title' => 'required|string']);
        $this->assertTrue($validator->fails());
        $this->assertSame('فیلد عنوان الزامی است.', $validator->errors()->first('title'));

        // هیچ‌وقت کلید خام نباید برگردد
        $this->assertStringNotContainsString('validation.', $validator->errors()->first('title'));
    }
}
