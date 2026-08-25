<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            AdminOwnerSeeder::class,
            ProductCategorySeeder::class,
            BrandSeeder::class,
            SpecificationSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            // --- سیستم سفارشات ---
            StateSeeder::class,
            CitySeeder::class,
            OrderShippingStatusSeeder::class,
        ]);
    }
}
