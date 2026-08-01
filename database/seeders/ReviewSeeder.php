<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(3)->get();

        $user = User::first() ?? User::factory()->create();

        $sampleReviews = [
            [
                'rating' => 5,
                'comment' => 'کیفیت فوق‌العاده بود و بسته‌بندی خیلی شیک. ارسال هم سریع‌تر از چیزی بود که فکر می‌کردم.',
            ],
            [
                'rating' => 4,
                'comment' => 'محصول دقیقاً مثل عکس بود. فقط زمان ارسال یک روز بیشتر از تعیین‌شده طول کشید.',
            ],
            [
                'rating' => 5,
                'comment' => 'دومین خریدمه از این فروشگاه و هر دو بار عالی بود. پشتیبانیشون هم خیلی سریع جواب داد.',
            ],
        ];

        foreach ($products as $product) {
            foreach ($sampleReviews as $reviewData) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'],
                    'is_approved' => true,
                ]);
            }
        }
    }
}
