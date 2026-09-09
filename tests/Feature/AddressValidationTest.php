<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\State;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * اعتبارسنجی آدرس سمت سرور (ایجاد + ویرایش).
 *
 * هدف اصلی: هرگز نباید خطای ولیدیشن به ۵۰۰ تبدیل شود — حتی وقتی فرانت
 * required را دور می‌زند (کاربر DevTools را باز می‌کند یا JS از کار می‌افتد).
 * کاربر باید به فرم برگردد، پیام فارسی ببیند و ورودی‌هایش حفظ شود.
 */
class AddressValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    /**
     * استان/شهر تست را با ID صریح می‌سازیم (بدون اتکا به auto-increment سیدرها).
     */
    private function makeStateCity(): array
    {
        $state = State::create(['name' => 'تهران-تست', 'status' => 1]);
        $city = City::create(['name' => 'تهران-تست', 'status' => 1, 'state_id' => $state->id]);

        return [$state, $city];
    }

    private function validPayload(array $overrides = []): array
    {
        [$state, $city] = $this->makeStateCity();

        return array_merge([
            'state_id'            => $state->id,
            'city_id'             => $city->id,
            'address'             => 'خیابان آزادی، کوچه بهار، پلاک ۱۲',
            'receiptor_full_name' => 'علی رضایی',
            'receiptor_mobile'    => '09123456789',
            'postal_code'         => '1234567890',
        ], $overrides);
    }

    private function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    /** حذف کامل فیلد کدپستی (سناریوی گزارش‌شده: حذف required از فرم) → 500 نباید رخ دهد */
    public function test_missing_postal_code_returns_validation_error_not_500(): void
    {
        $user = $this->actingUser();

        $payload = $this->validPayload();
        unset($payload['postal_code']);

        $response = $this->from('/account/addresses')
            ->post('/account/addresses', $payload);

        $response->assertStatus(302); // ریدایرکت back — نه 500
        $response->assertSessionHasErrors('postal_code');
        $response->assertSessionHasInput('address'); // ورودی‌ها حفظ شده‌اند
    }

    /** حذف همهٔ فیلدها → 302 با خطا، نه 500 */
    public function test_empty_payload_returns_validation_errors_not_500(): void
    {
        $this->actingUser();

        $this->from('/account/addresses')
            ->post('/account/addresses', [])
            ->assertStatus(302)
            ->assertSessionHasErrors(['state_id', 'city_id', 'address', 'receiptor_full_name', 'receiptor_mobile', 'postal_code']);
    }

    /** کدپستی با ارقام فارسی → نرمال می‌شود و پذیرفته می‌شود */
    public function test_persian_digit_postal_code_is_normalized_and_accepted(): void
    {
        $user = $this->actingUser();

        $this->from('/account/addresses')
            ->post('/account/addresses', $this->validPayload([
                'postal_code'      => '۱۲۳۴۵۶۷۸۹۰',
                'receiptor_mobile' => '۰۹۱۲۳۴۵۶۷۸۹',
            ]))
            ->assertStatus(302)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'user_id'      => $user->id,
            'postal_code'  => '1234567890',
            'receiptor_mobile' => '09123456789',
        ]);
    }

    /** کدپستی کوتاه‌تر از ۱۰ رقم → خطای فارسی */
    public function test_short_postal_code_is_rejected_with_persian_message(): void
    {
        $this->actingUser();

        $this->from('/account/addresses')
            ->post('/account/addresses', $this->validPayload(['postal_code' => '12345']))
            ->assertStatus(302)
            ->assertSessionHasErrors('postal_code');

        $this->assertDatabaseCount('addresses', 0);
    }

    /** موبایل نامعتبر (فرمت اشتباه) → رد می‌شود */
    public function test_invalid_mobile_format_is_rejected(): void
    {
        $this->actingUser();

        $this->from('/account/addresses')
            ->post('/account/addresses', $this->validPayload(['receiptor_mobile' => '12345678901']))
            ->assertStatus(302)
            ->assertSessionHasErrors('receiptor_mobile');

        $this->assertDatabaseCount('addresses', 0);
    }

    /** کاربر نمی‌تواند آدرس کاربر دیگر را ویرایش کند */
    public function test_user_cannot_update_someone_elses_address(): void
    {
        $this->actingUser();

        $otherUser = User::factory()->create();
        [$state, $city] = $this->makeStateCity();

        $address = \App\Models\Address::create([
            'user_id' => $otherUser->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'address' => 'آدرس کاربر دیگر',
            'postal_code' => '1234567890',
            'receiptor_full_name' => 'دیگری',
            'receiptor_mobile' => '09120000000',
        ]);

        $this->put('/account/addresses/'.$address->id, $this->validPayload())
            ->assertStatus(404); // findOrFail روی کوئری owner-scoped → 404

        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
            'receiptor_full_name' => 'علی رضایی',
        ]);
    }

    /** ویرایش آدرس خود کاربر با ولیدیشن موفق */
    public function test_user_can_update_own_address(): void
    {
        $user = $this->actingUser();
        [$state, $city] = $this->makeStateCity();

        $address = \App\Models\Address::create([
            'user_id' => $user->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'address' => 'آدرس قدیمی',
            'postal_code' => '1234567890',
            'receiptor_full_name' => 'قدیمی',
            'receiptor_mobile' => '09120000000',
        ]);

        $this->put('/account/addresses/'.$address->id, $this->validPayload([
            'address' => 'آدرس جدید کامل و طولانی',
        ]))
            ->assertStatus(302)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address' => 'آدرس جدید کامل و طولانی',
        ]);
    }
}
