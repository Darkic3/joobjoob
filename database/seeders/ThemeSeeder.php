<?php
namespace Database\Seeders;
use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Light Neumorphism', 'slug' => 'light-neumorphism',
                'preview_color' => '#e0e5ec', 'is_default' => true,
                'css_variables' => Theme::defaultVariables(),
            ],
            [
                'name' => 'Dark Neumorphism', 'slug' => 'dark-neumorphism',
                'preview_color' => '#2d3748', 'is_default' => false,
                'css_variables' => [
                    'bg' => '#2d3748', 'bg-elevated' => '#2d3748',
                    'shadow-light' => '#3a4759', 'shadow-dark' => '#212a37',
                    'text-primary' => '#edf2f7', 'text-secondary' => '#a0aec0',
                    'accent' => '#8b83ff', 'accent-contrast' => '#1a202c',
                    'danger' => '#fc8181', 'success' => '#68d391',
                ],
            ],
            [
                'name' => 'Blue Theme', 'slug' => 'blue-theme',
                'preview_color' => '#dde9f7', 'is_default' => false,
                'css_variables' => [
                    'bg' => '#dde9f7', 'bg-elevated' => '#dde9f7',
                    'shadow-light' => '#ffffff', 'shadow-dark' => '#a9c1de',
                    'text-primary' => '#1a365d', 'text-secondary' => '#4a6584',
                    'accent' => '#3182ce', 'accent-contrast' => '#ffffff',
                    'danger' => '#e53e5c', 'success' => '#38a169',
                ],
            ],
            [
                'name' => 'Green Theme', 'slug' => 'green-theme',
                'preview_color' => '#e2ece3', 'is_default' => false,
                'css_variables' => [
                    'bg' => '#e2ece3', 'bg-elevated' => '#e2ece3',
                    'shadow-light' => '#ffffff', 'shadow-dark' => '#a9c2ab',
                    'text-primary' => '#1c3a24', 'text-secondary' => '#4d6b53',
                    'accent' => '#38a169', 'accent-contrast' => '#ffffff',
                    'danger' => '#e53e5c', 'success' => '#2f855a',
                ],
            ],
        ];

        foreach ($themes as $theme) {
            Theme::updateOrCreate(['slug' => $theme['slug']], $theme);
        }
    }
}
