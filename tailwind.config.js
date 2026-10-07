import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Vazirmatn', ...defaultTheme.fontFamily.sans],
                // تم Eco-Collectible: تیتر نمایشی محدود + مونو برای شناسه/قیمت
                display: ['Estedad', 'Vazirmatn', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'Vazirmatn', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                'eco-sm': '8px',   // دکمه‌ها
                'eco': '12px',     // کارت‌ها
                'eco-lg': '18px',  // سکشن‌ها / مدال
            },
            boxShadow: {
                'eco-xs': '0 1px 2px rgb(36 36 34 / .04)',
                'eco-sm': '0 1px 2px rgb(36 36 34 / .04), 0 4px 12px -6px rgb(36 36 34 / .08)',
                'eco-md': '0 2px 4px rgb(36 36 34 / .03), 0 12px 28px -14px rgb(36 36 34 / .16)',
                'eco-lg': '0 24px 48px -24px rgb(36 36 34 / .22)',
            },
            colors: {
                primary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
                // پالت Eco-Collectible — مقادیر از کانال‌های RGB در resources/css/eco/tokens.css
                eco: {
                    canvas: 'rgb(var(--eco-canvas-rgb) / <alpha-value>)',
                    'canvas-2': 'rgb(var(--eco-canvas-2-rgb) / <alpha-value>)',
                    surface: 'rgb(var(--eco-surface-rgb) / <alpha-value>)',
                    line: 'rgb(var(--eco-line-rgb) / <alpha-value>)',
                    ink: 'rgb(var(--eco-ink-rgb) / <alpha-value>)',
                    'ink-2': 'rgb(var(--eco-ink-2-rgb) / <alpha-value>)',
                    'ink-3': 'rgb(var(--eco-ink-3-rgb) / <alpha-value>)',
                    sage: 'rgb(var(--eco-sage-rgb) / <alpha-value>)',
                    clay: 'rgb(var(--eco-clay-rgb) / <alpha-value>)',
                    sand: 'rgb(var(--eco-sand-rgb) / <alpha-value>)',
                    danger: 'rgb(var(--eco-danger-rgb) / <alpha-value>)',
                },
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
            },
            screens: {
                '3xl': '1920px',
            },
        },
    },

    plugins: [],
};
