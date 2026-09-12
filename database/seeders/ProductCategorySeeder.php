<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        // ۱. دسته‌بندی مادر: پوشاک
        $clothing = ProductCategory::updateOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => ['fa' => 'پوشاک', 'en' => 'Clothing'],
                'icon' => 'shirt-outline', // مثال: نام آیکون
                'description' => ['fa' => 'انواع لباس‌های مردانه و زنانه با بهترین کیفیت', 'en' => 'High-quality men and women clothing'],
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // ۲. زیرمجموعه: جوراب
        ProductCategory::updateOrCreate(
            ['slug' => 'socks'],
            [
                'parent_id' => $clothing->id,
                'name' => ['fa' => 'جوراب', 'en' => 'Socks'],
                'icon' => 'footsteps-outline',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // ۳. زیرمجموعه: تی‌شرت و پولوشرت
        ProductCategory::updateOrCreate(
            ['slug' => 'tshirts-polos'],
            [
                'parent_id' => $clothing->id,
                'name' => ['fa' => 'تی‌شرت و پولوشرت', 'en' => 'T-Shirts & Polos'],
                'icon' => 'shirt-outline',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        // ۴. دسته‌بندی مادر: اکسسوری
        ProductCategory::updateOrCreate(
            ['slug' => 'accessories'],
            [
                'name' => ['fa' => 'اکسسوری', 'en' => 'Accessories'],
                'icon' => 'glasses-outline',
                'description' => ['fa' => 'کمربند، کلاه، شال و گردنبند', 'en' => 'Belts, hats, scarves, and necklaces'],
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
