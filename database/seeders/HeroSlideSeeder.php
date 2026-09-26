<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        // هیرو مینیمال: تنها یک هیرو در صفحهٔ اصلی نمایش داده می‌شود
        HeroSlide::updateOrCreate(
            ['title->fa' => 'فروش ویژه پوشاک مردانه'],
            [
                'badge' => ['fa' => '🔥 پیشنهاد ویژه', 'en' => '🔥 Special Offer'],
                'title' => ['fa' => 'فروش ویژه پوشاک مردانه', 'en' => 'Men\'s Clothing Sale'],
                'desc' => ['fa' => 'تا ۴۰٪ تخفیف روی محصولات منتخب', 'en' => 'Up to 40% off on selected products'],
                'btn_text' => ['fa' => 'مشاهده محصولات', 'en' => 'View Products'],
                'image' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->command?->info('هیرو نمونهٔ صفحهٔ اصلی آماده شد.');
    }
}
