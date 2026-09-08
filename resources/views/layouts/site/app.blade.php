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
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet"></noscript>
    @endif
    @vite(['resources/css/app.css', 'resources/js/ui-effects.js', 'resources/js/app.js', 'resources/js/card-format.js', 'resources/js/only-digits.js'])
    @stack('styles')
</head>


{{--@include('layouts.site._partials.head')--}}

<body class="min-h-screen{{ auth()->check() ? " auth" : "" }}" style="background: var(--bg);">

{{-- Skip-to-content link for keyboard accessibility (hidden until focused) --}}
<a href="#main-content" class="skip-to-content" aria-label="{{ __('messages.skip_to_content') }}">
    {{ __('messages.skip_to_content') }}
</a>

{{-- داده‌های نوار اعلان توسط SiteLayoutComposer آماده می‌شود --}}
<div class="announce-bar text-center text-xs md:text-sm font-semibold py-2 px-4">
        <span id="announce-text" data-messages='{{ json_encode($announceMessages) }}'>
            {{ $firstAnnounce }}
        </span>
</div>

<x-header :cart-count="$cartCount" :wishlist-count="$wishlistCount" :unread-notification-count="$unreadNotificationCount"/>

<main id="main-content" class="max-w-7xl mx-auto px-4 md:px-6 py-6" tabindex="-1">
    @yield('content')
</main>

{{-- Toast notifications (success / error) — Alpine.js --}}
@if (session('success') || session('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed top-20 left-1/2 -translate-x-1/2 z-50 max-w-md w-full mx-4"
     style="pointer-events: auto;">
    <div class="flex items-center gap-3 rounded-2xl px-5 py-4 shadow-lg backdrop-blur-md border"
         style="background: color-mix(in srgb, var(--bg) 85%, transparent);
                border-color: color-mix(in srgb, {{ session('error') ? 'var(--danger)' : 'var(--success)' }} 25%, transparent);">
        @if (session('error'))
            <svg class="w-5 h-5 shrink-0" style="color: var(--danger);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
        @else
            <svg class="w-5 h-5 shrink-0" style="color: var(--success);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
            </svg>
        @endif
        <span class="flex-1 text-sm font-semibold" style="color: {{ session('error') ? 'var(--danger)' : 'var(--success)' }};">
            {{ session('error') ?: session('success') }}
        </span>
        <button @click="show = false" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif

<x-footer/>

<x-mobile-bar :cart-count="$cartCount" :wishlist-count="$wishlistCount"/>

<x-back-to-top/>

<x-quick-view-modal/>

@if(setting('allow_open_requests', false))
<x-social-proof-toast/>
@endif

<x-toast/>

{{--@include('layouts.site._partials.scripts')--}}
@stack('scripts')
</body>
</html>
