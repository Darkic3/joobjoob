<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * حساب کاربری سمت سایت: لاگین/خروج + گارد داشبورد + آیکون هدر.
 */
class SiteAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_guest_sees_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(__('messages.login_title'))
            ->assertSee(__('messages.login_subtitle'))
            ->assertSee('name="mobile"', false)
            ->assertSee('name="password"', false);
    }

    public function test_logged_in_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'mobile' => '09123456789',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->from('/login')->post('/login', [
            'mobile' => '09123456789',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'mobile' => '09123456789',
            'password' => 'password',
        ])->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_login_with_invalid_mobile_format_shows_validation_error(): void
    {
        $this->from('/login')->post('/login', [
            'mobile' => '09123',
            'password' => 'password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_login_normalizes_persian_digits_and_country_code(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'mobile' => '۰۹۱۲۳۴۵۶۷۸۹',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get('/account')->assertRedirect(route('login'));
        $this->get('/account/orders')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'علی رضایی']);

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee(__('messages.account_info'))
            ->assertSee('علی رضایی');

        $this->actingAs($user)
            ->get('/account/orders')
            ->assertOk()
            ->assertSee(__('messages.my_orders'));
    }

    public function test_logout_logs_out_and_redirects_home(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_login_page_works_in_localized_locale(): void
    {
        $this->get('/en/login')
            ->assertOk()
            ->assertSee(__('messages.login_title', [], 'en'))
            ->assertSee('name="mobile"', false);
    }

    public function test_header_shows_login_button_for_guest_and_full_actions_for_user(): void
    {
        // مهمان → فقط دکمهٔ ورود/ثبت‌نام در هدر (بدون آواتار عضو)
        $this->get('/')
            ->assertOk()
            ->assertSee('site-login-btn', false)
            ->assertSee(__('messages.login_register'))
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('site-account-btn', false);

        // عضو → سبد/علاقه‌مندی/آواتار (لینک پنل کاربری + حرف اول نام)
        $user = User::factory()->create(['name' => 'نگار احمدی']);
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('site-account-btn', false)
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee(mb_substr('نگار احمدی', 0, 1))
            ->assertDontSee('site-login-btn', false);
    }
}
