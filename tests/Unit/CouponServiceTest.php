<?php

namespace Tests\Unit;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\User;
use App\Services\CouponService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        $this->service = app(CouponService::class);
    }

    private function createDiscount(array $overrides = []): Discount
    {
        return Discount::create(array_merge([
            'title' => 'Test Discount',
            'code' => 'TEST10',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 1,
            'is_active' => true,
        ], $overrides));
    }

    // ── apply ──

    public function test_apply_invalid_code_returns_error(): void
    {
        $result = $this->service->apply('NONEXISTENT', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('نامعتبر', $result['message']);
    }

    public function test_apply_inactive_code_returns_error(): void
    {
        $this->createDiscount(['code' => 'INACTIVE', 'is_active' => false]);

        $result = $this->service->apply('INACTIVE', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('منقضی', $result['message']);
    }

    public function test_apply_expired_code_returns_error(): void
    {
        $this->createDiscount(['code' => 'EXPIRED', 'expires_at' => now()->subDay()]);

        $result = $this->service->apply('EXPIRED', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('منقضی', $result['message']);
    }

    public function test_apply_future_code_returns_error(): void
    {
        $this->createDiscount(['code' => 'FUTURE', 'starts_at' => now()->addDays(5)]);

        $result = $this->service->apply('FUTURE', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('منقضی', $result['message']);
    }

    public function test_apply_below_minimum_price_returns_error(): void
    {
        $this->createDiscount(['code' => 'MIN100', 'basket_minimum_price' => 200000]);

        $result = $this->service->apply('MIN100', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('حداقل خرید', $result['message']);
    }

    public function test_apply_at_minimum_price_succeeds(): void
    {
        $this->createDiscount(['code' => 'MIN100', 'basket_minimum_price' => 100000]);

        $result = $this->service->apply('MIN100', 100000);

        $this->assertTrue($result['success']);
    }

    public function test_apply_reached_total_limit_returns_error(): void
    {
        $discount = $this->createDiscount(['code' => 'FULL', 'count' => 1]);

        DiscountUsage::create([
            'discount_id' => $discount->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $result = $this->service->apply('FULL', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('ظرفیت', $result['message']);
    }

    public function test_apply_reached_per_user_limit_returns_error(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $discount = $this->createDiscount([
            'code' => 'USERLIMIT',
            'max_usage_per_user' => 1,
            'user_id' => null,
        ]);

        DiscountUsage::create([
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $result = $this->service->apply('USERLIMIT', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('ظرفیت', $result['message']);
    }

    public function test_apply_percent_discount_succeeds_and_stores_session(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'PCT20', 'amount' => 20, 'type' => 'percent']);

        $result = $this->service->apply('PCT20', 100000);

        $this->assertTrue($result['success']);
        $this->assertEquals(20000, $result['discount_amount']);

        $coupon = session('coupon');
        $this->assertNotNull($coupon);
        $this->assertEquals('PCT20', $coupon['code']);
        $this->assertEquals('percent', $coupon['type']);
        $this->assertEquals(20, $coupon['value']);
    }

    public function test_apply_cash_discount_succeeds(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'CASH50K', 'amount' => 50000, 'type' => 'cash']);

        $result = $this->service->apply('CASH50K', 100000);

        $this->assertTrue($result['success']);
        $this->assertEquals(50000, $result['discount_amount']);
    }

    public function test_cash_discount_never_exceeds_subtotal(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'BIGCASH', 'amount' => 200000, 'type' => 'cash']);

        $result = $this->service->apply('BIGCASH', 50000);

        $this->assertTrue($result['success']);
        $this->assertEquals(50000, $result['discount_amount']);
    }

    public function test_apply_case_insensitive_code(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'MYCODE']);

        $result = $this->service->apply('mycode', 100000);

        $this->assertTrue($result['success']);
    }

    public function test_apply_with_whitespace_trimmed(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'TRIMMED']);

        $result = $this->service->apply('  TRIMMED  ', 100000);

        $this->assertTrue($result['success']);
    }

    public function test_apply_first_purchase_code_with_existing_paid_order_returns_error(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $discount = $this->createDiscount([
            'code' => 'FIRSTONLY',
            'first_purchase' => 1,
        ]);

        // Create a paid order for the user
        $user->orders()->create([
            'order_status' => 'paid',
            'total_price' => 100000,
            'payment_price' => 100000,
        ]);

        $result = $this->service->apply('FIRSTONLY', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('خرید اول', $result['message']);
    }

    public function test_apply_first_purchase_code_for_new_user_succeeds(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount([
            'code' => 'FIRSTOK',
            'first_purchase' => 1,
            'type' => 'cash',
            'amount' => 15000,
        ]);

        $result = $this->service->apply('FIRSTOK', 100000);

        $this->assertTrue($result['success']);
        $this->assertEquals(15000, $result['discount_amount']);
    }

    public function test_apply_user_specific_code_for_wrong_user_returns_error(): void
    {
        $otherUser = User::factory()->create();
        $currentUser = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($currentUser);

        $this->createDiscount([
            'code' => 'VIP',
            'user_id' => $otherUser->id,
        ]);

        $result = $this->service->apply('VIP', 100000);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('شما نیست', $result['message']);
    }

    // ── remove ──

    public function test_remove_clears_coupon_session(): void
    {
        session(['coupon' => ['code' => 'TEST']]);

        $this->service->remove();

        $this->assertNull(session('coupon'));
    }

    // ── current ──

    public function test_current_returns_null_when_no_coupon(): void
    {
        $this->assertNull($this->service->current());
    }

    public function test_current_returns_coupon_data(): void
    {
        session(['coupon' => ['code' => 'TEST', 'type' => 'percent', 'value' => 10]]);

        $current = $this->service->current();

        $this->assertEquals('TEST', $current['code']);
    }

    // ── calculate ──

    public function test_calculate_returns_zero_without_coupon(): void
    {
        $this->assertEquals(0, $this->service->calculate(100000));
    }

    public function test_calculate_percent_discount(): void
    {
        session(['coupon' => ['type' => 'percent', 'value' => 25]]);

        $this->assertEquals(25000, $this->service->calculate(100000));
    }

    public function test_calculate_cash_discount(): void
    {
        session(['coupon' => ['type' => 'cash', 'value' => 30000]]);

        $this->assertEquals(30000, $this->service->calculate(100000));
    }

    public function test_calculate_cash_discount_never_exceeds_subtotal(): void
    {
        session(['coupon' => ['type' => 'cash', 'value' => 200000]]);

        $this->assertEquals(50000, $this->service->calculate(50000));
    }

    // ── recordUsage ──

    public function test_record_usage_creates_record(): void
    {
        $discount = $this->createDiscount();
        $user = User::factory()->create();

        $this->service->recordUsage($discount->id, $user->id, null, 15000);

        $this->assertDatabaseHas('discount_usage', [
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'discount_amount' => 15000,
        ]);
    }

    public function test_record_usage_with_null_user(): void
    {
        $discount = $this->createDiscount();

        $this->service->recordUsage($discount->id, null, null, 15000);

        $this->assertDatabaseHas('discount_usage', [
            'discount_id' => $discount->id,
            'user_id' => null,
            'discount_amount' => 15000,
        ]);
    }

    public function test_record_usage_with_order_id(): void
    {
        $discount = $this->createDiscount();
        $user = User::factory()->create();

        // Create a real order to satisfy FK constraint
        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'order_status' => 'paid',
            'total_price' => 100000,
            'payment_price' => 100000,
            'address' => ['state' => '', 'city' => '', 'address' => '', 'receiptor_mobile' => '', 'postal_code' => ''],
        ]);

        $this->service->recordUsage($discount->id, $user->id, $order->id, 15000);

        $this->assertDatabaseHas('discount_usage', [
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => 15000,
        ]);
    }

    // ── apply stores with_discount and pay_type in session ──

    public function test_apply_stores_with_discount_flag_in_session(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'WITHDISC', 'with_discount' => 1, 'pay_type' => 'category']);

        $this->service->apply('WITHDISC', 100000);

        $coupon = session('coupon');
        $this->assertTrue($coupon['with_discount']);
        $this->assertEquals('category', $coupon['pay_type']);
    }

    public function test_apply_stores_without_discount_flag(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $this->createDiscount(['code' => 'NODISC', 'with_discount' => 0, 'pay_type' => null]);

        $this->service->apply('NODISC', 100000);

        $coupon = session('coupon');
        $this->assertFalse($coupon['with_discount']);
        $this->assertNull($coupon['pay_type']);
    }
}
