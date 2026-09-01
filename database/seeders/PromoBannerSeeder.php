<?php

namespace Database\Seeders;

use App\Models\PromoBanner;
use Illuminate\Database\Seeder;

class PromoBannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => ['fa' => 'تخفیف ۲۰٪ روی اکسسوری', 'en' => '20% Off Accessories'],
                'subtitle' => ['fa' => 'فقط تا پایان هفته', 'en' => 'Only until end of week'],
                'url' => '/products?category=accessories',
                'style' => 'gradient',
                'tone' => 'accent',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => ['fa' => 'ارسال رایگان', 'en' => 'Free Shipping'],
                'subtitle' => ['fa' => 'برای سفارش‌های بالای ۵۰۰ هزار تومان', 'en' => 'On orders over 500K Tomans'],
                'url' => null,
                'style' => 'solid',
                'tone' => 'success',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            PromoBanner::updateOrCreate(['title->fa' => $banner['title']['fa']], $banner);
        }

        $this->command?->info('۲ بنر میانی نمونه ساخته شد.');
    }
}
