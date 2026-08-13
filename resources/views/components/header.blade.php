@props(['cartCount' => 0, 'wishlistCount' => 0])

@php
    $siteName = setting('site_name', __('messages.site_name'));
@endphp

{{-- هدر شناور (Fluid Island) — دسکتاپ: نوار قرصی شیشه‌ای شناور؛ موبایل: لوگو + همبرگر مورف --}}
<header class="site-header-wrap sticky top-3 z-50 px-3 md:px-5">
    <div class="site-header max-w-7xl mx-auto">

        {{-- لوگو: مارک گرادینتی (Double-Bezel) + نام --}}
        <a href="{{ localizedRoute('home') }}" class="site-logo shrink-0" aria-label="{{ $siteName }}">
            <span class="site-logo-mark" aria-hidden="true">{{ mb_substr($siteName, 0, 1) }}</span>
            <span class="site-logo-text truncate">{{ $siteName }}</span>
        </a>

        {{-- ناو دسکتاپ --}}
        <nav class="hidden lg:flex items-center gap-1 ms-3" aria-label="{{ __('messages.navigation') }}">
            <a href="{{ localizedRoute('home') }}"
               class="site-nav-link {{ request()->routeIs('home', 'locale.home') ? 'is-active' : '' }}">
                {{ __('messages.home') }}
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-nav-link {{ request()->routeIs('products.index', 'locale.products.index', 'products.category', 'locale.products.category') ? 'is-active' : '' }}">
                {{ __('messages.products') }}
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-nav-link">
                {{ __('messages.special_offers') }}
            </a>
        </nav>

        {{-- جستجو (md به بالا) --}}
        <form action="{{ localizedRoute('products.index') }}" method="GET" role="search"
              class="site-search hidden md:flex flex-1 max-w-md ms-auto me-3">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('messages.search_placeholder') }}" class="site-search-input"
                   autocomplete="off">
            <svg class="site-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 1 1 4 10.5a6.5 6.5 0 0 1 13 0Z"/>
            </svg>
        </form>

        {{-- اکشن‌ها — در موبایل فقط هامبرگر می‌ماند (زبان/تم داخل منو، سبد/علاقه‌مندی در نوار پایین) --}}
        <div class="flex items-center gap-2 ms-auto lg:ms-0">

            @if (count($locales) > 1)
                <div class="relative hidden lg:block">
                    <button type="button" data-lang-dropdown-btn
                            class="site-lang-btn text-xs font-bold"
                            title="{{ __('messages.language') }}" aria-haspopup="true" aria-expanded="false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" stroke-linecap="round"/>
                        </svg>
                        <span>{{ strtoupper($currentLocale) }}</span>
                        <svg class="w-3 h-3 site-lang-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>
                    <div data-lang-dropdown-menu class="site-lang-menu hidden absolute end-0 top-full mt-2 p-2 w-44 z-50">
                        @foreach($locales as $code => $lang)
                            <a href="{{ localizedUrl($code) }}"
                               class="site-lang-item {{ $currentLocale === $code ? 'is-active' : '' }}">
                                <span class="site-lang-code">{{ strtoupper($code) }}</span>
                                <span class="flex-1">{{ $lang->localizedName() }}</span>
                                @if ($currentLocale === $code)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" d="m5 13 4 4L19 7"/>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="button" id="theme-toggle" class="neu-toggle hidden lg:block" title="{{ __('messages.dark_mode') }}">
                <span class="neu-toggle-dot"></span>
            </button>

            <a href="{{ localizedRoute('wishlist.index') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
               title="{{ __('messages.wishlist') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 0 1 0-6.364Z"/>
                </svg>
                <span id="wishlist-count" class="site-badge">{{ $wishlistCount }}</span>
            </a>

            <a href="{{ localizedRoute('cart.index') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
               title="{{ __('messages.cart') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <span id="cart-count" class="site-badge">{{ $cartCount }}</span>
            </a>

            {{-- حساب کاربری: مهمان → لاگین، عضو → پنل کاربری --}}
            @auth
                <a href="{{ localizedRoute('dashboard') }}" class="site-action-btn relative hidden lg:inline-flex"
                   title="{{ auth()->user()->name }}" aria-label="{{ __('messages.dashboard') }}">
                    <span class="site-account-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                </a>
            @else
                <a href="{{ localizedRoute('login') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
                   title="{{ __('messages.login') }}" aria-label="{{ __('messages.login') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0 18.9 18.9 0 0 1-3.55 2.55H8.05A18.9 18.9 0 0 1 4.5 20.25Z"/>
                    </svg>
                </a>
            @endauth

            {{-- همبرگر مورف — فقط موبایل/تبلت --}}
            <button type="button" class="site-hamburger lg:hidden" data-menu-btn
                    aria-label="{{ __('messages.menu') }}" aria-expanded="false" aria-controls="site-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

