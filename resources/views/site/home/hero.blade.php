<section class="hero-fullbleed relative mb-12 overflow-hidden">

    {{-- حالت جشن: بارش کانفتی روی هیرو + بنر تخفیف (از تنظیمات — festive_mode / festive_style / festive_badge_text) --}}
    @if(setting('festive_mode'))
        <canvas id="festive-confetti" class="festive-confetti" data-style="{{ setting('festive_style', 'gold') }}" aria-hidden="true"></canvas>

        {{-- بنر/بج تخفیف جشن — متن از تنظیمات، با fallback به زبان جاری --}}
        <div class="festive-badge" role="status">
            <svg class="festive-badge-icon" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.1 6.9L21 11l-6.9 2.1L12 20l-2.1-6.9L3 11l6.9-2.1z"/></svg>
            <span>{{ settingOverride('festive_badge_text', __('messages.festive_badge')) }}</span>
        </div>
    @endif

    {{-- پس‌زمینه: گرید و هاله روی خود .hero-swiper (::before / ::after) پیاده شده — div جدا لازم نیست --}}

    <div class="swiper hero-swiper relative z-10">

        <div class="swiper-wrapper">

            @forelse($slides as $slide)

                <div class="swiper-slide">

                    <div
                        class="grid lg:grid-cols-[55fr_45fr] items-center gap-[var(--gap)] p-[var(--gap)] md:p-10 lg:p-14 min-h-0 md:min-h-[680px] lg:min-h-[720px]"
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
                                    <span aria-hidden="true">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/></svg>
                                    </span>
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

                                    {{-- Old Price — Vazirmatn برای ارقام فارسی (مونو گلیف فارسی ندارد) --}}
                                    @if($slide->old_price > $slide->price)

                                        <span
                                            class="hero-old-price mb-1"
                                            dir="ltr"
                                        >
                                            {{ number_format($slide->old_price) }}
                                            {{ $slide->currencyLabel() }}
                                        </span>

                                    @endif


                                    {{-- Current Price — Vazirmatn 900، بزرگ‌ترین عنصر متن در حالت میانه --}}
                                    <span
                                        class="hero-price-num"
                                        dir="ltr"
                                    >
                                        {{ number_format($slide->price) }}

                                        <small>
                                            {{ $slide->currencyLabel() }}
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

                                {{-- Primary CTA — کامپوننت cta-button: حلقهٔ «بازهٔ اعتماد» + دایرهٔ فلش --}}
                                <x-cta-button :href="$slide->url" variant="primary">
                                    {{ $slide->trans('btn_text') ?: __('messages.view_products') }}
                                </x-cta-button>

                                {{-- Secondary CTA — همان کامپوننت با تاکید کمتر (variant=secondary)؛
                                     شعلهٔ SVG هم‌شکلِ شعلهٔ بخش پیشنهادات (جایگزین ایموجی 🔥) --}}
                                <x-cta-button href="#deals" variant="secondary">
                                    <svg class="cta-ring-flame" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22c4.4 0 7-2.8 7-6.5 0-3.5-2.3-5.5-3.7-7.3-.5-.7-1.2-.2-1 .6.2.9.3 1.7-.1 2.5-.7-.6-1.5-1.6-1.9-3C11.5 5.3 12 3.5 12 2c-1.8 1.4-3 3.4-3.4 5.3C8.2 9.4 9 11 10 12.5 8.5 12 7 10.8 7 8.5 5.4 10.7 5 13 5 15.5 5 19.2 7.6 22 12 22z"/></svg>
                                    {{ __('messages.special_deals') }}
                                </x-cta-button>

                            </div>


                            {{-- =========================
                                 TRUST / SOCIAL PROOF
                            ========================== --}}
                            <div
                                class="hero-anim hero-trust mt-8 flex flex-wrap items-center justify-center lg:justify-start gap-5"
                            >

                                {{-- Rating --}}
                                @if($slide->hasRating())

                                    <div class="inline-flex items-center gap-3">

                                        {{-- ستاره‌ها — درصد پر شدن هر ستاره از HeroSlide::ratingStars() --}}
                                        <div
                                            class="hero-rating-stars flex items-center gap-[3px] text-base leading-none"
                                            aria-hidden="true"
                                        >
                                            @foreach($slide->ratingStars() as $fill)
                                                <span class="hero-star">
                                                    <span class="hero-star-bg">★</span>
                                                    <span class="hero-star-fill" style="width: {{ $fill }}%;">★</span>
                                                </span>
                                            @endforeach
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
                                                {{ __('messages.user_rating') }}
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
                                            {{ __('messages.viewing_now') }}
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

                            {{-- Product Ambient Glow — لفاف پارالاکس (لایهٔ عمق ۰.۳)؛ خود هاله انیمیشن CSS دارد
                                 پس ترجمهٔ ماوس روی لفاف اعمال می‌شود تا با انیمیشن تداخل نکند --}}
                            <div
                                class="hero-glow-parallax"
                                aria-hidden="true"
                            >
                                <div class="hero-product-glow"></div>
                            </div>


                            {{-- Orbital Ring — لفاف پارالاکس (لایهٔ عمق ۰.۵۵)؛ چرخش ۴۰ثانیه‌ای روی خود حلقه می‌ماند --}}
                            <div
                                class="hero-ring-parallax"
                                aria-hidden="true"
                            >
                                <div class="hero-stage-ring"></div>
                            </div>


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


                            {{-- سکوی شیشه‌ای + بازتاب کف زیر محصول (تزئینی، عمق‌دهندهٔ صحنه) --}}
                            <div class="hero-stage" aria-hidden="true">
                                <div class="hero-stage-plate"></div>
                                <div class="hero-stage-reflection"></div>
                            </div>


                            {{-- =========================
                                 DISCOUNT BADGE
                            ========================== --}}
                            @if($slide->old_price > $slide->price)

                                <span
                                    class="badge-urgency neu-pulse absolute top-[10%] start-[5%] z-20"
                                >

                                    <span aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.4 0 7-2.8 7-6.5 0-3.5-2.3-5.5-3.7-7.3-.5-.7-1.2-.2-1 .6.2.9.3 1.7-.1 2.5-.7-.6-1.5-1.6-1.9-3C11.5 5.3 12 3.5 12 2c-1.8 1.4-3 3.4-3.4 5.3C8.2 9.4 9 11 10 12.5 8.5 12 7 10.8 7 8.5 5.4 10.7 5 13 5 15.5 5 19.2 7.6 22 12 22z"/></svg>
                                    </span>

                                    <bdi dir="ltr">
                                        {{ $slide->discountPercent() }}٪
                                    </bdi>

                                    {{ __('messages.discount_off') }}

                                </span>

                            @endif


                            {{-- =========================
                                 PRODUCT IMAGE
                            ========================== --}}
                            <div
                                class="hero-product-media relative z-10"
                            >

                                <img
                                    src="{{ $slide->imageUrl() }}"
                                    class="hero-product-image"
                                    alt="{{ strip_tags($slide->trans('title')) }}"
                                    @if($loop->first) fetchpriority="high" @endif
                                    onerror="this.src='https://picsum.photos/seed/hero-fallback/600/600'"
                                >

                                {{-- درخشش گذرای مورب هنگام ورود اسلاید (Micro-interaction) --}}
                                <span class="hero-shine" aria-hidden="true"></span>

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

                            <div class="text-4xl" aria-hidden="true">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/></svg>
                            </div>

                            <p
                                class="text-sm font-semibold"
                                style="color: var(--text-secondary);"
                            >
                                {{ __('messages.no_slides') }}
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


        {{-- شمارندهٔ اسلاید (تزئینی — aria-hidden) --}}
        @if(count($slides) > 1)
            <div class="hero-counter" aria-hidden="true">
                <span class="hero-counter-current mono">01</span>
                <span class="hero-counter-sep">/</span>
                <span class="hero-counter-total mono">{{ str_pad(count($slides), 2, '0', STR_PAD_LEFT) }}</span>
            </div>
        @endif


        {{-- =====================================================
             SWIPER NAVIGATION
        ====================================================== --}}
        <div
            class="swiper-button-next hero-navigation"
            aria-label="{{ __('messages.next_slide') }}"
        ></div>

        <div
            class="swiper-button-prev hero-navigation"
            aria-label="{{ __('messages.prev_slide') }}"
        ></div>

    </div>

    {{-- نشانگر اسکرول (دسکتاپ) — دعوت به مرور محتوای پایین --}}
    @if(count($slides) > 0)
        <div class="hero-scroll-cue" aria-hidden="true">
            <span class="hero-scroll-cue-mouse">
                <span class="hero-scroll-cue-wheel"></span>
            </span>
        </div>
    @endif

