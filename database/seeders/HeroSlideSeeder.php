<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'badge' => ['fa' => '🔥 پیشنهاد ویژه', 'en' => '🔥 Special Offer'],
                'title' => ['fa' => 'فروش ویژه پوشاک مردانه', 'en' => 'Men\'s Clothing Sale'],
                'desc' => ['fa' => 'تا ۴۰٪ تخفیف روی محصولات منتخب', 'en' => 'Up to 40% off on selected products'],
                'btn_text' => ['fa' => 'مشاهده محصولات', 'en' => 'View Products'],
                'price' => 149000,
                'old_price' => 250000,
                'rating' => '4.8',
                'viewers' => 124,
                'image' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'badge' => ['fa' => '🆕 جدید', 'en' => '🆕 New'],
                'title' => ['fa' => 'کلکشن پاییزه', 'en' => 'Autumn Collection'],
                'desc' => ['fa' => 'جدیدترین مدل‌های پاییزه را ببینید', 'en' => 'Check out the latest autumn styles'],
                'btn_text' => ['fa' => 'همین الان بخرید', 'en' => 'Buy Now'],
                'price' => 450000,
                'old_price' => null,
                'rating' => '4.9',
                'viewers' => 89,
                'image' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'badge' => ['fa' => '🎁 هدیه', 'en' => '🎁 Gift'],
                'title' => ['fa' => 'ارسال رایگان بالای ۵۰۰ هزار تومان', 'en' => 'Free shipping over 500K Tomans'],
                'desc' => ['fa' => 'برای خریدهای بالای ۵۰۰,۰۰۰ تومان ارسال رایگان', 'en' => 'Free shipping for orders over 500,000 Tomans'],
                'btn_text' => ['fa' => 'مشاهده شرایط', 'en' => 'View Terms'],
                'price' => 0,
                'old_price' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::updateOrCreate(
                ['title->fa' => $slide['title']['fa']],
                $slide
            );
        }

        $this->command?->info('۳ اسلاید هیرو نمونه ساخته شد.');
    }
}
