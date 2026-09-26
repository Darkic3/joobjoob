{{-- هیرو — Playful Brutalism × Neo-Tokyo Minimal | تمام‌عرض، تک‌اسلاید، بدون اسلایدر --}}
@php
    $marqueeItems = __('messages.hero_marquee');
    if (! is_array($marqueeItems)) {
        $marqueeItems = array_filter(array_map('trim', explode('|', $marqueeItems)));
    }
@endphp

<section class="hero-minimal hero-brutal">
    {{-- برچسب مختصات (تزئینی) --}}
    <span class="hero-brutal-coords" aria-hidden="true">SOCK://SHOP — N°01</span>

    <div class="hero-brutal-shell{{ $heroSlide && $heroSlide->imageUrl() ? '' : ' hero-brutal-shell--solo' }}">

        @if($heroSlide)
            <div class="hero-brutal-copy">
                <span class="hero-brutal-kicker">{{ __('messages.hero_kicker') }}</span>

                {{-- بج (اختیاری — اگر ادمین پر کرده باشد) --}}
                @if($heroSlide->trans('badge'))
                    <span class="hero-brutal-badge">{{ $heroSlide->trans('badge') }}</span>
                @endif

                {{-- تیتر — ادمین می‌تواند با <span> روی کلمه ماژیک نئونی بکشد --}}
                <h1 class="hero-brutal-title">
                    {!! $heroSlide->trans('title') !!}
                </h1>

                @if($heroSlide->trans('desc'))
                    <p class="hero-brutal-desc">
                        {{ $heroSlide->trans('desc') }}
                    </p>
                @endif

                <div class="hero-brutal-actions">
                    <x-cta-button variant="brutalist" :href="$heroSlide->url !== '#' ? $heroSlide->url : localizedRoute('products.index')">
                        {{ $heroSlide->trans('btn_text') ?: __('messages.view_products') }}
                    </x-cta-button>
                </div>
            </div>

            @if($heroSlide->imageUrl())
                <div class="hero-brutal-media">
                    <img
                        src="{{ $heroSlide->imageUrl() }}"
                        alt="{{ strip_tags($heroSlide->trans('title')) }}"
                        width="720" height="540"
                        fetchpriority="high"
                        {{-- fallback محلی: picsum.photos 403 می‌داد؛ this.onerror=null فقط یک‌بار جایگزینی را مجاز می‌کند --}}
                        onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'"
                    >
                    <span class="hero-brutal-stamp" aria-hidden="true">{{ __('messages.hero_sticker') }}</span>
                </div>
            @endif
        @else
            {{-- بدون اسلاید فعال: هیروی پیش‌فرض ساده از فایل زبان --}}
            <div class="hero-brutal-copy">
                <span class="hero-brutal-kicker">{{ __('messages.hero_kicker') }}</span>
                <h1 class="hero-brutal-title">{{ __('messages.hero_default_title') }}</h1>
                <p class="hero-brutal-desc">{{ __('messages.hero_default_desc') }}</p>
                <div class="hero-brutal-actions">
                    <x-cta-button variant="brutalist" :href="localizedRoute('products.index')">
                        {{ __('messages.view_products') }}
                    </x-cta-button>
                </div>
            </div>
        @endif

        {{-- دودل‌های تزئینی نو-توکیو --}}
        <svg class="hero-brutal-doodle hero-brutal-doodle--star" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--b-pink, #FF5DA2)" stroke-width="2.6" stroke-linecap="round" aria-hidden="true">
            <path d="M12 2v20M2 12h20M4.9 4.9l14.2 14.2M19.1 4.9 4.9 19.1"/>
        </svg>
        <svg class="hero-brutal-doodle hero-brutal-doodle--squiggle" width="72" height="20" viewBox="0 0 72 20" fill="none" stroke="var(--b-blue, #3A3AF2)" stroke-width="3" stroke-linecap="round" aria-hidden="true">
            <path d="M3 10c6-9 12 9 18 0s12 9 18 0 12 9 18 0 9 6 12 3"/>
        </svg>

    </div>

    {{-- نوار متحرک (marquee) — آیتم‌ها از فایل زبان --}}
    @if(! empty($marqueeItems))
        <div class="hero-brutal-marquee">
            <div class="hero-brutal-marquee-track">
                @for($copy = 0; $copy < 2; $copy++)
                    <div class="hero-brutal-marquee-group" @if($copy === 1) aria-hidden="true" @endif>
                        @foreach($marqueeItems as $item)
                            <span class="hero-brutal-marquee-item">{{ $item }}</span>
                            <span class="hero-brutal-marquee-sep" aria-hidden="true">✳</span>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    @endif
</section>
