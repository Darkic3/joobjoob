<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutDiscountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);

        $this->user = User::factory()->create();
    }

    private function makeProduct(string $slug = 'test-product', int $price = 150000): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته تست', 'en' => 'Test Cat'],
            'slug' => 'cat-' . uniqid(),
            'is_active' => true,
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => $slug,
            'price' => $price,
            'final_price' => $price,
            'stock' => 50,
            'is_active' => true,
        ]);
    }

    private function createDiscount(array $overrides = []): Discount
    {
        return Discount::create(array_merge([
            'title' => 'تخفیف تست',
            'code' => 'CART' . strtoupper(uniqid()),
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 3,
            'is_active' => true,
        ], $overrides));
    }

    // ── Apply Discount via AJAX ──

    public function test_apply_discount_with_valid_code(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $discount = $this->createDiscount(['code' => 'SAVE10', 'amount' => 10, 'type' => 'percent']);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'SAVE10',
        ]);

        $response->assertJson([
            'success' => true,
        ]);

        $coupon = session('coupon');
        $this->assertNotNull($coupon);
        $this->assertEquals('SAVE10', $coupon['code']);
    }

    public function test_apply_discount_with_invalid_code(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'FAKECODE',
        ]);

        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_apply_discount_empty_code(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => '',
        ]);

        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_apply_cash_discount(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('cash-product', 200000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'CASHOFF', 'amount' => 50000, 'type' => 'cash']);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'CASHOFF',
        ]);

        $response->assertJson(['success' => true]);

        $coupon = session('coupon');
        $this->assertEquals('cash', $coupon['type']);
        $this->assertEquals(50000, $coupon['value']);
    }

    public function test_apply_below_minimum_price_fails(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('cheap-product', 50000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'MINBUY', 'basket_minimum_price' => 200000]);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'MINBUY',
        ]);

        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonFragment(['message' => 'حداقل خرید برای این کد 200,000 تومان است.']);
    }

    // ── Remove Discount ──

    public function test_remove_discount_clears_coupon(): void
    {
        $this->actingAs($this->user);

        // First apply a discount
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));
        $this->createDiscount(['code' => 'TOREMOVE']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'TOREMOVE']);
        $this->assertNotNull(session('coupon'));

        // Then remove it
        $this->postJson(route('checkout.remove-discount'))
            ->assertJson(['success' => true]);

        $this->assertNull(session('coupon'));
    }

    // ── Price Calculation with Discount ──

    public function test_order_price_reflects_discount(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('price-test', 100000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'PRICE20', 'amount' => 20, 'type' => 'percent']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'PRICE20']);

        $response = $this->getJson(route('checkout.order-price'));

        $response->assertJson([
            'final_price_sum' => 100000,
            'price_discount' => 20000,
            'price_cart' => 80000,
        ]);
    }

    public function test_order_price_with_cash_discount(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('cash-price', 300000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'CASH30', 'amount' => 30000, 'type' => 'cash']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'CASH30']);

        $response = $this->getJson(route('checkout.order-price'));

        $response->assertJson([
            'final_price_sum' => 300000,
            'price_discount' => 30000,
            'price_cart' => 270000,
        ]);
    }

    public function test_address_price_reflects_discount(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('addr-price', 200000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'ADDR10', 'amount' => 10, 'type' => 'percent']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'ADDR10']);

        $response = $this->getJson(route('checkout.address-price'));

        $response->assertJson([
            'final_price_sum' => 200000,
            'price_discount' => 20000,
        ]);
    }

    // ── Cart Service Discount Calculation ──

    public function test_cart_service_discount_method(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('svc-test', 100000);
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'SVC25', 'amount' => 25, 'type' => 'percent']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'SVC25']);

        $cart = app(CartService::class);
        $this->assertEquals(25000, $cart->discount());
        $this->assertEquals(75000, $cart->total());
    }

    public function test_cart_discount_zero_without_coupon(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct('no-coupon', 100000);
        $this->post(route('cart.add', $product->id));

        $cart = app(CartService::class);
        $this->assertEquals(0, $cart->discount());
        $this->assertEquals(100000, $cart->total());
    }

    // ── Expired / Inactive Codes ──

    public function test_expired_discount_fails(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'EXPIRED', 'expires_at' => now()->subDay()]);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'EXPIRED',
        ]);

        $response->assertJson(['success' => false]);
    }

    public function test_inactive_discount_fails(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'OFF', 'is_active' => false]);

        $response = $this->postJson(route('checkout.apply-discount'), [
            'discount_code' => 'OFF',
        ]);

        $response->assertJson(['success' => false]);
    }

    // ── Pay Type Per-Item Discount ──

    public function test_pay_type_category_discount_only_applies_to_eligible_items(): void
    {
        $this->actingAs($this->user);

        // Create two categories
        $cat1 = ProductCategory::create(['name' => ['fa' => 'Cat1'], 'slug' => 'cat1', 'is_active' => true]);
        $cat2 = ProductCategory::create(['name' => ['fa' => 'Cat2'], 'slug' => 'cat2', 'is_active' => true]);

        // Product in cat1 (eligible)
        $product1 = Product::create([
            'product_category_id' => $cat1->id,
            'name' => ['fa' => 'P1'],
            'slug' => 'p1',
            'price' => 100000,
            'final_price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Product in cat2 (NOT eligible)
        $product2 = Product::create([
            'product_category_id' => $cat2->id,
            'name' => ['fa' => 'P2'],
            'slug' => 'p2',
            'price' => 100000,
            'final_price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->post(route('cart.add', $product1->id));
        $this->post(route('cart.add', $product2->id));

        $discount = $this->createDiscount([
            'code' => 'CATONLY',
            'amount' => 10,
            'type' => 'percent',
            'pay_type' => 'category',
        ]);
        $discount->categories()->attach($cat1->id);

        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'CATONLY'])
            ->assertJson(['success' => true]);

        $cart = app(CartService::class);

        // Total = 200000, discount = 10% of 100000 (only cat1 product) = 10000
        $this->assertEquals(10000, $cart->discount());
    }

    public function test_with_discount_flag_excludes_already_discounted_products(): void
    {
        $this->actingAs($this->user);

        // Product WITH actual discount: original price 100000, discounted to 80000
        $catDisc = ProductCategory::create(['name' => ['fa' => 'Disc'], 'slug' => 'disc', 'is_active' => true]);
        $productDiscounted = Product::create([
            'product_category_id' => $catDisc->id,
            'name' => ['fa' => 'Discounted Product'],
            'slug' => 'disc-prod',
            'price' => 100000,
            'discount_price' => 80000,
            'final_price' => 80000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Product WITHOUT discount
        $catReg = ProductCategory::create(['name' => ['fa' => 'Reg'], 'slug' => 'reg', 'is_active' => true]);
        $productRegular = Product::create([
            'product_category_id' => $catReg->id,
            'name' => ['fa' => 'Regular Product'],
            'slug' => 'reg-prod',
            'price' => 100000,
            'final_price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->post(route('cart.add', $productDiscounted->id));
        $this->post(route('cart.add', $productRegular->id));

        // Verify: discounted product should have original_price > price in cart
        $cart = app(CartService::class);
        $cartItems = $cart->content();
        $discItem = $cartItems->first(fn($i) => $i['product_id'] == $productDiscounted->id);
        $this->assertNotNull($discItem);
        $this->assertGreaterThan($discItem['price'], $discItem['original_price']);

        // Coupon that does NOT apply to already-discounted items
        $this->createDiscount([
            'code' => 'NODISC',
            'amount' => 10,
            'type' => 'percent',
            'with_discount' => 0,
        ]);

        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'NODISC'])
            ->assertJson(['success' => true]);

        $cart = app(CartService::class);

        // Subtotal = 180000, discount = 10% of 100000 (only regular product) = 10000
        $this->assertEquals(10000, $cart->discount());
    }

    public function test_with_discount_flag_includes_already_discounted_products(): void
    {
        $this->actingAs($this->user);

        $productDiscounted = Product::create([
            'product_category_id' => ProductCategory::create(['name' => ['fa' => 'Disc2'], 'slug' => 'disc2', 'is_active' => true])->id,
            'name' => ['fa' => 'Discounted'],
            'slug' => 'disc2',
            'price' => 80000,
            'discount_price' => 80000,
            'final_price' => 80000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->post(route('cart.add', $productDiscounted->id));

        // Coupon that DOES apply to discounted items
        $this->createDiscount([
            'code' => 'YESDISC',
            'amount' => 10,
            'type' => 'percent',
            'with_discount' => 1,
        ]);

        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'YESDISC'])
            ->assertJson(['success' => true]);

        $cart = app(CartService::class);

        // 10% of 80000 = 8000
        $this->assertEquals(8000, $cart->discount());
    }

    // ── Page Renders with Discount ──

    public function test_cart_page_shows_coupon_section_when_applied(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $this->createDiscount(['code' => 'SHOWME']);
        $this->postJson(route('checkout.apply-discount'), ['discount_code' => 'SHOWME']);

        $html = $this->get(route('cart.index'))->getContent();

        $this->assertStringContainsString('SHOWME', $html);
    }

    public function test_cart_page_hides_coupon_row_when_no_coupon(): void
    {
        $this->actingAs($this->user);

        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $html = $this->get(route('cart.index'))->getContent();

        // Coupon row should be hidden (has 'hidden' class)
        $this->assertStringContainsString('cart-coupon-row', $html);
        $this->assertStringContainsString('hidden', $html);
    }
}
