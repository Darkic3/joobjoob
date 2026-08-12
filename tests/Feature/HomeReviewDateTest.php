<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeReviewDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function makeReview(Carbon $createdAt): Review
    {
        $category = ProductCategory::firstOrCreate(['slug' => 'cat'], [
            'name' => ['fa' => 'دسته', 'en' => 'Category'],
            'is_active' => true,
        ]);

        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => 'review-product',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'نظر آزمایشی',
            'is_approved' => true,
        ]);
        $review->created_at = $createdAt;
        $review->save();

        return $review;
    }

    public function test_home_review_card_shows_localized_date_in_persian(): void
    {
        $this->makeReview(Carbon::parse('2026-08-06 12:00:00'));

        $this->get('/')
            ->assertOk()
            ->assertSee('نظر آزمایشی')
            ->assertSee('مرداد')
            ->assertSee('۱۴۰۵');
    }

    public function test_home_review_card_shows_localized_date_in_english(): void
    {
        $this->makeReview(Carbon::parse('2026-08-06 12:00:00'));

        $this->get('/en')
            ->assertOk()
            ->assertSee('نظر آزمایشی')
            ->assertSee('06 August 2026');
    }

    public function test_home_review_card_shows_localized_date_in_arabic_hijri(): void
    {
        $this->makeReview(Carbon::parse('2026-08-06 12:00:00'));

        $this->get('/ar')
            ->assertOk()
            ->assertSee('نظر آزمایشی')
            ->assertSee('صفر')
            ->assertSee('١٤٤٨');
    }
}
