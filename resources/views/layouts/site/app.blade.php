<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}" data-digits="{{ $digitFamily }}"
      data-theme="{{ $activeTheme ?? 'light-neumorphism' }}">

<head>
    {{-- کلاس js: حالت «مخفی» هیرو فقط وقتی JS فعال است اعمال می‌شود؛ اگر JS یا باندل
         از کار بیفتد، محتوای هیرو هرگز نامرئی نمی‌ماند --}}
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', __('messages.site_name')))</title>

    {{-- SEO: canonical + hreflang + Open Graph locale --}}
    <link rel="canonical" href="{{ localizedUrl() }}">
    @foreach(locale()->active() as $code => $lang)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ localizedUrl($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ localizedUrl(locale()->defaultCode()) }}">
    <meta property="og:locale" content="{{ ogLocale() }}">
    <meta property="og:locale:alternate" content="{{ ogLocale(locale()->defaultCode()) }}">

    @if($direction === 'rtl')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @endif
    @vite(['resources/css/app.css', 'resources/js/ui-effects.js', 'resources/js/app.js'])
    @stack('styles')
</head>


{{--@include('layouts.site._partials.head')--}}

<body class="min-h-screen" style="background: var(--bg);">

{{-- داده‌های نوار اعلان توسط SiteLayoutComposer آماده می‌شود --}}
<div class="announce-bar text-center text-xs md:text-sm font-semibold py-2 px-4">
        <span id="announce-text" data-messages='{{ json_encode($announceMessages) }}'>
            {{ $firstAnnounce }}
        </span>
</div>

<x-header :cart-count="$cartCount" :wishlist-count="$wishlistCount"/>

<main class="max-w-7xl mx-auto px-4 md:px-6 @yield('main_class', 'py-6')">
    @if (session('success'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--success)">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--danger)">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<x-footer/>

<x-mobile-bar :cart-count="$cartCount" :wishlist-count="$wishlistCount"/>

<x-back-to-top/>

<x-quick-view-modal/>

<x-social-proof-toast/>

{{--@include('layouts.site._partials.scripts')--}}
@stack('scripts')
</body>
</html>
