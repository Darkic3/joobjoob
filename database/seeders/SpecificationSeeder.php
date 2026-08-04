<?php

namespace Database\Seeders;

use App\Models\Specification;
use Illuminate\Database\Seeder;

class SpecificationSeeder extends Seeder
{
    public function run(): void
    {
        // گروه‌ها (والدها)
        $color = Specification::updateOrCreate(
            ['title->fa' => 'رنگ', 'parent_id' => null],
            [
                'title' => ['fa' => 'رنگ', 'en' => 'Color'],
                'type' => 'select',
                'active' => true,
                'is_filter' => true,
                'is_color' => true,
                'color_code' => '#000000',
            ]
        );

        $size = Specification::updateOrCreate(
            ['title->fa' => 'سایز', 'parent_id' => null],
            [
                'title' => ['fa' => 'سایز', 'en' => 'Size'],
                'type' => 'select',
                'active' => true,
                'is_filter' => true,
                'is_color' => false,
            ]
        );

        $material = Specification::updateOrCreate(
            ['title->fa' => 'جنس', 'parent_id' => null],
            [
                'title' => ['fa' => 'جنس', 'en' => 'Material'],
                'type' => 'text',
                'active' => true,
                'is_filter' => false,
                'is_color' => false,
            ]
        );

        // مقادیر رنگ
        $colorValues = [
            ['fa' => 'مشکی', 'en' => 'Black', 'code' => '#1F2937'],
            ['fa' => 'سفید', 'en' => 'White', 'code' => '#F9FAFB'],
            ['fa' => 'طوسی', 'en' => 'Gray', 'code' => '#9CA3AF'],
            ['fa' => 'سورمه‌ای', 'en' => 'Navy', 'code' => '#1E3A8A'],
            ['fa' => 'زرشکی', 'en' => 'Maroon', 'code' => '#7F1D1D'],
            ['fa' => 'قهوه‌ای', 'en' => 'Brown', 'code' => '#78350F'],
        ];
        foreach ($colorValues as $cv) {
            Specification::updateOrCreate(
                ['title->fa' => $cv['fa'], 'parent_id' => $color->id],
                [
                    'title' => ['fa' => $cv['fa'], 'en' => $cv['en']],
                    'type' => 'select',
                    'parent_id' => $color->id,
                    'active' => true,
                    'is_color' => true,
                    'color_code' => $cv['code'],
                ]
            );
        }

        // مقادیر سایز
        $sizeValues = [
            ['fa' => 'S', 'en' => 'S'],
            ['fa' => 'M', 'en' => 'M'],
            ['fa' => 'L', 'en' => 'L'],
            ['fa' => 'XL', 'en' => 'XL'],
            ['fa' => '39-41', 'en' => '39-41'],
            ['fa' => '42-44', 'en' => '42-44'],
        ];
        foreach ($sizeValues as $sv) {
            Specification::updateOrCreate(
                ['title->fa' => $sv['fa'], 'parent_id' => $size->id],
                [
                    'title' => ['fa' => $sv['fa'], 'en' => $sv['en']],
                    'type' => 'select',
                    'parent_id' => $size->id,
                    'active' => true,
                ]
            );
        }

        // مشخصه نوشتاری جنس (مقدارهای نمونه به عنوان child برای type=text تعریف نمی‌شوند)
        Specification::updateOrCreate(
            ['title->fa' => 'کشور سازنده', 'parent_id' => null],
            [
                'title' => ['fa' => 'کشور سازنده', 'en' => 'Country'],
                'type' => 'text',
                'active' => true,
            ]
        );

        $this->command?->info('مشخصات نمونه ساخته شد.');
    }
}
