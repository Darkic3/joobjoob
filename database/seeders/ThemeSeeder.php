<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['name' => 'Light Neumorphism', 'slug' => 'light-neumorphism', 'preview_color' => '#e0e5ec', 'is_default' => true],
            ['name' => 'Dark Neumorphism', 'slug' => 'dark-neumorphism', 'preview_color' => '#2d3748', 'is_default' => false],
            ['name' => 'Blue Theme', 'slug' => 'blue-theme', 'preview_color' => '#dde9f7', 'is_default' => false],
            ['name' => 'Green Theme', 'slug' => 'green-theme', 'preview_color' => '#e2ece3', 'is_default' => false],
        ];

        foreach ($themes as $theme) {
            Theme::updateOrCreate(['slug' => $theme['slug']], $theme);
        }
    }
}
