{{-- هیرو — تم Eco-Collectible (فاز ۴، مرحله ۱) | تمام‌عرض، تک‌اسلاید، بدون اسلایدر --}}
{{-- منطق داده دست‌نخورده: $heroSlide (badge/title/desc/btn_text/url/image) + فallback زبانی + marquee از فایل زبان --}}
@php
    $marqueeItems = __('messages.hero_marquee');
    if (! is_array($marqueeItems)) {
        $marqueeItems = array_filter(array_map('trim', explode('|', $marqueeItems)));
    }
@endphp

<section class="hero-minimal eco-hero">
    <div class="eco-hero-shell{{ $heroSlide && $heroSlide->imageUrl() ? '' : ' eco-hero-shell--solo' }}">

        @if($heroSlide)
            <div class="eco-hero-copy">
                <span class="eco-hero-kicker">{{ __('messages.hero_kicker') }}</span>

                {{-- بج (اختیاری — اگر ادمین پر کرده باشد) --}}
                @if($heroSlide->trans('badge'))
                    <span class="eco-tag eco-tag--clay">{{ $heroSlide->trans('badge') }}</span>
                @endif

                {{-- تیتر — ادمین می‌تواند با <span> روی کلمه هایلایت بکشد --}}
                <h1 class="eco-hero-title">
                    {!! $heroSlide->trans('title') !!}
                </h1>

                @if($heroSlide->trans('desc'))
                    <p class="eco-hero-desc">
                        {{ $heroSlide->trans('desc') }}
                    </p>
                @endif

                <div class="eco-hero-actions">
                    <a href="{{ $heroSlide->url !== '#' ? $heroSlide->url : localizedRoute('products.index') }}"
                       class="eco-btn eco-btn--clay eco-btn--lg">
                        <span>{{ $heroSlide->trans('btn_text') ?: __('messages.view_products') }}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                    </a>
                </div>
            </div>

            @if($heroSlide->imageUrl())
                <div class="eco-hero-media">
                    <img
                        src="{{ $heroSlide->imageUrl() }}"
                        alt="{{ strip_tags($heroSlide->trans('title')) }}"
                        width="720" height="540"
                        fetchpriority="high"
                        {{-- fallback محلی: picsum.photos 403 می‌داد؛ this.onerror=null فقط یک‌بار جایگزینی را مجاز می‌کند --}}
                        onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'"
                    >
                    <span class="eco-hero-stamp" aria-hidden="true">{{ __('messages.hero_sticker') }}</span>
                </div>
            @endif
        @else
            {{-- بدون اسلاید فعال: هیروی پیش‌فرض ساده از فایل زبان --}}
            <div class="eco-hero-copy">
                <span class="eco-hero-kicker">{{ __('messages.hero_kicker') }}</span>
                <h1 class="eco-hero-title">{{ __('messages.hero_default_title') }}</h1>
                <p class="eco-hero-desc">{{ __('messages.hero_default_desc') }}</p>
                <div class="eco-hero-actions">
                    <a href="{{ localizedRoute('products.index') }}" class="eco-btn eco-btn--clay eco-btn--lg">
                        <span>{{ __('messages.view_products') }}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                    </a>
                </div>
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
