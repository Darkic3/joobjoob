import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/neumorphism.css', 'resources/js/app.js', 'resources/js/theme.js', 'resources/js/language.js', 'resources/js/slider.js'],
            refresh: true,
        }),
    ],
});
