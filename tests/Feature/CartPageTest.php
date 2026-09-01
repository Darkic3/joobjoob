<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function loginAsUser(): void
    {
        $this->actingAs(User::factory()->create());
    }

    private function makeProduct(string $slug = 'test-product', int $price = 150000): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'slug' => 'cat',
            'is_active' => true,
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => $slug,
            'price' => $price,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    // ==========================================
    // نوار پایین موبایل
    // ==========================================

    public function test_mobile_bar_renders_on_site_pages(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('data-mobile-search-btn', false)
            ->assertSee('data-mobile-search-input', false)
            ->assertSee('id="cart-count-mobile"', false)
            ->assertSee(__('messages.mobile_nav'), false)
            ->assertSee(__('messages.wishlist'));
    }

    public function test_back_to_top_button_renders_on_site_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="back-to-top"', false)
            ->assertSee(__('messages.back_to_top'), false);
    }

    public function test_mobile_bar_cart_badge_shows_real_session_count(): void
    {
        $this->loginAsUser();
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $this->get('/')
            ->assertOk()
            ->assertSee('id="cart-count-mobile"', false)
            ->assertSee('>1<', false);
    }

    // ==========================================
    // صفحه سبد خرید
    // ==========================================

    public function test_empty_cart_page_renders_with_empty_state(): void
    {
        $this->loginAsUser();
        $this->get('/cart')
            ->assertOk()
            ->assertSee(__('messages.cart_empty'))
            ->assertSee(__('messages.continue_shopping'));
    }

    public function test_cart_page_shows_added_items_and_totals(): void
    {
        $this->loginAsUser();
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product->id));
        $this->post(route('cart.add', $product->id)); // تعداد ۲

        $this->get('/cart')
            ->assertOk()
            ->assertSee('محصول تست')
            ->assertSee(format_price(300000), false);
    }

    public function test_cart_item_can_be_removed(): void
    {
        $this->loginAsUser();
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product->id));

        $cart = app(CartService::class);
        $this->assertSame(1, $cart->count());

        $rowId = $cart->content()->keys()->first();
        $this->delete(route('cart.remove', $rowId));

        $this->assertSame(0, $cart->count());
    }
}
