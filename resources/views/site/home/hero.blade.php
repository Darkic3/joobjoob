<section class="hero-fullbleed relative mb-12 overflow-hidden">

    {{-- پس‌زمینه: گرید و هاله روی خود .hero-swiper (::before / ::after) پیاده شده — div جدا لازم نیست --}}

    <div class="swiper hero-swiper relative z-10">

        <div class="swiper-wrapper">

            @forelse($slides as $slide)

                <div class="swiper-slide">

                    <div
                        class="grid lg:grid-cols-[55fr_45fr] items-center gap-[var(--gap)] p-[var(--gap)] md:p-10 lg:p-14 min-h-[600px] md:min-h-[680px] lg:min-h-[720px]"
                    >

                        {{-- =====================================================
                             TEXT CONTENT
                        ====================================================== --}}
                        <div
                            class="hero-content order-2 lg:order-1 z-10 text-center lg:text-start"
                        >

                            {{-- Badge --}}
                            @if($slide->trans('badge'))
                                <span
                                    class="hero-anim hero-badge badge-new inline-flex items-center gap-1.5 mb-5 px-4 py-1.5 text-xs"
                                >
                                    <span aria-hidden="true">✦</span>
                                    {{ $slide->trans('badge') }}
                                </span>
                            @endif


                            {{-- Title (سایز/فاصله توسط .hero-title در CSS کنترل می‌شود) --}}
                            <h1
                                class="hero-anim hero-title"
                            >
                                {!! $slide->trans('title') !!}
                            </h1>


                            {{-- Description --}}
                            <p
                                class="hero-anim hero-desc text-sm md:text-base lg:text-lg mb-6 max-w-xl mx-auto lg:mx-0 leading-relaxed"
                                style="color: var(--text-secondary);"
                            >
                                {{ $slide->trans('desc') }}
                            </p>


                            {{-- =========================
                                 PRICE
                            ========================== --}}
                            @if($slide->price > 0)

                                <div
                                    class="hero-anim hero-price flex items-end justify-center lg:justify-start gap-3 mb-7"
                                >

                                    {{-- Old Price --}}
                                    @if($slide->old_price > $slide->price)

                                        <span
                                            class="mono text-sm md:text-base line-through mb-1"
                                            style="color: var(--text-secondary);"
                                            dir="ltr"
                                        >
                                            {{ number_format($slide->old_price) }}
                                            {{ $slide->currency === 'IRT'
                                                ? (app()->getLocale() === 'fa' ? 'تومان' : 'IRT')
                                                : $slide->currency
                                            }}
                                        </span>

                                    @endif


                                    {{-- Current Price --}}
                                    <span
                                        class="mono font-black text-3xl md:text-4xl"
                                        style="color: var(--accent);"
                                        dir="ltr"
                                    >
                                        {{ number_format($slide->price) }}

                                        <small
                                            class="text-xs md:text-sm font-bold"
                                            style="color: var(--text-secondary);"
                                        >
                                            {{ $slide->currency === 'IRT'
                                                ? (app()->getLocale() === 'fa' ? 'تومان' : 'IRT')
                                                : $slide->currency
                                            }}
                                        </small>
                                    </span>

                                </div>

                            @endif


                            {{-- =========================
                                 CTA BUTTONS
                            ========================== --}}
                            <div
                                class="hero-anim hero-btns flex flex-wrap items-center justify-center lg:justify-start gap-4"
                            >

                                {{-- Primary CTA --}}
                                <a
                                    href="{{ $slide->url }}"
                                    class="neu-btn neu-btn-primary px-8 py-3.5 font-bold text-sm shadow-lg hover:shadow-xl transition-all"
                                >
                                    {{ $slide->trans('btn_text')
                                        ?: (app()->getLocale() === 'fa'
                                            ? 'مشاهده محصولات'
                                            : 'View Products')
                                    }}
                                </a>


                                {{-- Secondary CTA --}}
                                <a
                                    href="#deals"
                                    class="neu-btn px-8 py-3.5 font-bold text-sm"
                                >
                                    {{ app()->getLocale() === 'fa'
                                        ? 'پیشنهادات ویژه 🔥'
                                        : 'Special Deals 🔥'
                                    }}
                                </a>

                            </div>


                            {{-- =========================
                                 TRUST / SOCIAL PROOF
                            ========================== --}}
                            <div
                                class="hero-anim hero-trust mt-8 flex flex-wrap items-center justify-center lg:justify-start gap-5"
                            >

                                {{-- Rating --}}
                                @if($slide->hasRating())

                                    <div class="inline-flex items-center gap-3 neu-flat-sm px-4 py-2.5 rounded-2xl">

                                        {{-- ستاره‌ها --}}
                                        <div
                                            class="hero-rating-stars flex items-center gap-[3px] text-base leading-none"
                                            aria-hidden="true"
                                        >
                                            @for($s = 1; $s <= 5; $s++)
                                                <span class="{{ $s <= (int) round((float) $slide->rating) ? 'is-filled' : '' }}">★</span>
                                            @endfor
                                        </div>

                                        <div
                                            class="w-px h-8"
                                            style="background: var(--shadow-dark);"
                                            aria-hidden="true"
                                        ></div>

                                        <div
                                            class="text-start"
                                            dir="auto"
                                        >

                                            <div
                                                class="text-[11px] font-bold mb-1 leading-none"
                                                style="color: var(--text-secondary);"
                                            >
                                                {{ app()->getLocale() === 'fa'
                                                    ? 'امتیاز کاربران'
                                                    : 'User Rating'
                                                }}
                                            </div>

                                            <div
                                                class="mono font-black text-lg leading-none"
                                                style="color: var(--accent);"
                                            >
                                                <bdi dir="ltr">
                                                    {{ $slide->rating }}
                                                </bdi>

                                                <span
                                                    class="text-sm"
                                                    style="color: var(--text-secondary);"
                                                >
                                                    /
                                                </span>

                                                <bdi dir="ltr">
                                                    5.0
                                                </bdi>
                                            </div>

                                        </div>

                                    </div>

                                @endif


                                {{-- Viewer Count --}}
                                @if($slide->viewers > 0)

                                    <div
                                        class="flex items-center gap-2 text-[11px] font-semibold"
                                        style="color: var(--text-secondary);"
                                    >

                                        <span class="live-dot"></span>

                                        <span
                                            class="live-viewers mono"
                                            dir="ltr"
                                        >
                                            {{ $slide->viewers }}
                                        </span>

                                        <span>
                                            {{ app()->getLocale() === 'fa'
                                                ? 'نفر در حال مشاهده'
                                                : 'viewing now'
                                            }}
                                        </span>

                                    </div>

                                @endif


                                {{-- Trust Items (قابل تنظیم برای هر اسلاید — ۰ تا ۵ آیتم) --}}
                                @foreach($slide->trustItems() as $trust)

                                    <div class="hidden sm:flex items-center gap-2">

                                        <span
                                            class="neu-icon-circle-sm w-8 h-8 grid place-items-center text-xs font-bold"
                                            style="color: var(--accent);"
                                        >
                                            {{ $trust['icon'] }}
                                        </span>

                                        <span
                                            class="text-xs font-bold"
                                            style="color: var(--text-secondary);"
                                        >
                                            {{ $trust['text'] }}
                                        </span>

                                    </div>

                                @endforeach

                            </div>

                        </div>


                        {{-- =====================================================
                             PRODUCT SHOWCASE
                        ====================================================== --}}
                        <div
                            class="hero-anim hero-image-wrap order-1 lg:order-2 relative flex justify-center items-center min-h-[420px] lg:min-h-[600px]"
                        >

                            {{-- Product Ambient Glow --}}
                            <div
                                class="hero-product-glow"
                                aria-hidden="true"
                            ></div>


                            {{-- Orbital Ring --}}
                            <div
                                class="hero-stage-ring"
                                aria-hidden="true"
                            ></div>


                            {{-- Decorative Particles --}}
                            <div
                                class="hero-particles"
                                aria-hidden="true"
                            >
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>


                            {{-- =========================
                                 DISCOUNT BADGE
                            ========================== --}}
                            @if($slide->old_price > $slide->price)

                                <span
                                    class="badge-urgency neu-pulse absolute top-[10%] start-[5%] z-20"
                                >

                                    <span aria-hidden="true">🔥</span>

                                    <bdi dir="ltr">
                                        {{ round((($slide->old_price - $slide->price) / $slide->old_price) * 100) }}٪
                                    </bdi>

                                    {{ app()->getLocale() === 'fa'
                                        ? 'تخفیف'
                                        : 'OFF'
                                    }}

                                </span>

                            @endif


                            {{-- =========================
                                 PRODUCT IMAGE
                            ========================== --}}
                            <div
                                class="hero-product-media relative z-10"
                            >

                                @if(filter_var($slide->image, FILTER_VALIDATE_URL))

                                    <img
                                        src="{{ $slide->image }}"
                                        class="hero-product-image"
                                        alt="{{ strip_tags($slide->trans('title')) }}"
                                    >

                                @else

                                    <img
                                        src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $slide->image) }}"
                                        class="hero-product-image"
                                        alt="{{ strip_tags($slide->trans('title')) }}"
                                        onerror="this.src='https://picsum.photos/seed/hero-fallback/600/600'"
                                    >

                                @endif

                            </div>


                            {{-- =========================
                                 (باکس‌های شناور امتیاز و بازدیدکنندگان حذف شدند —
                                 هر دو در ناحیهٔ متنی کنار CTA نمایش داده می‌شوند تا تکراری نباشند)
                            ========================== --}}

                        </div>

                    </div>

                </div>

            @empty

                {{-- =========================
                     EMPTY STATE
                ========================== --}}
                <div class="swiper-slide">

                    <div
                        class="min-h-[600px] flex items-center justify-center text-center"
                    >

                        <div class="space-y-3">

                            <div class="text-4xl">
                                ✦
                            </div>

                            <p
                                class="text-sm font-semibold"
                                style="color: var(--text-secondary);"
                            >
                                {{ app()->getLocale() === 'fa'
                                    ? 'هنوز بنری تعریف نشده است.'
                                    : 'No hero slides available.'
                                }}
                            </p>

                        </div>

                    </div>

                </div>

            @endforelse

        </div>


        {{-- =====================================================
             SWIPER PAGINATION
        ====================================================== --}}
        <div
            class="swiper-pagination hero-pagination !bottom-6"
        ></div>


        {{-- =====================================================
             SWIPER NAVIGATION
        ====================================================== --}}
        <div
            class="swiper-button-next hero-navigation"
            aria-label="{{ app()->getLocale() === 'fa' ? 'اسلاید بعدی' : 'Next slide' }}"
        ></div>

        <div
            class="swiper-button-prev hero-navigation"
            aria-label="{{ app()->getLocale() === 'fa' ? 'اسلاید قبلی' : 'Previous slide' }}"
        ></div>

    </div>

