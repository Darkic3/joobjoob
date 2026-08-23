<?php

namespace Tests\Feature;

use App\Helpers\Sms;
use App\Models\User;
use App\Services\OtpService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * کلاس جعلی کاوه‌نگار — به‌جای فراخوانی واقعی API، پاسخ موفق برمی‌گرداند
 * تا بتوان کل جریان OTP را بدون شبکه تست کرد.
 */
class FakeSms extends Sms
{
    public bool $lookupCalled = false;

    public ?string $lastTemplate = null;

    public ?array $lastTokens = null;

    public ?string $lastMobile = null;

    public function sendLookup(string $template, array $tokens, string $mobile, string $type = 'sms')
    {
        $this->lookupCalled = true;
        $this->lastTemplate = $template;
        $this->lastTokens = $tokens;
        $this->lastMobile = $mobile;

        return json_encode([
            'return' => ['status' => 200, 'message' => 'تایید شد'],
            'entries' => [['messageid' => 1, 'receptor' => $mobile]],
        ]);
    }
}

/**
 * ورود/ثبت‌نام با کد یکبارمصرف (OTP) از طریق پیامک کاوه‌نگار.
 */
class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function fakeSms(): FakeSms
    {
        $fake = new FakeSms;
        $this->app->instance(Sms::class, $fake);

        return $fake;
    }

    public function test_otp_page_renders_mobile_step_for_guest(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(__('messages.otp_login'))
            ->assertSee('name="mobile"', false)
            ->assertDontSee('name="code"', false);
    }

    public function test_logged_in_user_visiting_login_page_is_redirected_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_send_otp_existing_user_redirects_to_verify(): void
    {
        $fake = $this->fakeSms();
        $user = User::factory()->create(['mobile' => '09123456789']);

        $this->post('/login/send', ['mobile' => '۰۹۱۲۳۴۵۶۷۸۹'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('otp_mobile', '09123456789');

        $this->assertSame(1, User::where('mobile', '09123456789')->count());
        $this->assertSame('09123456789', $fake->lastMobile);
        $this->assertSame($user->id, User::where('mobile', '09123456789')->value('id'));

        $this->assertTrue($fake->lookupCalled);
        $this->assertSame('verify', $fake->lastTemplate);
        $this->assertSame($user->fresh()->confirm_code, $fake->lastTokens['token']);
        // سرویس OTP نام سایت رو پاکسازی می‌کنه: اگر با «فروشگاه» شروع شود کلمه‌ی دوم ارسال می‌شود
        $rawName = setting('site_name', config('app.name', 'Shopora'));
        $parts = preg_split('/\s+/', trim($rawName));
        $expectedToken2 = (count($parts) >= 2 && $parts[0] === 'فروشگاه') ? $parts[1] : $parts[0];
        $this->assertSame($expectedToken2, $fake->lastTokens['token2']);
    }

    public function test_send_otp_new_user_with_name_creates_user_and_sends_code(): void
    {
        $fake = $this->fakeSms();

        $this->post('/login/send', [
            'mobile' => '09123456789',
            'name' => 'علی رضایی',
        ])->assertRedirect(route('login.verify'))
            ->assertSessionHas('otp_mobile', '09123456789');

        $user = User::where('mobile', '09123456789')->first();
        $this->assertNotNull($user);
        $this->assertSame('علی رضایی', $user->name);
        $this->assertMatchesRegularExpression('/^\d{4}$/', (string) $user->confirm_code);
        $this->assertNotNull($user->confirm_code_expires_at);

        $this->assertTrue($fake->lookupCalled);
        $this->assertSame('verify', $fake->lastTemplate);
        $this->assertSame('09123456789', $fake->lastMobile);
    }

    public function test_send_otp_new_user_without_name_shows_error_and_redirects_back(): void
    {
        $this->post('/login/send', ['mobile' => '09123456789'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('users', ['mobile' => '09123456789']);
    }

    public function test_send_otp_rejects_invalid_mobile(): void
    {
        $this->fakeSms();

        $this->from('/login')->post('/login/send', ['mobile' => '12345'])
            ->assertSessionHasErrors('mobile');

        $this->assertDatabaseMissing('users', ['mobile' => '12345']);
    }

    public function test_send_otp_without_configured_key_redirects_to_verify_with_error(): void
    {
        // بدون جایگزینی Sms — کلید API در settings خالی است → خطای پیکربندی
        // کاربر وجود دارد → کد ساخته می‌شود ولی SMS ارسال نمی‌شود
        $user = User::factory()->create(['mobile' => '09123456789']);

        $this->post('/login/send', ['mobile' => '09123456789'])
            ->assertRedirect(route('login.verify'))
            ->assertSessionHas('error');
    }

    public function test_verify_otp_logs_in_user_and_consumes_code(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'confirm_code' => '1234',
            'confirm_code_expires_at' => now()->addMinutes(2),
        ]);

        $this->withSession(['otp_mobile' => '09123456789'])
            ->post('/login/otp/verify', ['code' => '1234'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['mobile' => '09123456789', 'confirm_code' => null]);
    }

    public function test_verify_otp_with_wrong_code_fails(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'confirm_code' => '1234',
            'confirm_code_expires_at' => now()->addMinutes(2),
        ]);

        $this->withSession(['otp_mobile' => '09123456789'])
            ->post('/login/otp/verify', ['code' => '9999'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_verify_otp_with_expired_code_fails(): void
    {
        User::factory()->create([
            'mobile' => '09123456789',
            'confirm_code' => '1234',
            'confirm_code_expires_at' => now()->subMinute(),
        ]);

        $this->withSession(['otp_mobile' => '09123456789'])
            ->post('/login/otp/verify', ['code' => '1234'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_verify_without_session_mobile_redirects_to_login(): void
    {
        $this->post('/login/otp/verify', ['code' => '1234'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_resend_sends_new_code_to_session_mobile(): void
    {
        $fake = $this->fakeSms();
        $user = User::factory()->create(['mobile' => '09123456789']);

        $this->withSession(['otp_mobile' => '09123456789'])
            ->from('/login/verify')
            ->post('/login/otp/resend')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($fake->lookupCalled);
        $this->assertSame('09123456789', $fake->lastMobile);
        $this->assertNotNull($user->fresh()->confirm_code);
    }

    public function test_mobile_normalization_formats(): void
    {
        $service = app(OtpService::class);

        $this->assertSame('09123456789', $service->normalizeMobile('۰۹۱۲۳۴۵۶۷۸۹'));
        $this->assertSame('09123456789', $service->normalizeMobile('+989123456789'));
        $this->assertSame('09123456789', $service->normalizeMobile('00989123456789'));
        $this->assertSame('09123456789', $service->normalizeMobile('9123456789'));
        $this->assertSame('09123456789', $service->normalizeMobile('0912-345-6789'));
    }
}
