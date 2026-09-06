<?php

namespace Database\Seeders;

use App\Models\PromoBanner;
use Illuminate\Database\Seeder;

class PromoBannerSeeder extends Seeder
{
    public function run(): void
    {
        // نمونه‌هایی که هر ۵ سبک بنر را با تون/حالت‌های متفاوت پوشش می‌دهند تا
        // تمام حالت‌های ممکنِ اسلایدر بنرهای میانی دیده شود (سقف نمایش: ۶ بنر).
        $banners = [
            [
                'icon' => 'megaphone',
                'title' => ['fa' => 'تخفیف ۲۰٪ روی اکسسوری', 'en' => '20% Off Accessories'],
                'subtitle' => ['fa' => 'فقط تا پایان هفته', 'en' => 'Only until end of week'],
                'url' => '/products?category=accessories',
                'style' => 'gradient',
                'tone' => 'accent',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'icon' => 'ticket',
                'title' => ['fa' => 'کد تخفیف WELCOME10', 'en' => 'Coupon Code WELCOME10'],
                'subtitle' => ['fa' => '۱۰٪ تخفیف اولین خرید', 'en' => '10% off your first order'],
                'url' => '/products',
                'style' => 'coupon',
                'tone' => 'gold',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'icon' => 'truck',
                'title' => ['fa' => 'ارسال رایگان', 'en' => 'Free Shipping'],
                'subtitle' => ['fa' => 'برای سفارش‌های بالای ۵۰۰ هزار تومان', 'en' => 'On orders over 500K Tomans'],
                'url' => null,
                'style' => 'neumorphic',
                'tone' => 'blue',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'icon' => 'gift',
                'title' => ['fa' => 'جدیدهای شگفت‌انگیز', 'en' => 'Amazing New Arrivals'],
                'subtitle' => null,
                'url' => '/products',
                'style' => 'minimal',
                'tone' => 'emerald',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'icon' => 'star',
                'title' => ['fa' => 'خواندنی‌های تازه', 'en' => 'Fresh Reads'],
                'subtitle' => ['fa' => 'جدیدترین مقالات بلاگ', 'en' => 'Latest blog posts'],
                'url' => null,
                'style' => 'dark',
                'tone' => 'violet',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'icon' => 'lightning',
                'title' => ['fa' => 'پیشنهاد ویژه', 'en' => 'Special Offer'],
                'subtitle' => ['fa' => 'شگفت‌زده خواهی شد!', 'en' => 'You will be surprised!'],
                'url' => '/products?category=deals',
                'style' => 'gradient',
                'tone' => 'orange',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            PromoBanner::updateOrCreate(['title->fa' => $banner['title']['fa']], $banner);
        }

        $this->command?->info('۶ بنر میانی نمونه ساخته شد.');
    }
}
