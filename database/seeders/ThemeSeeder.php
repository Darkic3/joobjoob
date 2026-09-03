<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['name' => 'پیش‌فرض', 'slug' => 'default', 'preview_color' => '#e0e5ec', 'is_default' => true],
            ['name' => 'تیره', 'slug' => 'dark', 'preview_color' => '#1a1a2e', 'is_default' => false],
        ];

        foreach ($themes as $theme) {
            DB::table('themes')->updateOrInsert(
                ['slug' => $theme['slug']],
                array_merge($theme, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command?->info('۲ تم نمونه ساخته شد.');
    }
}
