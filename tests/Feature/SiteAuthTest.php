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
            ->assertSee('name="email"', false)
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
            'email' => 'customer@example.com',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'is_active' => true,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.com',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

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

    public function test_login_page_works_in_localized_locale(): void
    {
        $this->get('/en/login')
            ->assertOk()
            ->assertSee(__('messages.login_title', [], 'en'))
            ->assertSee('name="email"', false);
    }

    public function test_header_shows_login_icon_for_guest_and_dashboard_for_user(): void
    {
        // مهمان → لینک لاگین
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee(__('messages.login_subtitle'));

        // عضو → لینک پنل کاربری + حرف اول نام
        $user = User::factory()->create(['name' => 'نگار احمدی']);
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee(mb_substr('نگار احمدی', 0, 1));
    }
}
