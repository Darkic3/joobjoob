<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $names = [
            'گوشی هوشمند مدل X', 'لپ‌تاپ اولترابوک', 'پیراهن مردانه کلاسیک', 'کیف چرم زنانه',
            'هدفون بی‌سیم نویز کنسلینگ', 'ساعت هوشمند اسپرت', 'کفش رانینگ مدل پرو', 'عطر مردانه امبر',
            'تصفیه‌آب خانگی', 'دوچرخه کوهستان', 'کتاب رمان معاصر', 'کوله‌پشتی سفری',
        ];

        foreach ($names as $i => $name) {
            $price = rand(500, 20000) * 1000;
            $hasDiscount = $i % 3 === 0;

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($name).'-'.($i + 1)],
                [
                    'category_id' => $categories->random()->id,
                    'name' => $name,
                    'description' => 'توضیحات کامل محصول '.$name.'. کیفیت ساخت بالا، گارانتی اصالت و ارسال سریع.',
                    'price' => $price,
                    'discount_price' => $hasDiscount ? (int) ($price * 0.8) : null,
                    'stock' => rand(0, 50),
                    'brand' => ['برند A', 'برند B', 'برند C'][array_rand(['برند A', 'برند B', 'برند C'])],
                    'sku' => 'SKU-'.strtoupper(Str::random(8)),
                    'is_featured' => $i % 2 === 0,
                    'is_best_seller' => $i % 4 === 0,
                    'colors' => ['قرمز', 'آبی', 'مشکی'],
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'deal_ends_at' => $i % 3 === 0 ? now()->addDays(3) : null,
                ]
            );

            $product->images()->updateOrCreate(
                ['sort_order' => 0],
                ['url' => 'https://picsum.photos/seed/product'.$i.'/600/600', 'is_primary' => true]
            );
        }
    }
}