</section>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swiper !== 'undefined') {
                const heroSwiperEl = document.querySelector('.hero-swiper');
                if (heroSwiperEl) {
                    new Swiper(heroSwiperEl, {
                        // جهت از سند گرفته می‌شود تا در زبان‌های LTR (انگلیسی و…) درست کار کند
                        rtl: document.documentElement.dir === 'rtl',
                        effect: 'fade',
                        fadeEffect: {crossFade: true},
                        speed: 900, // کمی سریع‌تر برای حس پویایی بیشتر
                        loop: true,
                        autoplay: {
                            delay: 6500,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true,
                        },
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        // watchSlidesProgress: true,
                    });
                }
            }

            // اسکریپت تغییر زنده تعداد بازدیدکنندگان
            // (پشتیبانی از ارقام فارسی: localize-digits.js متن را به ارقام فارسی تبدیل کرده است)
            const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
            const toAsciiDigits = s => s.replace(/[۰-۹]/g, d => String(FA_DIGITS.indexOf(d)));
            const viewersElements = document.querySelectorAll('.live-viewers');
            if (viewersElements.length > 0) {
                setInterval(() => {
                    viewersElements.forEach(el => {
                        let current = parseInt(toAsciiDigits(el.innerText).replace(/[^0-9]/g, '')) || 40;
                        let change = Math.floor(Math.random() * 7) - 3;
                        let newValue = Math.max(20, current + change);

                        if (current !== newValue) {
                            el.innerText = newValue;
                            el.classList.add('count-pop');
                            setTimeout(() => el.classList.remove('count-pop'), 300);
                        }
                    });
                }, 3000);
            }
        });
    </script>
@endpush
