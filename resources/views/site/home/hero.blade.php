{{-- هیرو مینیمال — تک‌هیرو بدون اسلایدر: متن + تصویر، محتوا از اولین اسلاید فعال ادمین --}}
<section class="hero-minimal">
    <div class="hero-minimal-inner{{ $heroSlide && $heroSlide->imageUrl() ? '' : ' hero-minimal-inner--solo' }}">

        @if($heroSlide)
            <div class="hero-minimal-copy{{ $heroSlide->imageUrl() ? '' : ' hero-minimal-copy--center' }}">
                {{-- بج (اختیاری — اگر ادمین پر کرده باشد) --}}
                @if($heroSlide->trans('badge'))
                    <span class="hero-minimal-badge">{{ $heroSlide->trans('badge') }}</span>
                @endif

                {{-- تیتر — ادمین می‌تواند با <span> بخشی از متن را رنگی کند --}}
                <h1 class="hero-minimal-title">
                    {!! $heroSlide->trans('title') !!}
                </h1>

                @if($heroSlide->trans('desc'))
                    <p class="hero-minimal-desc">
                        {{ $heroSlide->trans('desc') }}
                    </p>
                @endif

                <div class="hero-minimal-actions">
                    <x-cta-button :href="$heroSlide->url !== '#' ? $heroSlide->url : localizedRoute('products.index')" variant="primary">
                        {{ $heroSlide->trans('btn_text') ?: __('messages.view_products') }}
                    </x-cta-button>
                </div>
            </div>

            @if($heroSlide->imageUrl())
                <div class="hero-minimal-media">
                    <img
                        src="{{ $heroSlide->imageUrl() }}"
                        alt="{{ strip_tags($heroSlide->trans('title')) }}"
                        width="720" height="540"
                        fetchpriority="high"
                        {{-- fallback محلی: picsum.photos 403 می‌داد؛ this.onerror=null فقط یک‌بار جایگزینی را مجاز می‌کند --}}
                        onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'"
                    >
                </div>
            @endif
        @else
            {{-- بدون اسلاید فعال: هیروی پیش‌فرض ساده از فایل زبان --}}
            <div class="hero-minimal-copy hero-minimal-copy--center">
                <h1 class="hero-minimal-title">{{ __('messages.hero_default_title') }}</h1>
                <p class="hero-minimal-desc">{{ __('messages.hero_default_desc') }}</p>
                <div class="hero-minimal-actions">
                    <x-cta-button :href="localizedRoute('products.index')" variant="primary">
                        {{ __('messages.view_products') }}
                    </x-cta-button>
                </div>
            </div>
        @endif

    </div>
</section>
