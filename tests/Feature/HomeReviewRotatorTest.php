<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeReviewRotatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function makeReviews(int $count): void
    {
        $category = ProductCategory::firstOrCreate(['slug' => 'cat'], [
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'is_active' => true,
        ]);

        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => 'rotator-product',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
        ]);

        for ($i = 0; $i < $count; $i++) {
            Review::create([
                'product_id' => $product->id,
                'user_id' => User::factory()->create()->id,
                'rating' => 5,
                'comment' => "نظر نمونه $i",
                'is_approved' => true,
                'is_featured' => true,
                'created_at' => now()->subMinutes($count - $i),
            ]);
        }
    }

    public function test_home_reviews_render_in_rotator_batches(): void
    {
        $this->makeReviews(6);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-reviews-rotator', false)
            ->assertSee('data-reviews-batch="0"', false)
            ->assertSee('data-reviews-batch="1"', false)
            ->assertSee('نظر نمونه 0')
            ->assertSee('نظر نمونه 5')
            ->assertSee('reviews-dot', false);
    }

    public function test_home_reviews_rotator_hides_when_no_reviews(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-reviews-rotator', false);
    }

    public function test_home_reviews_rotator_first_batch_is_active(): void
    {
        $this->makeReviews(6);

        $this->get('/')
            ->assertOk()
            ->assertSee('reviews-batch is-active', false);
    }
}
