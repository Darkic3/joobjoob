<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['title' => 'راهنمای خرید', 'slug' => 'buying-guide', 'description' => 'راهنمای انتخاب و خرید محصولات', 'is_active' => true, 'show_in_first_page' => true, 'sort_order' => 1],
            ['title' => 'اخبار فروشگاه', 'slug' => 'store-news', 'description' => 'آخرین اخبار و رویدادها', 'is_active' => true, 'show_in_first_page' => false, 'sort_order' => 2],
            ['title' => 'نکات مد و پوشاک', 'slug' => 'fashion-tips', 'description' => 'نکات و ترندهای مد و پوشاک', 'is_active' => true, 'show_in_first_page' => true, 'sort_order' => 3],
        ];

        foreach ($cats as $cat) {
            BlogCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
