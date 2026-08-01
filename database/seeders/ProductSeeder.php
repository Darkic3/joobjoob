<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $socksCategory = ProductCategory::where('slug', 'socks')->first();
        $tshirtCategory = ProductCategory::where('slug', 'tshirts-polos')->first();
        $accessoriesCategory = ProductCategory::where('slug', 'accessories')->first();

        // محصول ۱: جوراب
        Product::create([
            'product_category_id' => $socksCategory->id,
            'name' => ['fa' => 'جوراب نخی مردانه طرح‌دار (بسته ۳ عددی)', 'en' => 'Men Patterned Cotton Socks (3-Pack)'],
            'slug' => 'men-patterned-cotton-socks-3pack',
            'short_description' => ['fa' => 'نخ پنبه‌ی طبیعی، بدون حساسیت و با دوام بالا', 'en' => 'Natural cotton, hypoallergenic and highly durable'],
            'description' => ['fa' => 'این جوراب‌ها از بهترین نخ پنبه تهیه شده و برای استفاده روزمره و ورزشی بسیار مناسب هستند...', 'en' => 'Made from the finest cotton, perfect for daily and sports use...'],
            'sku' => 'SK-001-BLK',
            'brand' => 'نایک‌استایل',
            'price' => 185000,
            'discount_price' => 149000,
            'stock' => 150,
            'weight' => 150, // گرم
            'status' => 'published',
            'is_featured' => true,
            'is_best_seller' => true,
            'colors' => ['مشکی', 'سفید', 'طوسی'],
            'sizes' => ['39-41', '42-44'],
            'deal_ends_at' => now()->addDays(5),
        ]);

        // محصول ۲: تی‌شرت
        Product::create([
            'product_category_id' => $tshirtCategory->id,
            'name' => ['fa' => 'تی‌شرت یقه گرد نخی ساده', 'en' => 'Plain Cotton Crew Neck T-Shirt'],
            'slug' => 'plain-cotton-crew-neck-tshirt',
            'short_description' => ['fa' => 'پارچه‌ی پنبه‌ای تنفس‌پذیر، مناسب فصل گرما', 'en' => 'Breathable cotton fabric, perfect for summer'],
            'description' => ['fa' => 'تی‌شرتی با برش استاندارد و پارچه‌ی خنک که پس از شستشو تغییر شکل نمی‌دهد.', 'en' => 'Standard cut and cool fabric that does not lose shape after washing.'],
            'sku' => 'TS-002-WHT',
            'brand' => 'بیسیک‌ویر',
            'price' => 450000,
            'discount_price' => null,
            'stock' => 85,
            'weight' => 250,
            'status' => 'published',
            'is_featured' => false,
            'is_best_seller' => true,
            'colors' => ['سفید', 'مشکی', 'سورمه‌ای', 'زرشکی'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
        ]);

        // محصول ۳: اکسسوری (کمربند)
        Product::create([
            'product_category_id' => $accessoriesCategory->id,
            'name' => ['fa' => 'کمربند چرم طبیعی مردانه', 'en' => 'Men Genuine Leather Belt'],
            'slug' => 'men-genuine-leather-belt',
            'short_description' => ['fa' => 'چرم طبیعی گاوی با سگک فلزی ضد زنگ', 'en' => 'Genuine cowhide leather with rust-proof metal buckle'],
            'description' => ['fa' => 'این کمربند از چرم درجه یک تبریز تهیه شده و با هر استایل رسمی یا نیمه‌رسمی ست می‌شود.', 'en' => 'Made from premium Tabriz leather, matches any formal or semi-formal style.'],
            'sku' => 'BLT-003-BRN',
            'brand' => 'چرم‌دوز',
            'price' => 890000,
            'discount_price' => 750000,
            'stock' => 40,
            'weight' => 300,
            'status' => 'published',
            'is_featured' => true,
            'is_best_seller' => false,
            'colors' => ['قهوه‌ای', 'مشکی'],
            'sizes' => ['90cm', '95cm', '100cm', '105cm'],
        ]);
    }
}
