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
        $this->get('/login/otp')
            ->assertOk()
            ->assertSee(__('messages.otp_login'))
            ->assertSee('name="mobile"', false)
            ->assertDontSee('name="code"', false);
    }

    public function test_logged_in_user_visiting_otp_page_is_redirected_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login/otp')->assertRedirect(route('dashboard'));
    }

    public function test_send_otp_creates_user_stores_code_and_calls_kavenegar(): void
    {
        $fake = $this->fakeSms();

        $this->post('/login/otp/send', ['mobile' => '09123456789'])
            ->assertRedirect(route('login.otp'))
            ->assertSessionHas('otp_mobile', '09123456789');

        $user = User::where('mobile', '09123456789')->first();
        $this->assertNotNull($user);
        $this->assertMatchesRegularExpression('/^\d{4}$/', (string) $user->confirm_code);
        $this->assertNotNull($user->confirm_code_expires_at);

        $this->assertTrue($fake->lookupCalled);
        $this->assertSame('verify', $fake->lastTemplate);
        $this->assertSame($user->confirm_code, $fake->lastTokens['token']);
        $this->assertSame('09123456789', $fake->lastMobile);
    }

    public function test_send_otp_normalizes_mobile_and_uses_existing_user(): void
    {
        $fake = $this->fakeSms();
        $user = User::factory()->create(['mobile' => '09123456789']);

        $this->post('/login/otp/send', ['mobile' => '۰۹۱۲۳۴۵۶۷۸۹'])
            ->assertRedirect(route('login.otp'));

        $this->assertSame(1, User::where('mobile', '09123456789')->count());
        $this->assertSame('09123456789', $fake->lastMobile);
        $this->assertSame($user->id, User::where('mobile', '09123456789')->value('id'));
    }

    public function test_send_otp_rejects_invalid_mobile(): void
    {
        $this->fakeSms();

        $this->post('/login/otp/send', ['mobile' => '12345'])
            ->assertSessionHasErrors('mobile');

        $this->assertDatabaseMissing('users', ['mobile' => '12345']);
    }

    public function test_send_otp_without_configured_key_reports_error(): void
    {
        // بدون جایگزینی Sms — کلید API در settings خالی است → خطای پیکربندی
        $this->from('/login/otp')
            ->post('/login/otp/send', ['mobile' => '09123456789'])
            ->assertRedirect('/login/otp')
            ->assertSessionHasErrors('mobile');

        // کاربر و کد ذخیره می‌شوند تا بعد از تنظیم کلید در پنل قابل استفاده باشند
        $this->assertDatabaseHas('users', ['mobile' => '09123456789']);
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

    public function test_verify_without_session_mobile_redirects_to_otp_page(): void
    {
        $this->post('/login/otp/verify', ['code' => '1234'])
            ->assertRedirect(route('login.otp'))
            ->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_resend_sends_new_code_to_session_mobile(): void
    {
        $fake = $this->fakeSms();
        $user = User::factory()->create(['mobile' => '09123456789']);

        $this->withSession(['otp_mobile' => '09123456789'])
            ->from('/login/otp')
            ->post('/login/otp/resend')
            ->assertRedirect('/login/otp')
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
