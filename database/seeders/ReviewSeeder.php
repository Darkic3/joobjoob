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
        $products = Product::take(4)->get();
        $users = User::take(4)->get();

        if ($products->isEmpty() || $users->isEmpty()) {
            return;
        }

        // ۱۲ نظر متنوع برای چرخش صفحهٔ اصلی (۴ بچ ۳تایی) — هر نظر یک محصول و کاربر متفاوت
        $sampleReviews = [
            ['rating' => 5, 'comment' => 'کیفیت فوق‌العاده بود و بسته‌بندی خیلی شیک. ارسال هم سریع‌تر از چیزی بود که فکر می‌کردم.'],
            ['rating' => 4, 'comment' => 'محصول دقیقاً مثل عکس بود. فقط زمان ارسال یک روز بیشتر از تعیین‌شده طول کشید.'],
            ['rating' => 5, 'comment' => 'دومین خریدمه از این فروشگاه و هر دو بار عالی بود. پشتیبانی‌شون هم خیلی سریع جواب داد.'],
            ['rating' => 5, 'comment' => 'جنسش از چیزی که انتظار داشتم هم بهتر بود. حتماً دوباره از این فروشگاه خرید می‌کنم.'],
            ['rating' => 4, 'comment' => 'قیمت منصفانه و کیفیت قابل قبول. فقط کاش تنوع رنگ بیشتری داشت.'],
            ['rating' => 5, 'comment' => 'سفارش‌مو صبح دادم و عصر تحویل گرفتم! اصلاً انتظار این سرعت رو نداشتم.'],
            ['rating' => 4, 'comment' => 'محصول سالم و با کیفیت رسید. بسته‌بندی محکم بود و هیچ آسیبی ندید.'],
            ['rating' => 5, 'comment' => 'سه‌مین خریدمه و هر بار تجربه‌م بهتر شده. پیشنهاد می‌کنم به همه.'],
            ['rating' => 3, 'comment' => 'کیفیت خوب بود ولی رنگش کمی با عکس سایت فرق داشت. در کل راضی‌ام.'],
            ['rating' => 5, 'comment' => 'برای هدیه خریده بودم و خیلی پسندید. فروشگاه واقعاً با سلیقه بسته‌بندی کرده بود.'],
            ['rating' => 4, 'comment' => 'دوام خوبی داره و بعد از چند بار شستشو هم کیفیتش حفظ شده.'],
            ['rating' => 5, 'comment' => 'ارزش خرید بالایی داره. از انتخاب‌های متنوع و قیمت‌های منصفانه‌شون ممنونم.'],
        ];

        $users = $users->values();
        $products = $products->values();

        foreach ($sampleReviews as $index => $data) {
            $user = $users[$index % $users->count()];
            $product = $products[$index % $products->count()];

            $review = Review::firstOrNew([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'comment' => $data['comment'],
            ]);

            $review->rating = $data['rating'];
            $review->is_approved = true;
            // پخش تاریخ‌ها در بازهٔ چند هفته — جدیدترین‌ها اول نمایش داده می‌شوند
            $review->created_at = now()->subDays($index * 2 + 1);
            $review->save();
        }

        $this->command?->info('۱۲ نظر نمونه برای چرخش صفحهٔ اصلی ساخته شد.');
    }
}
