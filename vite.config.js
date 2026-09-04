import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/app-admin.css',
                'resources/js/ui-effects.js',
                'resources/js/app.js',
                'resources/js/app-admin.js',
                'resources/js/card-format.js',
            ],
            refresh: true,
        }),
    ],
});
