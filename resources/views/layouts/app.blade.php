<!DOCTYPE html>
<html lang="{{ $currentLocale ?? app()->getLocale() }}" dir="{{ $direction ?? 'rtl' }}" data-theme="{{ $activeTheme ?? 'light-neumorphism' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.site_name'))</title>

    @if(($direction ?? 'rtl') === 'rtl')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @vite(['resources/css/app.css', 'resources/js/ui-effects.js'])
    @stack('styles')
</head>
<body class="min-h-screen" style="background: var(--bg);">

{{-- تعریف آرایه پیام‌ها در PHP برای جلوگیری از باگ کامپایلر Blade --}}
@php
    $announceMessages = [
        "🚚 " . __("messages.trust_free_shipping_desc") . " — امروز تا نیمه‌شب",
        "🎁 با کد WELCOME10، ۱۰٪ تخفیف اولین خرید بگیر",
        "🔥 پیشنهادهای لحظه‌ای فقط تا پایان امشب",
    ];
@endphp

<div class="announce-bar text-center text-xs md:text-sm font-semibold py-2 px-4">
        <span id="announce-text" data-messages='{{ json_encode($announceMessages) }}'>
            🚚 {{ __('messages.trust_free_shipping_desc') }} — امروز تا نیمه‌شب
        </span>
</div>

<x-header :cart-count="$cartCount ?? 0" />

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

<x-social-proof-toast />

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@stack('scripts')
</body>
</html>
