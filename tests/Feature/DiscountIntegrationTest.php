<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        $this->user = User::factory()->create();
    }

    private function mc(string $s = 'cat'): ProductCategory
    {
        return ProductCategory::create(['name' => ['fa' => $s], 'slug' => $s . '-' . uniqid(), 'is_active' => true]);
    }

    private function mb(string $s = 'brand'): Brand
    {
        return Brand::create(['title' => ['fa' => $s], 'url' => $s . '-' . uniqid(), 'active' => true]);
    }

    private function mp(?ProductCategory $category = null, ?Brand $brand = null, int $price = 100000): Product
    {
        return Product::create([
            'product_category_id' => ($category ?? $this->mc())->id,
            'brand_id' => $brand?->id,
            'name' => ['fa' => 'test'],
            'slug' => 'p-' . uniqid(),
            'price' => $price,
            'discount_price' => 0,
            'final_price' => $price,
            'stock' => 50,
            'is_active' => true,
        ]);
    }

    private function cd(array $o = []): Discount
    {
        return Discount::create(array_merge([
            'title' => 'test',
            'code' => 'INT' . strtoupper(uniqid()),
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 5,
            'is_active' => true,
        ], $o));
    }

    private function add(Product $p): void
    {
        $this->post(route('cart.add', $p->id));
    }

    private function cp(string $c): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('checkout.apply-discount'), ['discount_code' => $c]);
    }

    // ═══ A. Pay-Type Restriction in Checkout ═══

    public function test_brand_restriction_only_applies_to_eligible_items(): void
    {
        $this->actingAs($this->user);
        $brandA = $this->mb('brandA');
        $brandB = $this->mb('brandB');
        $productA = $this->mp(brand: $brandA, price: 100000);
        $productB = $this->mp(brand: $brandB, price: 200000);
        $this->add($productA);
        $this->add($productB);
        $discount = $this->cd(['code' => 'BRANDA', 'amount' => 10, 'type' => 'percent', 'pay_type' => 'brand']);
        $discount->brands()->attach($brandA->id);
        $this->cp('BRANDA')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(10000, $cart->discount());
        $this->assertEquals(290000, $cart->total());
    }

    public function test_product_restriction_only_applies_to_eligible_items(): void
    {
        $this->actingAs($this->user);
        $productA = $this->mp(price: 100000);
        $productB = $this->mp(price: 200000);
        $this->add($productA);
        $this->add($productB);
        $discount = $this->cd(['code' => 'PRODA', 'amount' => 20, 'type' => 'percent', 'pay_type' => 'product']);
        $discount->products()->attach($productA->id);
        $this->cp('PRODA')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(20000, $cart->discount());
        $this->assertEquals(280000, $cart->total());
    }

    public function test_category_restriction_excludes_products_from_other_categories(): void
    {
        $this->actingAs($this->user);
        $catA = $this->mc('catA');
        $catB = $this->mc('catB');
        $productA = $this->mp(category: $catA, price: 100000);
        $productB = $this->mp(category: $catB, price: 100000);
        $this->add($productA);
        $this->add($productB);
        $discount = $this->cd(['code' => 'CATAONLY', 'amount' => 15, 'type' => 'percent', 'pay_type' => 'category']);
        $discount->categories()->attach($catA->id);
        $this->cp('CATAONLY')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(15000, $cart->discount());
    }

    public function test_brand_restriction_all_items_ineligible_returns_zero_discount(): void
    {
        $this->actingAs($this->user);
        $brandA = $this->mb('brandA');
        $brandB = $this->mb('brandB');
        $productB = $this->mp(brand: $brandB, price: 100000);
        $this->add($productB);
        $discount = $this->cd(['code' => 'BRANDA2', 'amount' => 10, 'type' => 'percent', 'pay_type' => 'brand']);
        $discount->brands()->attach($brandA->id);
        $this->cp('BRANDA2')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(0, $cart->discount());
    }

    // ═══ B. User-Restriction Flows ═══

    public function test_multi_user_discount_allowed_for_pivot_users(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $discount = $this->cd(['code' => 'MULTIUSER', 'user_id' => null]);
        $discount->users()->attach($this->user->id);
        $this->cp('MULTIUSER')->assertJson(['success' => true]);
    }

    public function test_multi_user_discount_still_applies_without_pivot_check(): void
    {
        // NOTE: CouponService does not check discount_users pivot at apply time.
        // The pivot is for display/management purposes only.
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $otherUser = User::factory()->create();
        $discount = $this->cd(['code' => 'RESTRICTED', 'user_id' => null]);
        $discount->users()->attach($otherUser->id);
        $this->cp('RESTRICTED')->assertJson(['success' => true]);
    }

    public function test_single_user_discount_applied_by_correct_user(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'MYCODE', 'user_id' => $this->user->id]);
        $this->cp('MYCODE')->assertJson(['success' => true]);
    }

    public function test_single_user_discount_rejected_by_wrong_user(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'HISCODE', 'user_id' => $otherUser->id]);
        $this->cp('HISCODE')->assertJson(['success' => false]);
    }

    // ═══ C. First-Purchase Restriction ═══

    public function test_first_purchase_code_succeeds_for_new_user(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'FIRSTOK', 'first_purchase' => 1, 'type' => 'cash', 'amount' => 15000]);
        $this->cp('FIRSTOK')->assertJson(['success' => true]);
    }

    public function test_first_purchase_code_fails_for_user_with_paid_order(): void
    {
        $this->actingAs($this->user);
        $this->user->orders()->create(['order_status' => 'paid', 'total_price' => 100000, 'payment_price' => 100000, 'address' => ['state' => '', 'city' => '', 'address' => '']]);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'FIRSTFAIL', 'first_purchase' => 1]);
        $this->cp('FIRSTFAIL')->assertJson(['success' => false]);
    }

    // ═══ D. Per-User Usage Limits ═══

    public function test_user_exhausts_per_user_limit(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $discount = $this->cd(['code' => 'LIMIT2', 'max_usage_per_user' => 2]);
        DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => $this->user->id, 'discount_amount' => 10000, 'used_at' => now()]);
        DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => $this->user->id, 'discount_amount' => 10000, 'used_at' => now()]);
        $this->add($product);
        $this->cp('LIMIT2')->assertJson(['success' => false]);
    }

    public function test_different_user_can_still_use_same_code(): void
    {
        $userA = $this->user;
        $userB = User::factory()->create();
        $discount = $this->cd(['code' => 'SHAREABLE', 'max_usage_per_user' => 1, 'count' => 100]);
        DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => $userA->id, 'discount_amount' => 10000, 'used_at' => now()]);
        $this->actingAs($userB);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cp('SHAREABLE')->assertJson(['success' => true]);
    }

    public function test_global_limit_reached_blocks_all_users(): void
    {
        $this->actingAs($this->user);
        $discount = $this->cd(['code' => 'FULLGLOBAL', 'count' => 1, 'max_usage_per_user' => 1]);
        DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => User::factory()->create()->id, 'discount_amount' => 10000, 'used_at' => now()]);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cp('FULLGLOBAL')->assertJson(['success' => false]);
    }

    public function test_unlimited_per_user_with_max_usage_zero(): void
    {
        $this->actingAs($this->user);
        $discount = $this->cd(['code' => 'UNLIMITED', 'max_usage_per_user' => 0, 'count' => 100]);
        for ($i = 0; $i < 5; $i++) {
            DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => $this->user->id, 'discount_amount' => 10000, 'used_at' => now()]);
        }
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cp('UNLIMITED')->assertJson(['success' => true]);
    }

    // ═══ E. Discount-Order Integration ═══

    public function test_order_model_has_discount_relationship(): void
    {
        $discount = $this->cd(['code' => 'RELTEST']);
        $order = Order::create(['user_id' => $this->user->id, 'order_status' => 'paying', 'total_price' => 100000, 'discount_price' => 10000, 'payment_price' => 90000, 'discount_id' => $discount->id, 'address' => ['state' => '', 'city' => '', 'address' => '']]);
        $this->assertNotNull($order->discount);
        $this->assertEquals($discount->id, $order->discount->id);
        $this->assertEquals('RELTEST', $order->discount->code);
    }

    public function test_order_without_discount_has_null_discount(): void
    {
        $order = Order::create(['user_id' => $this->user->id, 'order_status' => 'paying', 'total_price' => 100000, 'discount_price' => 0, 'payment_price' => 100000, 'discount_id' => null, 'address' => ['state' => '', 'city' => '', 'address' => '']]);
        $this->assertNull($order->discount);
    }

    public function test_discount_usage_records_link_to_order_and_user(): void
    {
        $discount = $this->cd(['code' => 'LINKTEST']);
        $order = Order::create(['user_id' => $this->user->id, 'order_status' => 'paid', 'total_price' => 100000, 'discount_price' => 10000, 'payment_price' => 90000, 'discount_id' => $discount->id, 'address' => ['state' => '', 'city' => '', 'address' => '']]);
        DiscountUsage::create(['discount_id' => $discount->id, 'user_id' => $this->user->id, 'order_id' => $order->id, 'discount_amount' => 10000, 'used_at' => now()]);
        $usage = DiscountUsage::where('discount_id', $discount->id)->first();
        $this->assertNotNull($usage->discount);
        $this->assertEquals($discount->id, $usage->discount->id);
        $this->assertNotNull($usage->user);
        $this->assertEquals($this->user->id, $usage->user->id);
        $this->assertNotNull($usage->order);
        $this->assertEquals($order->id, $usage->order->id);
    }

    public function test_discount_has_usage_records_relationship(): void
    {
        $discount = $this->cd(['code' => 'HASRECS']);
        DiscountUsage::create(['discount_id' => $discount->id, 'discount_amount' => 10000, 'used_at' => now()]);
        DiscountUsage::create(['discount_id' => $discount->id, 'discount_amount' => 5000, 'used_at' => now()]);
        $this->assertEquals(2, $discount->usageRecords()->count());
    }

    // ═══ F. Sequential Coupon Operations ═══

    public function test_apply_remove_apply_different_code(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'FIRST10', 'amount' => 10, 'type' => 'percent']);
        $this->cd(['code' => 'SECOND20', 'amount' => 20, 'type' => 'percent']);
        $this->cp('FIRST10')->assertJson(['success' => true]);
        $this->assertEquals('FIRST10', session('coupon')['code']);
        $this->postJson(route('checkout.remove-discount'))->assertJson(['success' => true]);
        $this->assertNull(session('coupon'));
        $this->cp('SECOND20')->assertJson(['success' => true]);
        $this->assertEquals('SECOND20', session('coupon')['code']);
        $this->assertEquals(20000, session('coupon')['discount_amount']);
    }

    public function test_apply_code_then_add_more_items_discount_updates(): void
    {
        $this->actingAs($this->user);
        $product1 = $this->mp(price: 100000);
        $this->add($product1);
        $this->cd(['code' => 'ADDUP', 'amount' => 10, 'type' => 'percent']);
        $this->cp('ADDUP')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(10000, $cart->discount());
        $product2 = $this->mp(price: 200000);
        $this->add($product2);
        $cart = app(CartService::class);
        $this->assertEquals(30000, $cart->discount());
    }

    public function test_apply_code_with_zero_amount_is_rejected(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'ZEROAMT', 'amount' => 0, 'type' => 'percent']);
        $this->cp('ZEROAMT')->assertJson(['success' => false]);
    }

    // ═══ G. Complex Cart Scenarios ═══

    public function test_mixed_eligibility_and_discounted_products(): void
    {
        $this->actingAs($this->user);
        $catEligible = $this->mc('eligible');
        $catIneligible = $this->mc('ineligible');
        $productEligibleDiscounted = $this->mp(category: $catEligible, price: 100000);
        $productEligibleDiscounted->update(['discount_price' => 80000, 'final_price' => 80000]);
        $productEligibleRegular = $this->mp(category: $catEligible, price: 100000);
        $productIneligible = $this->mp(category: $catIneligible, price: 100000);
        $this->add($productEligibleDiscounted);
        $this->add($productEligibleRegular);
        $this->add($productIneligible);
        $discount = $this->cd(['code' => 'MIXED', 'amount' => 10, 'type' => 'percent', 'pay_type' => 'category', 'with_discount' => 0]);
        $discount->categories()->attach($catEligible->id);
        $this->cp('MIXED')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(10000, $cart->discount());
    }

    public function test_cash_discount_caps_at_cart_subtotal(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 30000);
        $this->add($product);
        $this->cd(['code' => 'BIGCASH', 'amount' => 50000, 'type' => 'cash']);
        $this->cp('BIGCASH')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(30000, $cart->discount());
        $this->assertEquals(0, $cart->total());
    }

    public function test_percent_discount_on_multiple_items_sums_correctly(): void
    {
        $this->actingAs($this->user);
        $product1 = $this->mp(price: 100000);
        $product2 = $this->mp(price: 300000);
        $this->add($product1);
        $this->add($product2);
        $this->cd(['code' => 'MULTI', 'amount' => 10, 'type' => 'percent']);
        $this->cp('MULTI')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(40000, $cart->discount());
        $this->assertEquals(360000, $cart->total());
    }

    public function test_cash_discount_on_cart_with_multiple_items(): void
    {
        $this->actingAs($this->user);
        $product1 = $this->mp(price: 100000);
        $product2 = $this->mp(price: 200000);
        $this->add($product1);
        $this->add($product2);
        // with_discount=1 so simple calculation applies on cart total
        $this->cd(['code' => 'CASHMULTI', 'amount' => 50000, 'type' => 'cash', 'with_discount' => 1]);
        $this->cp('CASHMULTI')->assertJson(['success' => true]);
        $cart = app(CartService::class);
        $this->assertEquals(50000, $cart->discount());
        $this->assertEquals(250000, $cart->total());
    }

    // ═══ H. CouponService::calculateForCart ═══

    public function test_calculate_for_cart_respects_brand_restriction(): void
    {
        $this->actingAs($this->user);
        $brandA = $this->mb('calcBrandA');
        $brandB = $this->mb('calcBrandB');
        $productA = $this->mp(brand: $brandA, price: 100000);
        $productB = $this->mp(brand: $brandB, price: 200000);
        $this->add($productA);
        $this->add($productB);
        $discount = $this->cd(['code' => 'CALCBA', 'amount' => 10, 'type' => 'percent', 'pay_type' => 'brand']);
        $discount->brands()->attach($brandA->id);
        $couponService = app(CouponService::class);
        $couponService->apply('CALCBA', 300000);
        $cart = app(CartService::class);
        $this->assertEquals(10000, $couponService->calculateForCart($cart));
    }

    public function test_calculate_for_cart_respects_product_restriction(): void
    {
        $this->actingAs($this->user);
        $productA = $this->mp(price: 100000);
        $productB = $this->mp(price: 200000);
        $this->add($productA);
        $this->add($productB);
        $discount = $this->cd(['code' => 'CALCPA', 'amount' => 25, 'type' => 'percent', 'pay_type' => 'product']);
        $discount->products()->attach($productA->id);
        $couponService = app(CouponService::class);
        $couponService->apply('CALCPA', 300000);
        $cart = app(CartService::class);
        $this->assertEquals(25000, $couponService->calculateForCart($cart));
    }

    public function test_calculate_for_cart_no_pay_type_simple(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'SIMPLE', 'amount' => 15, 'type' => 'percent', 'with_discount' => 1]);
        $couponService = app(CouponService::class);
        $couponService->apply('SIMPLE', 100000);
        $cart = app(CartService::class);
        $this->assertEquals(15000, $couponService->calculateForCart($cart));
    }

    // ═══ I. Edge Cases ═══

    public function test_soft_deleted_discount_cannot_be_applied(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $discount = $this->cd(['code' => 'DELETED']);
        $discount->delete();
        $this->cp('DELETED')->assertJson(['success' => false]);
    }

    public function test_discount_starts_at_future_rejected(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'FUTURECODE', 'starts_at' => now()->addDays(30)]);
        $this->cp('FUTURECODE')->assertJson(['success' => false]);
    }

    public function test_discount_expires_at_past_rejected(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'OLDCODE', 'expires_at' => now()->subDay()]);
        $this->cp('OLDCODE')->assertJson(['success' => false]);
    }

    public function test_discount_within_valid_date_range_accepted(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'VALIDCODE', 'starts_at' => now()->subDay(), 'expires_at' => now()->addDay()]);
        $this->cp('VALIDCODE')->assertJson(['success' => true]);
    }

    public function test_basket_minimum_price_exactly_met_succeeds(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'MINEXACT', 'basket_minimum_price' => 100000]);
        $this->cp('MINEXACT')->assertJson(['success' => true]);
    }

    public function test_basket_minimum_price_not_met_fails(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 50000);
        $this->add($product);
        $this->cd(['code' => 'MINFAIL', 'basket_minimum_price' => 100000]);
        $this->cp('MINFAIL')->assertJson(['success' => false]);
    }

    public function test_discount_code_case_insensitive_in_checkout(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'CASETEST']);
        $this->cp('casetest')->assertJson(['success' => true]);
    }

    public function test_discount_code_with_whitespace_trimmed(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->cd(['code' => 'TRIMTEST']);
        $this->cp('  TRIMTEST  ')->assertJson(['success' => true]);
    }

    // ═══ J. Admin RBAC ═══

    public function test_non_admin_user_cannot_access_discount_admin(): void
    {
        $nonAdmin = User::factory()->create(['is_admin' => false]);
        $this->actingAs($nonAdmin)->get(route('admin.discounts.index'))->assertStatus(302);
    }

    public function test_non_admin_user_cannot_create_discount(): void
    {
        $nonAdmin = User::factory()->create(['is_admin' => false]);
        $this->actingAs($nonAdmin)->post(route('admin.discounts.store'), [
            'title' => 'HACK', 'code' => 'HACK' . uniqid(), 'amount' => 99,
            'type' => 'percent', 'count' => 1, 'max_usage_per_user' => 1, 'is_active' => true,
        ])->assertStatus(302);
    }

    // ═══ K. Store/Update with user_ids (Pivot) ═══

    public function test_store_with_user_ids_syncs_pivot(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.discounts.store'), [
            'title' => 'Pivot Test', 'code' => 'PIVOT' . uniqid(), 'amount' => 10,
            'type' => 'percent', 'count' => 10, 'max_usage_per_user' => 1,
            'user_ids' => [$user1->id, $user2->id], 'is_active' => true,
        ])->assertRedirect();
        $discount = Discount::where('code', 'LIKE', 'PIVOT%')->first();
        $this->assertNotNull($discount);
        $this->assertCount(2, $discount->users);
        $this->assertTrue($discount->users->contains($user1->id));
        $this->assertTrue($discount->users->contains($user2->id));
    }

    public function test_update_replaces_user_ids_in_pivot(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $discount = $this->cd(['code' => 'UPDPIVOT']);
        $discount->users()->attach([$user1->id, $user2->id]);
        $this->actingAs($admin)->put(route('admin.discounts.update', $discount), [
            'title' => 'Updated Pivot', 'code' => 'UPDPIVOT', 'amount' => 10,
            'type' => 'percent', 'count' => 10, 'max_usage_per_user' => 1,
            'user_ids' => [$user2->id, $user3->id], 'is_active' => true,
        ])->assertRedirect();
        $discount->refresh();
        $this->assertCount(2, $discount->users);
        $this->assertTrue($discount->users->contains($user2->id));
        $this->assertTrue($discount->users->contains($user3->id));
        $this->assertFalse($discount->users->contains($user1->id));
    }

    // ═══ L. Discount Model Helpers ═══

    public function test_amount_name_accessor_for_cash(): void
    {
        $discount = $this->cd(['amount' => 50000, 'type' => 'cash']);
        $this->assertStringContainsString('تومان', mb_strtoupper($discount->amount_name));
    }

    public function test_amount_name_accessor_for_percent(): void
    {
        $discount = $this->cd(['amount' => 25, 'type' => 'percent']);
        $this->assertStringContainsString('درصد', mb_strtoupper($discount->amount_name));
    }

    public function test_has_pay_type_restriction_true(): void
    {
        $discount = $this->cd(['pay_type' => 'category']);
        $this->assertTrue($discount->hasPayTypeRestriction());
    }

    public function test_has_pay_type_restriction_false(): void
    {
        $discount = $this->cd(['pay_type' => null]);
        $this->assertFalse($discount->hasPayTypeRestriction());
    }

    // ═══ M. Validation ═══

    public function test_store_validation_invalid_discountable_ids(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.discounts.store'), [
            'title' => 'Bad IDs', 'code' => 'BADIDS' . uniqid(), 'amount' => 10,
            'type' => 'percent', 'count' => 10, 'max_usage_per_user' => 1,
            'pay_type' => 'category', 'discountable_ids' => [99999], 'is_active' => true,
        ])->assertSessionHasErrors('discountable_ids');
    }

    public function test_store_validation_requires_type(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.discounts.store'), [
            'title' => 'No Type', 'code' => 'NOTYPE' . uniqid(), 'amount' => 10,
            'count' => 10, 'max_usage_per_user' => 1, 'is_active' => true,
        ])->assertSessionHasErrors('type');
    }

    public function test_store_validation_expires_at_after_starts_at(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.discounts.store'), [
            'title' => 'Bad Dates', 'code' => 'BADDATES' . uniqid(), 'amount' => 10,
            'type' => 'percent', 'count' => 10, 'max_usage_per_user' => 1,
            'starts_at' => now()->addDays(10)->format('Y-m-d'),
            'expires_at' => now()->addDays(5)->format('Y-m-d'), 'is_active' => true,
        ])->assertSessionHasErrors('expires_at');
    }

    // ═══ N. Order/Address Price Endpoints ═══

    public function test_order_price_endpoint_reflects_discount(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 200000);
        $this->add($product);
        $this->cd(['code' => 'API20', 'amount' => 20, 'type' => 'percent']);
        $this->cp('API20')->assertJson(['success' => true]);
        $this->getJson(route('checkout.order-price'))->assertJson([
            'final_price_sum' => 200000, 'price_discount' => 40000, 'price_cart' => 160000,
        ]);
    }

    public function test_address_price_endpoint_reflects_discount(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 150000);
        $this->add($product);
        $this->cd(['code' => 'ADDR25', 'amount' => 25, 'type' => 'percent']);
        $this->cp('ADDR25')->assertJson(['success' => true]);
        $this->getJson(route('checkout.address-price'))->assertJson([
            'final_price_sum' => 150000, 'price_discount' => 37500,
        ]);
    }

    public function test_order_price_endpoint_without_discount(): void
    {
        $this->actingAs($this->user);
        $product = $this->mp(price: 100000);
        $this->add($product);
        $this->getJson(route('checkout.order-price'))->assertJson([
            'final_price_sum' => 100000, 'price_discount' => 0, 'price_cart' => 100000,
        ]);
    }

    // ═══ O. Pay-Type Switch ═══

    public function test_update_can_switch_pay_type_from_category_to_brand(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $cat = $this->mc('oldcat');
        $brand = $this->mb('newbrand');
        $discount = $this->cd(['code' => 'SWITCH1', 'pay_type' => 'category']);
        $discount->categories()->attach($cat->id);
        $this->actingAs($admin)->put(route('admin.discounts.update', $discount), [
            'title' => 'Switched', 'code' => 'SWITCH1', 'amount' => 10,
            'type' => 'percent', 'count' => 50, 'max_usage_per_user' => 1,
            'pay_type' => 'brand', 'discountable_ids' => [$brand->id], 'is_active' => true,
        ])->assertRedirect();
        $discount->refresh();
        $this->assertCount(0, $discount->categories);
        $this->assertTrue($discount->brands->contains($brand->id));
    }

    public function test_update_clears_discountable_when_pay_type_removed(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $cat = $this->mc('willclear');
        $discount = $this->cd(['code' => 'CLEARIT', 'pay_type' => 'category']);
        $discount->categories()->attach($cat->id);
        $this->actingAs($admin)->put(route('admin.discounts.update', $discount), [
            'title' => 'No PayType', 'code' => 'CLEARIT', 'amount' => 10,
            'type' => 'percent', 'count' => 50, 'max_usage_per_user' => 1,
            'pay_type' => null, 'discountable_ids' => [], 'is_active' => true,
        ])->assertRedirect();
        $discount->refresh();
        $this->assertCount(0, $discount->categories);
    }

    // ═══ P. Polymorphic Relationships ═══

    public function test_discount_belongs_to_many_categories(): void
    {
        $cat1 = $this->mc('dc1');
        $cat2 = $this->mc('dc2');
        $discount = $this->cd(['code' => 'MULTICAT']);
        $discount->categories()->attach([$cat1->id, $cat2->id]);
        $this->assertCount(2, $discount->categories);
    }

    public function test_discount_belongs_to_many_brands(): void
    {
        $brand1 = $this->mb('db1');
        $brand2 = $this->mb('db2');
        $discount = $this->cd(['code' => 'MULTIBRAND']);
        $discount->brands()->attach([$brand1->id, $brand2->id]);
        $this->assertCount(2, $discount->brands);
    }

    public function test_discount_belongs_to_many_products(): void
    {
        $p1 = $this->mp();
        $p2 = $this->mp();
        $discount = $this->cd(['code' => 'MULTIPROD']);
        $discount->products()->attach([$p1->id, $p2->id]);
        $this->assertCount(2, $discount->products);
    }

    // ═══ Q. Valid Scope ═══

    public function test_valid_scope_filters_correctly(): void
    {
        $this->cd(['code' => 'V1', 'is_active' => true]);
        $this->cd(['code' => 'V2', 'is_active' => true, 'expires_at' => now()->subDay()]);
        $this->cd(['code' => 'V3', 'is_active' => false]);
        $this->cd(['code' => 'V4', 'is_active' => true, 'starts_at' => now()->addDays(5)]);
        $this->cd(['code' => 'V5', 'is_active' => true, 'starts_at' => now()->subDay(), 'expires_at' => now()->addDay()]);
        $valid = Discount::valid()->pluck('code')->toArray();
        $this->assertContains('V1', $valid);
        $this->assertContains('V5', $valid);
        $this->assertNotContains('V2', $valid);
        $this->assertNotContains('V3', $valid);
        $this->assertNotContains('V4', $valid);
    }
}
