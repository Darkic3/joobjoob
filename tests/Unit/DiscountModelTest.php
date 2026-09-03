<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Product;
use App\Models\ProductCategory;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    // ── isCurrentlyValid ──

    public function test_active_discount_without_dates_is_valid(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue($discount->isCurrentlyValid());
    }

    public function test_inactive_discount_is_not_valid(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'is_active' => false,
        ]);

        $this->assertFalse($discount->isCurrentlyValid());
    }

    public function test_discount_not_yet_started_is_not_valid(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
            'starts_at' => now()->addDays(5),
        ]);

        $this->assertFalse($discount->isCurrentlyValid());
    }

    public function test_expired_discount_is_not_valid(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($discount->isCurrentlyValid());
    }

    public function test_discount_within_date_range_is_valid(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($discount->isCurrentlyValid());
    }

    // ── hasReachedLimit ──

    public function test_zero_count_means_reached_limit(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($discount->hasReachedLimit());
    }

    public function test_discount_reaches_total_limit(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 2,
            'is_active' => true,
        ]);

        // Create 2 usages
        DiscountUsage::create([
            'discount_id' => $discount->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);
        DiscountUsage::create([
            'discount_id' => $discount->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $this->assertTrue($discount->hasReachedLimit());
    }

    public function test_discount_not_reaching_total_limit(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 5,
            'is_active' => true,
        ]);

        DiscountUsage::create([
            'discount_id' => $discount->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $this->assertFalse($discount->hasReachedLimit());
    }

    public function test_user_reaches_per_user_limit(): void
    {
        $user = \App\Models\User::factory()->create();

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 2,
            'is_active' => true,
        ]);

        DiscountUsage::create([
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);
        DiscountUsage::create([
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $this->assertTrue($discount->hasReachedLimit($user->id));
    }

    public function test_user_has_not_reached_per_user_limit(): void
    {
        $user = \App\Models\User::factory()->create();

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 3,
            'is_active' => true,
        ]);

        DiscountUsage::create([
            'discount_id' => $discount->id,
            'user_id' => $user->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $this->assertFalse($discount->hasReachedLimit($user->id));
    }

    // ── calculateDiscount ──

    public function test_percent_discount_calculation(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 25,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
        ]);

        $this->assertEquals(25000, $discount->calculateDiscount(100000));
    }

    public function test_cash_discount_calculation(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 50000,
            'type' => 'cash',
            'count' => 100,
            'is_active' => true,
        ]);

        $this->assertEquals(50000, $discount->calculateDiscount(200000));
    }

    public function test_discount_never_exceeds_subtotal(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 50000,
            'type' => 'cash',
            'count' => 100,
            'is_active' => true,
        ]);

        // Subtotal is less than discount amount
        $this->assertEquals(20000, $discount->calculateDiscount(20000));
    }

    public function test_percent_discount_never_exceeds_100_percent(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 100,
            'type' => 'percent',
            'count' => 100,
            'is_active' => true,
        ]);

        $this->assertEquals(100000, $discount->calculateDiscount(100000));
    }

    // ── isEligibleCartItem ──

    public function test_no_pay_type_restriction_makes_all_items_eligible(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($discount->isEligibleCartItem(['product_id' => 1]));
    }

    public function test_category_restriction_eligible_item(): void
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته تست'],
            'slug' => 'test-cat',
            'is_active' => true,
        ]);

        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست'],
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => 'category',
            'is_active' => true,
        ]);

        $discount->categories()->attach($category->id);

        $this->assertTrue($discount->isEligibleCartItem(['product_id' => $product->id]));
    }

    public function test_category_restriction_ineligible_item(): void
    {
        $category1 = ProductCategory::create([
            'name' => ['fa' => 'دسته ۱'],
            'slug' => 'cat-1',
            'is_active' => true,
        ]);
        $category2 = ProductCategory::create([
            'name' => ['fa' => 'دسته ۲'],
            'slug' => 'cat-2',
            'is_active' => true,
        ]);

        $product = Product::create([
            'product_category_id' => $category2->id,
            'name' => ['fa' => 'محصول تست'],
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => 'category',
            'is_active' => true,
        ]);

        $discount->categories()->attach($category1->id);

        $this->assertFalse($discount->isEligibleCartItem(['product_id' => $product->id]));
    }

    public function test_brand_restriction_eligible_item(): void
    {
        $brand = Brand::create([
            'title' => ['fa' => 'برند تست'],
            'url' => 'test-brand-' . uniqid(),
            'active' => true,
        ]);

        $product = Product::create([
            'product_category_id' => ProductCategory::create(['name' => ['fa' => 'Cat'], 'slug' => 'cat', 'is_active' => true])->id,
            'brand_id' => $brand->id,
            'name' => ['fa' => 'محصول تست'],
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => 'brand',
            'is_active' => true,
        ]);

        $discount->brands()->attach($brand->id);

        $this->assertTrue($discount->isEligibleCartItem(['product_id' => $product->id]));
    }

    public function test_product_restriction_eligible_item(): void
    {
        $product = Product::create([
            'product_category_id' => ProductCategory::create(['name' => ['fa' => 'Cat'], 'slug' => 'cat', 'is_active' => true])->id,
            'name' => ['fa' => 'محصول تست'],
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => 'product',
            'is_active' => true,
        ]);

        $discount->products()->attach($product->id);

        $this->assertTrue($discount->isEligibleCartItem(['product_id' => $product->id]));
    }

    public function test_product_restriction_ineligible_item(): void
    {
        $product = Product::create([
            'product_category_id' => ProductCategory::create(['name' => ['fa' => 'Cat'], 'slug' => 'cat', 'is_active' => true])->id,
            'name' => ['fa' => 'محصول تست'],
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'pay_type' => 'product',
            'is_active' => true,
        ]);

        // Don't attach the product
        $this->assertFalse($discount->isEligibleCartItem(['product_id' => $product->id]));
    }

    // ── appliesToDiscountedItems ──

    public function test_with_discount_true(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'with_discount' => 1,
            'is_active' => true,
        ]);

        $this->assertTrue($discount->appliesToDiscountedItems());
    }

    public function test_with_discount_false(): void
    {
        $discount = Discount::create([
            'title' => 'Test',
            'code' => 'TEST',
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'with_discount' => 0,
            'is_active' => true,
        ]);

        $this->assertFalse($discount->appliesToDiscountedItems());
    }

    // ── scopes ──

    public function test_scope_active_filters_only_active(): void
    {
        Discount::create(['title' => 'A', 'code' => 'A', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => true]);
        Discount::create(['title' => 'B', 'code' => 'B', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => false]);

        $this->assertEquals(1, Discount::active()->count());
    }

    public function test_scope_valid_filters_by_dates_and_active(): void
    {
        // Active, no dates
        Discount::create(['title' => 'A', 'code' => 'A', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => true]);
        // Active, expired
        Discount::create(['title' => 'B', 'code' => 'B', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => true, 'expires_at' => now()->subDay()]);
        // Inactive
        Discount::create(['title' => 'C', 'code' => 'C', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => false]);
        // Active, future start
        Discount::create(['title' => 'D', 'code' => 'D', 'amount' => 10, 'type' => 'percent', 'count' => 10, 'is_active' => true, 'starts_at' => now()->addDays(5)]);

        $this->assertEquals(1, Discount::valid()->count());
    }
}
