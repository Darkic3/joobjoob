<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = app(StockService::class);
    }

    private function makeProduct(int $stock = 10, int $price = 100000): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'Cat', 'en' => 'Cat'],
            'slug' => fake()->slug(),
            'is_active' => true,
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'Test', 'en' => 'Test'],
            'slug' => fake()->unique()->slug(),
            'price' => $price,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    private function makeVariant(Product $product, int $stock = 5, int $price = 100000): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'price' => $price,
            'final_price' => $price,
            'stock' => $stock,
        ]);
    }

    private function makeOrder(array $items = []): Order
    {
        $user = User::factory()->create();
        $status = OrderShippingStatus::create(['title' => 'Normal', 'default' => 1]);
        $order = Order::create([
            'user_id' => $user->id,
            'order_status' => 'paying',
            'shipping_status_id' => $status->id,
            'payment_price' => 0,
            'total_price' => 0,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['qty'] ?? 1,
                'price' => $item['price'] ?? 0,
                'discounted_price' => $item['price'] ?? 0,
            ]);
        }

        return $order;
    }

    // ==================== reserveStock ====================

    public function test_reserve_stock_decreases_product_stock(): void
    {
        $product = $this->makeProduct(stock: 10);
        $order = $this->makeOrder([['product_id' => $product->id, 'qty' => 3, 'price' => 100000]]);

        $this->stockService->reserveStock($order);

        $product->refresh();
        $this->assertEquals(7, $product->stock);
    }

    public function test_reserve_stock_decreases_variant_stock(): void
    {
        $product = $this->makeProduct(stock: 20);
        $variant = $this->makeVariant($product, stock: 8);
        $order = $this->makeOrder([['product_id' => $product->id, 'variant_id' => $variant->id, 'qty' => 3, 'price' => 100000]]);

        $this->stockService->reserveStock($order);

        $variant->refresh();
        $product->refresh();
        $this->assertEquals(5, $variant->stock);
        // Product stock is recalculated as sum of all variants (5)
        $this->assertEquals(5, $product->stock);
    }

    public function test_reserve_stock_creates_inventory_transaction(): void
    {
        $product = $this->makeProduct(stock: 10);
        $order = $this->makeOrder([['product_id' => $product->id, 'qty' => 2, 'price' => 100000]]);

        $this->stockService->reserveStock($order);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'quantity' => 2,
            'type' => 'subtract',
            'order_id' => $order->id,
        ]);
    }

    public function test_reserve_stock_throws_on_insufficient_stock(): void
    {
        $product = $this->makeProduct(stock: 2);
        $order = $this->makeOrder([['product_id' => $product->id, 'qty' => 5, 'price' => 100000]]);

        $this->expectException(\RuntimeException::class);
        $this->stockService->reserveStock($order);
    }

    public function test_reserve_stock_does_not_decrease_on_failure(): void
    {
        $product = $this->makeProduct(stock: 2);
        $order1 = $this->makeOrder([['product_id' => $product->id, 'qty' => 2, 'price' => 100000]]);
        $order2 = $this->makeOrder([['product_id' => $product->id, 'qty' => 3, 'price' => 100000]]);

        $this->stockService->reserveStock($order1);

        try { $this->stockService->reserveStock($order2); } catch (\RuntimeException $e) { /* expected */ }

        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }

    public function test_reserve_stock_multiple_items_single_order(): void
    {
        $p1 = $this->makeProduct(stock: 10);
        $p2 = $this->makeProduct(stock: 5);
        $order = $this->makeOrder([
            ['product_id' => $p1->id, 'qty' => 3, 'price' => 100000],
            ['product_id' => $p2->id, 'qty' => 2, 'price' => 50000],
        ]);

        $this->stockService->reserveStock($order);

        $p1->refresh();
        $p2->refresh();
        $this->assertEquals(7, $p1->stock);
        $this->assertEquals(3, $p2->stock);
    }

    public function test_reserve_stock_empty_order(): void
    {
        $order = $this->makeOrder([]);
        $this->assertTrue($this->stockService->reserveStock($order));
    }

    // ==================== restoreStock ====================

    public function test_restore_stock_increases_product_stock(): void
    {
        $product = $this->makeProduct(stock: 7);
        $order = $this->makeOrder([['product_id' => $product->id, 'qty' => 3, 'price' => 100000]]);

        $this->stockService->restoreStock($order);

        $product->refresh();
        $this->assertEquals(10, $product->stock);
    }

    public function test_restore_stock_increases_variant_stock(): void
    {
        $product = $this->makeProduct(stock: 5);
        $variant = $this->makeVariant($product, stock: 5);
        $order = $this->makeOrder([['product_id' => $product->id, 'variant_id' => $variant->id, 'qty' => 3, 'price' => 100000]]);

        $this->stockService->restoreStock($order);

        $variant->refresh();
        $product->refresh();
        $this->assertEquals(8, $variant->stock);
        // Product stock is recalculated as sum of all variants (8)
        $this->assertEquals(8, $product->stock);
    }

    public function test_restore_stock_creates_inventory_transaction(): void
    {
        $product = $this->makeProduct(stock: 8);
        $order = $this->makeOrder([['product_id' => $product->id, 'qty' => 2, 'price' => 100000]]);

        $this->stockService->restoreStock($order);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'quantity' => 2,
            'type' => 'add',
            'order_id' => $order->id,
        ]);
    }

    public function test_restore_stock_multiple_items(): void
    {
        $p1 = $this->makeProduct(stock: 7);
        $p2 = $this->makeProduct(stock: 3);
        $order = $this->makeOrder([
            ['product_id' => $p1->id, 'qty' => 3, 'price' => 100000],
            ['product_id' => $p2->id, 'qty' => 2, 'price' => 50000],
        ]);

        $this->stockService->restoreStock($order);

        $p1->refresh();
        $p2->refresh();
        $this->assertEquals(10, $p1->stock);
        $this->assertEquals(5, $p2->stock);
    }

    // ==================== validateCartItems ====================

    public function test_validate_cart_items_returns_empty_when_all_available(): void
    {
        $product = $this->makeProduct(stock: 10);
        $result = $this->stockService->validateCartItems([['product_id' => $product->id, 'qty' => 5]]);

        $this->assertEmpty($result['unavailable']);
        $this->assertEmpty($result['insufficient']);
    }

    public function test_validate_cart_items_detects_unavailable(): void
    {
        $product = $this->makeProduct(stock: 0);
        $result = $this->stockService->validateCartItems([['product_id' => $product->id, 'qty' => 1, 'name' => 'Test']]);

        $this->assertCount(1, $result['unavailable']);
    }

    public function test_validate_cart_items_detects_insufficient(): void
    {
        $product = $this->makeProduct(stock: 2);
        $result = $this->stockService->validateCartItems([['product_id' => $product->id, 'qty' => 5, 'name' => 'Test']]);

        $this->assertEmpty($result['unavailable']);
        $this->assertCount(1, $result['insufficient']);
        $this->assertEquals(2, $result['insufficient'][0]['available_qty']);
    }

    // ==================== getAvailableStock ====================

    public function test_get_available_stock_product(): void
    {
        $product = $this->makeProduct(stock: 7);
        $this->assertEquals(7, $this->stockService->getAvailableStock($product->id));
    }

    public function test_get_available_stock_variant(): void
    {
        $product = $this->makeProduct(stock: 20);
        $variant = $this->makeVariant($product, stock: 5);
        $this->assertEquals(5, $this->stockService->getAvailableStock($product->id, $variant->id));
    }

    public function test_get_available_stock_returns_zero_for_missing(): void
    {
        $this->assertEquals(0, $this->stockService->getAvailableStock(99999));
    }

    // ==================== enrichCartItemsWithStockStatus ====================

    public function test_enrich_cart_items_adds_stock_status(): void
    {
        $inStock = $this->makeProduct(stock: 5);
        $outOfStock = $this->makeProduct(stock: 0);

        $items = collect([
            ['product_id' => $inStock->id, 'variant_id' => null],
            ['product_id' => $outOfStock->id, 'variant_id' => null],
        ]);

        $enriched = $this->stockService->enrichCartItemsWithStockStatus($items);

        $this->assertTrue($enriched[0]['is_in_stock']);
        $this->assertEquals(5, $enriched[0]['current_stock']);
        $this->assertFalse($enriched[1]['is_in_stock']);
        $this->assertEquals(0, $enriched[1]['current_stock']);
    }
}
