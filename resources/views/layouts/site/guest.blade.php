<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}" data-digits="{{ $digitFamily }}"
      data-theme="{{ $activeTheme ?? 'light-neumorphism' }}">

<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', __('messages.site_name')))</title>

    @if($direction === 'rtl')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/card-format.js', 'resources/js/only-digits.js'])
    @stack('styles')
</head>

<body class="min-h-screen flex items-center justify-center" style="background: var(--bg);">

    <div class="w-full max-w-md px-4 py-10">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
