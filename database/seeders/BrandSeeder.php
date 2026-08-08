<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'title' => ['fa' => 'نایک‌استایل', 'en' => 'NikStyle'],
                'url' => 'nikstyle',
                'description' => ['fa' => 'برند پوشاک ورزشی و راحتی', 'en' => 'Sportswear & casual brand'],
            ],
            [
                'title' => ['fa' => 'بیسیک‌ویر', 'en' => 'BasicWear'],
                'url' => 'basicwear',
                'description' => ['fa' => 'پوشاک پایه و ساده', 'en' => 'Basic essentials clothing'],
            ],
            [
                'title' => ['fa' => 'چرم‌دوز', 'en' => 'CharmDuz'],
                'url' => 'charmduz',
                'description' => ['fa' => 'محصولات چرم طبیعی', 'en' => 'Genuine leather goods'],
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['url' => $brand['url']],
                array_merge($brand, ['active' => true])
            );
        }
    }
}
