{{--
    هدر فروشگاه — جستجو، سوییچر زبان، تاگل تم، ویش‌لیست، سبد خرید.
    Props: cartCount (پیش‌فرض 0) — در پروژه واقعی از app(CartService::class)->count() پر کنید.
--}}
@props(['cartCount' => 0])

@php
    $locales = config('app.available_locales', ['fa' => 'فارسی', 'en' => 'English', 'ar' => 'العربية', 'tr' => 'Türkçe']);
@endphp

<header class="sticky top-0 z-40 backdrop-blur" style="background:color-mix(in srgb, var(--bg) 90%, transparent);">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 flex items-center gap-4">

        <a href="{{ route('home') }}" class="neu-flat-sm px-4 py-2 font-black text-lg shrink-0" style="color:var(--accent);">
            {{ __('messages.site_name') }}
        </a>

        <nav class="hidden lg:flex items-center gap-1 ms-2">
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-full text-sm font-medium hover:opacity-70">{{ __('messages.home') }}</a>
            <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-full text-sm font-medium hover:opacity-70">{{ __('messages.products') }}</a>
            <a href="{{ route('products.index') }}#deals" class="px-4 py-2 rounded-full text-sm font-medium hover:opacity-70">{{ __('messages.special_offers') }}</a>
        </nav>

        <form action="{{ route('products.index') }}" method="GET" class="hidden md:flex flex-1 max-w-md relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}"
                   class="neu-input w-full py-2.5 ps-11 pe-4 text-sm">
            <svg class="absolute top-1/2 -translate-y-1/2 start-4 w-4 h-4" style="color:var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 1 1 4 10.5a6.5 6.5 0 0 1 13 0Z"/>
            </svg>
        </form>

        <div class="flex items-center gap-2 ms-auto">

            <div class="relative">
                <button type="button" data-lang-dropdown-btn class="neu-btn w-10 h-10 grid place-items-center text-xs font-bold">
                    {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                </button>
                <div data-lang-dropdown-menu class="hidden absolute end-0 mt-2 neu-flat-sm p-2 w-32 text-sm z-50">
                    @foreach($locales as $code => $label)
                        <button type="button" data-lang-select="{{ $code }}"
                                class="w-full text-start px-3 py-2 rounded-lg hover:opacity-70 {{ ($currentLocale ?? app()->getLocale()) === $code ? 'font-bold' : '' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="button" id="theme-toggle" class="neu-toggle w-14 h-8 relative" title="{{ __('messages.dark_mode') }}">
                <span id="toggle-dot" class="absolute top-[3px] start-[3px] w-[22px] h-[22px] rounded-full transition-all" style="background:var(--accent);"></span>
            </button>

            <button type="button" class="neu-btn w-10 h-10 grid place-items-center relative" title="{{ __('messages.wishlist') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 0 1 0-6.364Z"/></svg>
            </button>

            <a href="{{ route('cart.index') }}" class="neu-btn w-10 h-10 grid place-items-center relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                <span id="cart-count" class="absolute -top-1 -end-1 rounded-full text-[10px] font-bold w-4 h-4 grid place-items-center" style="background:var(--accent); color:var(--accent-contrast);">{{ $cartCount }}</span>
            </a>
        </div>
    </div>
</header>