</section>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // اگر سوییپر (باندل Vite) به هر دلیلی در دسترس نبود یا ساخت آن خطا داد،
            // کلاس hero-init-failed اضافه می‌شود تا CSS محتوا را قابل دیدن کند —
            // هیرو هرگز به‌صورت دائمی نامرئی نمی‌ماند.
            const heroSwiperEl = document.querySelector('.hero-swiper');
            if (heroSwiperEl && typeof Swiper === 'undefined') {
                heroSwiperEl.classList.add('hero-init-failed');
            } else if (heroSwiperEl) {
                try {
                    const heroSwiper = new Swiper(heroSwiperEl, {
                        // جهت از سند گرفته می‌شود تا در زبان‌های LTR (انگلیسی و…) درست کار کند
                        rtl: document.documentElement.dir === 'rtl',
                        effect: 'fade',
                        fadeEffect: {crossFade: true},
                        speed: 900, // کمی سریع‌تر برای حس پویایی بیشتر
                        // ارتفاع هر اسلاید را به محتوای خودش می‌چسباند — اسلاید کوتاه فضای خالی بزرگ زیرش نمی‌گذارد
                        autoHeight: true,
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

                    // دات پیشرفت زمان: در طول هر اسلاید، نوار داخل دات فعال از رویداد
                    // autoplayTimeLeft سوییپر پر می‌شود (روی هاور/توقف متوقف و ادامه می‌یابد)
                    const heroPagination = heroSwiperEl.querySelector('.hero-pagination');
                    const setDotProgress = (fraction) => {
                        const active = heroPagination && heroPagination.querySelector('.swiper-pagination-bullet-active');
                        if (active) active.style.setProperty('--dot-progress', (Math.max(0, Math.min(1, fraction)) * 100) + '%');
                    };
                    heroSwiper.on('slideChange', () => setDotProgress(0));
                    heroSwiper.on('autoplayTimeLeft', (swiper, timeLeft) => {
                        const delay = (swiper.params.autoplay && swiper.params.autoplay.delay) || 6500;
                        setDotProgress(1 - timeLeft / delay);
                    });

                    // شمارندهٔ اسلاید (01 / 03) — با realIndex تا در حالت loop ایندکس تکراری نشمارد
                    const heroCounter = heroSwiperEl.querySelector('.hero-counter');
                    if (heroCounter) {
                        const heroCounterCurrent = heroCounter.querySelector('.hero-counter-current');
                        const heroCounterTotal = heroCounter.querySelector('.hero-counter-total');
                        const pad2 = (n) => String(n).padStart(2, '0');
                        const setHeroCounter = () => {
                            const real = (typeof heroSwiper.realIndex === 'number') ? heroSwiper.realIndex : heroSwiper.activeIndex;
                            if (heroCounterCurrent) heroCounterCurrent.textContent = pad2(real + 1);
                            if (heroCounterTotal) heroCounterTotal.textContent = pad2(heroSwiper.slides.length);
                        };
                        setHeroCounter();
                        heroSwiper.on('slideChange', setHeroCounter);
                    }

                    // ============================================================
                    // پارالاکس چندلایه (جهت A — فاز ۱): فقط دسکتاپ و بدون reduced-motion
                    // لایه‌های عمق: محصول (۱.۰)، حلقه‌ها (۰.۵۵)، هاله (۰.۳)، ذرات (۰.۲)
                    // gsap.matchMedia شرایط را مدیریت می‌کند: هنگام خروج از شرط،
                    // پاکسازی کامل انجام می‌شود (killing تویین‌ها + حذف لیسنرها)
                    // ============================================================
                    if (typeof gsap !== 'undefined') {
                        const heroMM = gsap.matchMedia();
                        heroMM.add('(min-width: 1024px) and (prefers-reduced-motion: no-preference)', () => {
                            const MAX = 28; // حداکثر جابه‌جایی (px) برای عمیق‌ترین لایه
                            let lastNX = 0, lastNY = 0;
                            let layers = [];

                            const getLayers = () => {
                                const slide = heroSwiperEl.querySelector('.swiper-slide-active');
                                if (!slide) return [];
                                const q = (sel) => slide.querySelector(sel);
                                return [
                                    { el: q('.hero-product-media'), depth: 1.0 },
                                    { el: q('.hero-ring-parallax'), depth: 0.55 },
                                    { el: q('.hero-glow-parallax'), depth: 0.3 },
                                    { el: q('.hero-particles'), depth: 0.2 },
                                ].filter(l => l.el);
                            };

                            // پاکسازی تویین‌های یک لایه و بازگرداندن آن به حالت طبیعی
                            // (در GSAP 3.15 تابع quickTo فقط .tween دارد — kill روی tween صدا زده می‌شود)
                            const killLayer = (l) => {
                                if (l.xTo && l.xTo.tween) l.xTo.tween.kill();
                                if (l.yTo && l.yTo.tween) l.yTo.tween.kill();
                                if (l.el) gsap.set(l.el, { x: 0, y: 0, willChange: 'auto' });
                            };

                            const initLayers = () => {
                                // پاکسازی تویین‌های قبلی (اسلاید قبلی) — تویین سرگردان نماند
                                layers.forEach(killLayer);
                                layers = getLayers().map(l => ({
                                    ...l,
                                    xTo: gsap.quickTo(l.el, 'x', { duration: 0.9, ease: 'power3.out' }),
                                    yTo: gsap.quickTo(l.el, 'y', { duration: 0.9, ease: 'power3.out' }),
                                }));
                                // بدون حرکت ماوس (مثلاً بعد از تعویض اسلاید) موقعیت قبلی بلافاصله اعمال شود
                                layers.forEach(l => {
                                    gsap.set(l.el, { willChange: 'transform' });
                                    gsap.set(l.el, { x: lastNX * MAX * l.depth, y: lastNY * MAX * l.depth });
                                });
                            };

                            const onMove = (e) => {
                                lastNX = (e.clientX / window.innerWidth) * 2 - 1; // -1..1
                                lastNY = (e.clientY / window.innerHeight) * 2 - 1;
                                layers.forEach(l => {
                                    l.xTo(lastNX * MAX * l.depth);
                                    l.yTo(lastNY * MAX * l.depth);
                                });
                            };

                            initLayers();
                            heroSwiper.on('slideChange', initLayers);
                            window.addEventListener('mousemove', onMove, { passive: true });

                            return () => {
                                layers.forEach(killLayer);
                                heroSwiper.off('slideChange', initLayers);
                                window.removeEventListener('mousemove', onMove);
                            };
                        });
                    }
                } catch (err) {
                    heroSwiperEl.classList.add('hero-init-failed');
                }
            }

            // CTA ثانویهٔ هیرو: اسکرول نرم به بخش پیشنهادات + هایلایت لحظه‌ای (حلقهٔ آکسانت)
            // با event delegation — چون سوییپر با loop گره‌های اسلاید را جابه‌جا/تکثیر می‌کند،
            // بایند مستقیم به یک گرهٔ خاص ناامن است
            document.addEventListener('click', (e) => {
                const link = e.target.closest && e.target.closest('a[href="#deals"]');
                if (!link) return;
                const target = document.querySelector('#deals');
                if (!target) return; // اگر بخش نیست، لینک پیش‌فرض اجرا شود
                e.preventDefault();
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
                target.classList.remove('deals-highlight');
                void target.offsetWidth; // ری‌استارت انیمیشن هایلایت
                target.classList.add('deals-highlight');
                setTimeout(() => target.classList.remove('deals-highlight'), 2500);
            });

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
