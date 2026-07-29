<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'موبایل و تبلت', 'slug' => 'mobile-tablet', 'icon' => 'smartphone'],
            ['name' => 'لپ‌تاپ و کامپیوتر', 'slug' => 'laptop-computer', 'icon' => 'laptop'],
            ['name' => 'پوشاک', 'slug' => 'fashion', 'icon' => 'shirt'],
            ['name' => 'کفش و کیف', 'slug' => 'shoes-bags', 'icon' => 'shopping-bag'],
            ['name' => 'لوازم خانگی', 'slug' => 'home-appliances', 'icon' => 'home'],
            ['name' => 'زیبایی و سلامت', 'slug' => 'beauty-health', 'icon' => 'heart'],
            ['name' => 'کتاب و لوازم تحریر', 'slug' => 'books-stationery', 'icon' => 'book'],
            ['name' => 'ورزش و سفر', 'slug' => 'sports-travel', 'icon' => 'activity'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
