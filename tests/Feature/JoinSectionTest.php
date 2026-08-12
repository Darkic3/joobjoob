<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ستون دوگانه پایانی صفحهٔ اصلی (مسیر C): شبکه‌های اجتماعی + باکس ثبت‌نام.
 */
class JoinSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_guest_sees_registration_form_with_discount_offer(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-register-form', false)
            ->assertSee(__('messages.register_title'))
            ->assertSee(__('messages.register_desc'))
            ->assertSee(__('messages.register_btn'))
            // TODO: فرم هنوز به روت ثبت‌نام کانکت نشده (منطق بعداً پیاده می‌شود)
            ->assertSee('action="#"', false);
    }

    public function test_logged_in_user_sees_member_card_instead_of_form(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-register-form', false)
            ->assertSee(__('messages.already_member_title'));
    }

    public function test_socials_column_renders_from_settings(): void
    {
        app(SettingService::class)->updateGroup('socials', [
            'social_instagram' => 'https://instagram.com/shopora',
            'social_telegram' => 'https://t.me/shopora',
        ], []);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://instagram.com/shopora', false)
            ->assertSee('https://t.me/shopora', false)
            ->assertSee(__('messages.socials.instagram'))
            ->assertSee(__('messages.socials.telegram'));
    }

    public function test_socials_column_hidden_when_empty_and_register_card_takes_full_width(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee(__('messages.join_section_title'))
            // کارت ثبت‌نام تمام‌عرض می‌شود
            ->assertSee('lg:col-span-2', false);
    }
}
