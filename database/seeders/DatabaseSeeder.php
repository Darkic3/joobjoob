<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * ترتیب اجرای سیدرها مهم است — هر سیدر ممکن است به داده‌ی سیدر قبلی وابسته باشد.
     */
    public function run(): void
    {
        $this->call([
            // ── ۱) زیرساخت ──
            LanguageSeeder::class,
            ThemeSeeder::class,
            SettingSeeder::class,

            // ── ۲) ادمین و نقش‌ها ──
            AdminOwnerSeeder::class,

            // ── ۳) کاربران نمونه ──
            UserSeeder::class,

            // ── ۴) استان و شهر ──
            StateSeeder::class,
            CitySeeder::class,

            // ── ۵) محصولات و مشخصات ──
            ProductCategorySeeder::class,
            BrandSeeder::class,
            SpecificationSeeder::class,
            ProductSeeder::class,

            // ── ۶) محتوا ──
            HeroSlideSeeder::class,
            TrustBadgeSeeder::class,
            PromoBannerSeeder::class,
            BlogCategorySeeder::class,
            BlogSeeder::class,

            // ── ۷) تخفیف و نظرات ──
            DiscountSeeder::class,
            ReviewSeeder::class,

            // ── ۸) ارسال و پرداخت ──
            ShippingMethodSeeder::class,
            BankSeeder::class,
            OrderShippingStatusSeeder::class,

            // ── ۹) آدرس و سفارش ──
            AddressSeeder::class,
            OrderSeeder::class,

            // ── ۱۰) پیام‌های تماس ──
            ContactRequestSeeder::class,
        ]);
    }
}
