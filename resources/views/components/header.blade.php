@php
    $locales = config('app.available_locales', ['fa' => 'فارسی', 'en' => 'English', 'ar' => 'العربية', 'tr' => 'Türkçe']);
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp

<header class="sticky top-0 z-50 backdrop-blur-md border-b transition-all duration-300"
        style="background: color-mix(in srgb, var(--bg) 85%, transparent); border-color: color-mix(in srgb, var(--shadow-dark) 20%, transparent);">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 flex items-center gap-4 flex-wrap">

        {{-- لوگو --}}
        <a href="{{ route('home') }}" class="neu-flat-sm px-4 py-2 font-extrabold text-lg"
           style="color: var(--accent);">
            {{ __('messages.site_name') }}
        </a>

        {{-- منوی ناوبری --}}
        <nav class="hidden md:flex items-center gap-2 mx-4">
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-full hover:opacity-80">{{ __('messages.home') }}</a>
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-full hover:opacity-80">{{ __('messages.products') }}</a>
        </nav>

        {{-- جستجو --}}
        <form action="{{ route('products.index') }}" method="GET" class="flex-1 min-w-[180px] max-w-md">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}"
                   class="neu-input">
        </form>

        <div class="flex items-center gap-3 ms-auto">

            {{-- سوییچر زبان --}}
            <div class="relative">
                <button data-lang-dropdown-btn class="neu-btn neu-btn-icon"
                        title="{{ __('messages.select_language') ?? 'Language' }}">
                    {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                </button>
                <div data-lang-dropdown-menu class="hidden absolute end-0 mt-2 neu-flat-sm p-2 w-40 z-50">
                    @foreach($locales as $code => $label)
                        <button data-lang-select="{{ $code }}"
                                class="w-full text-start px-3 py-2 rounded-lg hover:opacity-80 {{ ($currentLocale ?? app()->getLocale()) === $code ? 'font-bold' : '' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- تاگل تم --}}
            <button data-theme-toggle class="neu-toggle" title="{{ __('messages.dark_mode') }}">
                <span class="neu-toggle-dot"></span>
            </button>

            {{-- سبد خرید --}}
            <a href="{{ route('cart.index') }}" class="neu-btn neu-btn-icon relative">
                🛒
                @if($cartCount > 0)
                    <span class="absolute -top-1 -end-1 neu-badge" data-cart-count style="padding: 0 6px; font-size: .65rem;">{{ $cartCount }}</span>
                @endif
            </a>

            {{-- حساب کاربری --}}
            <span class="neu-btn neu-btn-primary text-sm">{{ __('messages.login') ?? 'ورود' }}</span>
        </div>
    </div>
</header>
