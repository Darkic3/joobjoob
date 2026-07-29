<!DOCTYPE html>
<html lang="{{ $currentLocale ?? app()->getLocale() }}" dir="{{ $direction ?? 'rtl' }}" data-theme="{{ $activeTheme ?? 'light-neumorphism' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.site_name'))</title>

    @if(($direction ?? 'rtl') === 'rtl')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    @endif

    @vite(['resources/css/app.css','resources/css/neumorphism.css', 'resources/js/theme.js', 'resources/js/language.js', 'resources/js/slider.js'])
    @stack('styles')
</head>
<body class="min-h-screen" style="background: var(--bg);">

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
