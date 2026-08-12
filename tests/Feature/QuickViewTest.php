<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function makeProduct(string $slug = 'quick-product', int $price = 150000): Product
    {
        $category = ProductCategory::firstOrCreate(['slug' => 'cat'], [
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'is_active' => true,
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول پیش‌نمایش', 'en' => 'Preview Product'],
            'slug' => $slug,
            'price' => $price,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    public function test_quick_view_endpoint_returns_product_partial(): void
    {
        $product = $this->makeProduct();

        $this->get(route('products.quick-view', ['product' => $product->slug]))
            ->assertOk()
            ->assertSee('محصول پیش‌نمایش')
            ->assertSee(number_format(150000), false)
            ->assertSee('data-quick-view-add', false)
            ->assertSee(__('messages.view_details'));
    }

    public function test_quick_view_shows_add_to_cart_form_pointing_to_cart_add(): void
    {
        $product = $this->makeProduct();

        $this->get(route('products.quick-view', ['product' => $product->slug]))
            ->assertOk()
            ->assertSee(route('cart.add', ['product' => $product->id]), false)
            ->assertSee('name="qty"', false);
    }

    public function test_modal_and_card_buttons_render_on_home_page(): void
    {
        $this->makeProduct();

        $this->get('/')
            ->assertOk()
            ->assertSee('id="quick-view-modal"', false)
            ->assertSee('data-quick-view-url', false);
    }
}
