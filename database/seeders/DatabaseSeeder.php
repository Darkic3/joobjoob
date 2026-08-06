<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminOwnerSeeder::class,
            ProductCategorySeeder::class,
            BrandSeeder::class,
            SpecificationSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
