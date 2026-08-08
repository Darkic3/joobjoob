<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Services\LocaleService;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * زبان‌های اولیه — idempotent (بر اساس code updateOrCreate).
     * در DatabaseSeeder اولین سیدر اجرا می‌شود.
     */
    public function run(): void
    {
        // اگر از قبل زبان پیش‌فرضی هست (مثلاً ادمین در پنل عوضش کرده)،
        // اجرای دوباره‌ی سیدر انتخاب او را بی‌صدا برنگرداند → is_default دست نمی‌خورد
        $hasDefault = Language::where('is_default', true)->exists();

        $languages = [
            [
                'code' => 'fa',
                'name' => ['fa' => 'فارسی', 'en' => 'Persian', 'ar' => 'الفارسية', 'tr' => 'Farsça'],
                'native_name' => 'فارسی',
                'dir' => 'rtl',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'en',
                'name' => ['fa' => 'انگلیسی', 'en' => 'English', 'ar' => 'الإنجليزية', 'tr' => 'İngilizce'],
                'native_name' => 'English',
                'dir' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'ar',
                'name' => ['fa' => 'عربی', 'en' => 'Arabic', 'ar' => 'العربية', 'tr' => 'Arapça'],
                'native_name' => 'العربية',
                'dir' => 'rtl',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'tr',
                'name' => ['fa' => 'ترکی', 'en' => 'Turkish', 'ar' => 'التركية', 'tr' => 'Türkçe'],
                'native_name' => 'Türkçe',
                'dir' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($languages as $language) {
            if ($hasDefault) {
                unset($language['is_default']);
            }

            Language::updateOrCreate(['code' => $language['code']], $language);
        }

        // تضمین: دقیقاً یک زبان پیش‌فرض (اگر هیچ‌کدام نبود → اولین زبان)
        if (! Language::where('is_default', true)->exists() && Language::exists()) {
            Language::orderBy('sort_order')->first()->update(['is_default' => true]);
        }

        app(LocaleService::class)->flush();
    }
}
