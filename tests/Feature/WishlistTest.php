<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\WishlistService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function makeProduct(string $slug = 'wish-product'): Product
    {
        $category = ProductCategory::firstOrCreate(['slug' => 'cat'], [
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'is_active' => true,
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول علاقه‌مندی', 'en' => 'Wish Product'],
            'slug' => $slug,
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    public function test_toggle_adds_and_removes_product(): void
    {
        $product = $this->makeProduct();

        $this->post(route('wishlist.toggle', ['product' => $product->slug]))
            ->assertJson(['in_wishlist' => true, 'count' => 1]);

        $this->assertTrue(app(WishlistService::class)->has($product));

        $this->post(route('wishlist.toggle', ['product' => $product->slug]))
            ->assertJson(['in_wishlist' => false, 'count' => 0]);

        $this->assertFalse(app(WishlistService::class)->has($product));
    }

    public function test_wishlist_page_shows_saved_products(): void
    {
        $product = $this->makeProduct();
        $this->post(route('wishlist.toggle', ['product' => $product->slug]));

        $this->get('/wishlist')
            ->assertOk()
            ->assertSee('محصول علاقه‌مندی');
    }

    public function test_empty_wishlist_page_shows_empty_state(): void
    {
        $this->get('/wishlist')
            ->assertOk()
            ->assertSee(__('messages.wishlist_empty'));
    }

    public function test_header_and_mobile_bar_badges_show_count(): void
    {
        $product = $this->makeProduct();
        $this->post(route('wishlist.toggle', ['product' => $product->slug]));

        $this->get('/')
            ->assertOk()
            ->assertSee('id="wishlist-count"', false)
            ->assertSee('id="wishlist-count-mobile"', false)
            ->assertSee('>1<', false);
    }

    public function test_product_card_heart_starts_active_when_in_wishlist(): void
    {
        $product = $this->makeProduct();
        $this->post(route('wishlist.toggle', ['product' => $product->slug]));

        $this->get('/')
            ->assertOk()
            ->assertSee('data-wishlist-active="1"', false);
    }

    public function test_wishlist_keeps_insertion_order(): void
    {
        $first = $this->makeProduct('wish-first');
        $second = $this->makeProduct('wish-second');
        $third = $this->makeProduct('wish-third');

        $this->post(route('wishlist.toggle', ['product' => $first->slug]));
        $this->post(route('wishlist.toggle', ['product' => $second->slug]));
        $this->post(route('wishlist.toggle', ['product' => $third->slug]));

        $ids = app(WishlistService::class)->ids();
        $this->assertSame([$first->id, $second->id, $third->id], $ids);
    }
}
