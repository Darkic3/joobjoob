<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * حساب کاربری سمت سایت: ورود OTP + گارد داشبورد + آیکون هدر.
 */
class SiteAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_guest_sees_otp_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(__('messages.otp_login'))
            ->assertSee('name="mobile"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_login_page_renders_directly(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(__('messages.otp_login'));
    }

    public function test_old_otp_route_redirects_to_login(): void
    {
        $this->get('/login/otp')
            ->assertRedirect(route('login'));
    }

    public function test_logged_in_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_otp_send_existing_user_redirects_to_verify(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->post('/login/send', ['mobile' => '09123456789'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('otp_mobile', '09123456789');
    }

    public function test_otp_send_new_user_without_name_shows_error_and_redirects_back(): void
    {
        $this->post('/login/send', ['mobile' => '09991112222'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('name');
    }

    public function test_otp_send_new_user_with_name_creates_user_and_redirects_to_verify(): void
    {
        $this->post('/login/send', [
            'mobile' => '09991112222',
            'name' => 'علی رضایی',
        ])->assertRedirect(route('login.verify'));

        $this->assertDatabaseHas('users', [
            'mobile' => '09991112222',
            'name' => 'علی رضایی',
            'is_active' => true,
        ]);
    }

    public function test_otp_send_with_invalid_mobile_shows_error(): void
    {
        $this->from('/login')->post('/login/send', ['mobile' => '09123'])
            ->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_otp_send_normalizes_persian_digits(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->post('/login/send', ['mobile' => '۰۹۱۲۳۴۵۶۷۸۹'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('otp_mobile', '09123456789');
    }

    public function test_verify_page_requires_otp_mobile_in_session(): void
    {
        $this->get('/login/verify')
            ->assertRedirect(route('login'));
    }

    public function test_verify_page_shows_for_valid_session(): void
    {
        $this->session(['otp_mobile' => '09123456789']);

        $this->get('/login/verify')
            ->assertOk()
            ->assertSee(__('messages.otp_verify_title'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => false,
        ]);

        $this->post('/login/send', ['mobile' => '09123456789'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('error');

        $this->assertGuest();
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

    public function test_otp_page_works_in_localized_locale(): void
    {
        $this->get('/en/login')
            ->assertOk()
            ->assertSee(__('messages.otp_login', [], 'en'));
    }

    public function test_header_shows_login_button_for_guest_and_full_actions_for_user(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('site-login-btn', false)
            ->assertSee(__('messages.login_register'))
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('site-account-btn', false);

        $user = User::factory()->create(['name' => 'نگار احمدی']);
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('site-account-btn', false)
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee(mb_substr('نگار احمدی', 0, 1))
            ->assertDontSee('site-login-btn', false);
    }

    public function test_check_phone_returns_exists_for_existing_user(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'is_active' => true,
        ]);

        $this->postJson('/login/check-phone', ['mobile' => '09123456789'])
            ->assertOk()
            ->assertJson(['exists' => true, 'valid' => true]);
    }

    public function test_check_phone_returns_not_exists_for_new_user(): void
    {
        $this->postJson('/login/check-phone', ['mobile' => '09991112222'])
            ->assertOk()
            ->assertJson(['exists' => false, 'valid' => true]);
    }

    public function test_check_phone_returns_invalid_for_bad_mobile(): void
    {
        $this->postJson('/login/check-phone', ['mobile' => '09123'])
            ->assertOk()
            ->assertJson(['valid' => false]);
    }
}