{{-- منوی تمام‌صفحه موبایل — z پایین‌تر از هدر تا همبرگر مورف‌شده روی آن دیده شود --}}
<div class="site-menu lg:hidden" id="site-menu" data-site-menu
     role="dialog" aria-modal="true" aria-label="{{ __('messages.navigation') }}" aria-hidden="true">
    <div class="site-menu-backdrop" data-menu-close></div>
    <div class="site-menu-panel">

        <nav class="site-menu-nav" aria-label="{{ __('messages.navigation') }}">
            <a href="{{ localizedRoute('home') }}"
               class="site-menu-link {{ request()->routeIs('home', 'locale.home') ? 'is-active' : '' }}">
                <span class="site-menu-num">01</span>
                <span class="site-menu-label">{{ __('messages.home') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-menu-link {{ request()->routeIs('products.*', 'locale.products.*') ? 'is-active' : '' }}">
                <span class="site-menu-num">02</span>
                <span class="site-menu-label">{{ __('messages.products') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-menu-link">
                <span class="site-menu-num">03</span>
                <span class="site-menu-label">{{ __('messages.special_offers') }}</span>
            </a>
        </nav>

        {{-- حساب کاربری — مهمان → لاگین، عضو → پنل کاربری --}}
        <div class="site-menu-account">
            @auth
                <a href="{{ localizedRoute('dashboard') }}" class="site-menu-account-link">
                    <span class="site-menu-account-icon">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="site-menu-account-body">
                        <span class="site-menu-account-title">{{ auth()->user()->name }}</span>
                        <span class="site-menu-account-sub">{{ __('messages.dashboard') }}</span>
                    </span>
                    <svg class="site-menu-account-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <a href="{{ localizedRoute('login') }}" class="site-menu-account-link">
                    <span class="site-menu-account-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0 18.9 18.9 0 0 1-3.55 2.55H8.05A18.9 18.9 0 0 1 4.5 20.25Z"/>
                        </svg>
                    </span>
                    <span class="site-menu-account-body">
                        <span class="site-menu-account-title">{{ __('messages.login') }}</span>
                        <span class="site-menu-account-sub">{{ __('messages.login_subtitle') }}</span>
                    </span>
                    <svg class="site-menu-account-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            @endauth
        </div>

        <div class="site-menu-footer">
            @if (count($locales) > 1)
                <div class="site-menu-langs" aria-label="{{ __('messages.language') }}">
                    @foreach($locales as $code => $lang)
                        <a href="{{ localizedUrl($code) }}"
                           class="site-menu-lang {{ $currentLocale === $code ? 'is-active' : '' }}">
                            {{ $lang->localizedName() }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="site-menu-theme">
                <span class="site-menu-theme-label">{{ __('messages.dark_mode') }}</span>
                <button type="button" id="theme-toggle-menu" class="neu-toggle"
                        title="{{ __('messages.dark_mode') }}" aria-label="{{ __('messages.dark_mode') }}">
                    <span class="neu-toggle-dot"></span>
                </button>
            </div>
        </div>
    </div>
</div>
