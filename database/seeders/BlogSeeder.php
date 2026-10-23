<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $guideCat = BlogCategory::where('slug', 'buying-guide')->first();
        $tipsCat = BlogCategory::where('slug', 'fashion-tips')->first();
        // ادمین را داینامیک پیدا کن — ایمیل ادمین از env می‌آید و ثابت نیست
        $admin = User::where('is_admin', true)->first()
            ?? User::where('email', config('admin.owner.email'))->first();

        if (! $admin) {
            $this->command?->warn('⚠️ ادمینی پیدا نشد (AdminOwnerSeeder را اول اجرا کنید) — مقاله‌ای ساخته نشد.');

            return;
        }

        $posts = [
            [
                'blog_category_id' => $guideCat?->id,
                'user_id' => $admin->id,
                'title' => 'راهنمای انتخاب سایز مناسب تی‌شرت',
                'slug' => 'tshirt-size-guide',
                'excerpt' => 'چطور سایز مناسب تی‌شرت را انتخاب کنیم تا خرید رضایت‌بخشی داشته باشیم.',
                'content' => '<p>انتخاب سایز مناسب یکی از مهم‌ترین مراحل خرید آنلاین لباس است...</p><p>برای اندازه‌گیری، یک متر خیاطی بردارید و دور سینه، دور کمر و قد خود را اندازه بگیرید...</p>',
                'views' => 245,
                'is_active' => true,
                'show_in_first_page' => true,
            ],
            [
                'blog_category_id' => $tipsCat?->id,
                'user_id' => $admin->id,
                'title' => 'ترندهای پاییزه ۲۰۲۶',
                'slug' => 'autumn-trends-2026',
                'excerpt' => 'با ترندهای جدید پاییزه آشنا شوید.',
                'content' => '<p>پاییز ۲۰۲۶ با رنگ‌های گرم و بافت‌های خاصی همراه است...</p><p>رنگ‌های زرشکی، قهوه‌ای و سبز تیره از محبوب‌ترین رنگ‌های این فصل هستند...</p>',
                'views' => 182,
                'is_active' => true,
                'show_in_first_page' => true,
            ],
            [
                'blog_category_id' => $guideCat?->id,
                'user_id' => $admin->id,
                'title' => 'نگهداری از محصولات چرم طبیعی',
                'slug' => 'leather-care-guide',
                'excerpt' => 'نکات مهم برای نگهداری و مراقبت از محصولات چرمی.',
                'content' => '<p>چرم طبیعی نیاز به مراقبت ویژه دارد...</p><p>از قرار دادن محصولات چرمی در معرض نور مستقیم خورشید خودداری کنید...</p>',
                'views' => 98,
                'is_active' => true,
                'show_in_first_page' => false,
            ],
        ];

        foreach ($posts as $post) {
            Blog::updateOrCreate(['slug' => $post['slug']], $post);
        }

        $this->command?->info('۳ مقاله نمونه ساخته شد.');
    }
}
