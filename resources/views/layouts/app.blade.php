<!DOCTYPE html>
<html lang="{{ $currentLocale ?? 'fa' }}" dir="{{ $direction ?? 'rtl' }}" data-theme="{{ $activeTheme ?? 'light-neumorphism' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.site_name'))</title>

    @if(($direction ?? 'rtl') === 'rtl')
        <link rel="preconnect" href="https://cdn.jsdelivr.net">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    @endif

    @vite(['resources/css/app.css', 'resources/css/neumorphism.css', 'resources/js/app.js'])

    {{-- Dynamic Theme Injection (Safe & Optimized) --}}
    @php
        $defaultVars = [
            '--bg' => '#e0e5ec',
            '--bg-elevated' => '#e0e5ec',
            '--shadow-light' => '#ffffff',
            '--shadow-dark' => '#a3b1c6',
            '--text-primary' => '#2d3748',
            '--text-secondary' => '#6b7788',
            '--accent' => '#6c63ff',
            '--accent-contrast' => '#ffffff',
            '--danger' => '#e53e5c',
            '--success' => '#38a169',
        ];
        // اگر متغیرها از میدلور آمده باشند از آن‌ها استفاده می‌کند، در غیر این صورت از پیش‌فرض
        $themeVars = $themeVariables ?? $defaultVars;
    @endphp

    <style>
        [data-theme="{{ $activeTheme ?? 'light-neumorphism' }}"] {
        @foreach($themeVars as $key => $value)
            {{ $key }}: {{ $value }};
        @endforeach
}
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="min-h-screen" style="background: var(--bg); color: var(--text-primary);">

<x-header />

<main class="max-w-7xl mx-auto px-4 md:px-6 py-6">
    @if (session('success'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--success)">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--danger)">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<x-footer />

@stack('scripts')
</body>
</html>
