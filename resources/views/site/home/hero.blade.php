{{-- هیرو — تم Eco-Collectible | تمام‌عرض، تک‌اسلاید، بدون اسلایدر --}}
{{-- منطق داده دست‌نخورده: $heroSlide (badge/title/desc/btn_text/url/image) + فallback زبانی + marquee از فایل زبان --}}
@php
    $marqueeItems = __('messages.hero_marquee');
    if (! is_array($marqueeItems)) {
        $marqueeItems = array_filter(array_map('trim', explode('|', $marqueeItems)));
    }
    $heroTicks = array_slice($marqueeItems, 0, 3);
    $heroCtaUrl = ($heroSlide && $heroSlide->url !== '#') ? $heroSlide->url : localizedRoute('products.index');
    $heroCtaLabel = ($heroSlide && $heroSlide->trans('btn_text')) ? $heroSlide->trans('btn_text') : __('messages.view_products');
@endphp

<section class="hero-minimal eco-hero">
    {{-- لایه‌های پس‌زمینه (تزئینی) --}}
    <div class="eco-hero-bg" aria-hidden="true">
        <span class="eco-hero-glow eco-hero-glow--sand"></span>
        <span class="eco-hero-glow eco-hero-glow--clay"></span>
        <span class="eco-hero-ring"></span>
    </div>

    <div class="eco-hero-shell{{ $heroSlide && $heroSlide->imageUrl() ? '' : ' eco-hero-shell--solo' }}">

        <div class="eco-hero-copy">
            <div class="eco-hero-toprow">
                <span class="eco-hero-kicker">{{ __('messages.hero_kicker') }}</span>

                {{-- بج (اختیاری — اگر ادمین پر کرده باشد) --}}
                @if($heroSlide && $heroSlide->trans('badge'))
                    <span class="eco-tag eco-tag--clay">{{ $heroSlide->trans('badge') }}</span>
                @endif
            </div>

            {{-- تیتر — ادمین می‌تواند با <span> روی کلمه هایلایت بکشد --}}
            <h1 class="eco-hero-title">
                @if($heroSlide)
                    {!! $heroSlide->trans('title') !!}
                @else
                    {{ __('messages.hero_default_title') }}
                @endif
            </h1>

            @if(($heroSlide && $heroSlide->trans('desc')) || !$heroSlide)
                <p class="eco-hero-desc">
                    @if($heroSlide)
                        {{ $heroSlide->trans('desc') }}
                    @else
                        {{ __('messages.hero_default_desc') }}
                    @endif
                </p>
            @endif

            <div class="eco-hero-actions">
                <a href="{{ $heroCtaUrl }}" class="eco-btn eco-btn--clay eco-btn--lg eco-hero-cta">
                    <span>{{ $heroCtaLabel }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                </a>
                <a href="{{ localizedRoute('products.index') }}" class="eco-btn eco-btn--ghost eco-btn--lg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.8"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.8"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.8"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.8"/></svg>
                    <span>{{ __('messages.categories') }}</span>
                </a>
            </div>

            {{-- ردیف اطمینان (سه مورد اول نوار متحرک) --}}
            @if(! empty($heroTicks))
                <ul class="eco-hero-ticks">
                    @foreach($heroTicks as $tick)
                        <li>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.4 12.3 2.4 2.4 4.8-5.2"/></svg>
                            <span>{{ $tick }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if($heroSlide && $heroSlide->imageUrl())
            <div class="eco-hero-media">
                <span class="eco-hero-mat" aria-hidden="true"></span>
                <img
                    src="{{ $heroSlide->imageUrl() }}"
                    alt="{{ strip_tags($heroSlide->trans('title')) }}"
                    width="720" height="540"
                    fetchpriority="high"
                    {{-- fallback محلی: picsum.photos 403 می‌داد؛ this.onerror=null فقط یک‌بار جایگزینی را مجاز می‌کند --}}
                    onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'"
                >
                <span class="eco-hero-stamp" aria-hidden="true">{{ __('messages.hero_sticker') }}</span>
                {{-- کارت نمونهٔ شناور (امضای «برچسب انبار») --}}
                <span class="eco-hero-specimen" aria-hidden="true">
                    <span class="eco-hero-specimen-hole"></span>
                    <span class="eco-hero-specimen-id" dir="ltr">#ECO — <span class="hero-num">01</span></span>
                    <span class="eco-hero-specimen-sep"></span>
                    <span class="eco-hero-specimen-text">{{ __('messages.hero_sticker') }}</span>
                </span>
            </div>
        @endif

    </div>

    {{-- نوار متحرک (marquee) — آیتم‌ها از فایل زبان --}}
    @if(! empty($marqueeItems))
        <div class="eco-hero-marquee">
            <div class="eco-hero-marquee-track">
                @for($copy = 0; $copy < 2; $copy++)
                    <div class="eco-hero-marquee-group" @if($copy === 1) aria-hidden="true" @endif>
                        @foreach($marqueeItems as $item)
                            <span class="eco-hero-marquee-item">{{ $item }}</span>
                            <span class="eco-hero-marquee-sep" aria-hidden="true">✳</span>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    @endif
</section>
