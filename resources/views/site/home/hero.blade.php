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

                    <div class="hero-fb-inner min-h-0 md:min-h-[680px] lg:min-h-[720px]">

                        {{-- =====================================================
                             عکس تمام‌کادر — پس‌زمینهٔ کامل اسلاید (نه ستون کناری)
                             همون imageUrl() قبلی؛ برای محصول/دسته/مقاله/استاتیک یکسان کار می‌کنه
                        ====================================================== --}}
                        <div class="hero-fb-media" aria-hidden="true">
                            <img
                                src="{{ $slide->imageUrl() }}"
                                alt=""
                                @if($loop->first) fetchpriority="high" @endif
                                {{-- fallback محلی: picsum.photos 403 می‌داد و حلقهٔ بی‌نهایت ریکوئست می‌ساخت؛
                                     this.onerror=null فقط یک‌بار جایگزینی را مجاز می‌کند --}}
                                onerror="this.onerror=null;this.src='{{ asset('images/hero-fallback.svg') }}'"
                            >
                        </div>
                        <div class="hero-fb-overlay" aria-hidden="true"></div>

                        {{-- بج تخفیف — روی خود عکس، سمت مقابل پنل متن --}}
                        @if($slide->old_price > $slide->price)
                            <span class="badge-urgency neu-pulse hero-fb-discount">
                                <span aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.4 0 7-2.8 7-6.5 0-3.5-2.3-5.5-3.7-7.3-.5-.7-1.2-.2-1 .6.2.9.3 1.7-.1 2.5-.7-.6-1.5-1.6-1.9-3C11.5 5.3 12 3.5 12 2c-1.8 1.4-3 3.4-3.4 5.3C8.2 9.4 9 11 10 12.5 8.5 12 7 10.8 7 8.5 5.4 10.7 5 13 5 15.5 5 19.2 7.6 22 12 22z"/></svg>
                                </span>
                                <bdi dir="ltr">{{ $slide->discountPercent() }}٪</bdi>
                                {{ __('messages.discount_off') }}
                            </span>
                        @endif

                        {{-- =====================================================
                             پنل شیشه‌ای متن — دقیقاً همون محتوای hero-content قبلی
                             (بج / تیتر / توضیح / قیمت / CTA / نوار اعتماد) بدون تغییر منطق
                        ====================================================== --}}
                        <div class="hero-fb-panel hero-anim">

                            {{-- Badge --}}
                            @if($slide->trans('badge'))
                                <span class="hero-badge badge-new inline-flex items-center gap-1.5 mb-5 px-4 py-1.5 text-xs">
                                    <span aria-hidden="true">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/></svg>
                                    </span>
                                    {{ $slide->trans('badge') }}
                                </span>
                            @endif

                            {{-- Title (سایز/فاصله توسط .hero-fb-title در CSS کنترل می‌شود) --}}
                            <h1 class="hero-fb-title">
                                {!! $slide->trans('title') !!}
                            </h1>

                            {{-- Description --}}
                            <p class="hero-fb-desc">
                                {{ $slide->trans('desc') }}
                            </p>

                            {{-- =========================
                                 PRICE
                            ========================== --}}
                            @if($slide->price > 0)
                                <div class="hero-fb-price flex items-end gap-3 mb-7">
                                    {{-- Old Price — Vazirmatn برای ارقام فارسی (مونو گلیف فارسی ندارد) --}}
                                    @if($slide->old_price > $slide->price)
                                        <span class="hero-old-price mb-1" dir="ltr">
                                            {{ number_format($slide->old_price) }}
                                            {{ $slide->currencyLabel() }}
                                        </span>
                                    @endif

                                    {{-- Current Price — Vazirmatn 900، بزرگ‌ترین عنصر متن --}}
                                    <span class="hero-price-num" dir="ltr">
                                        {{ number_format($slide->price) }}
                                        <small>{{ $slide->currencyLabel() }}</small>
                                    </span>
                                </div>
                            @endif

                            {{-- =========================
                                 CTA BUTTONS
                            ========================== --}}
                            <div class="hero-btns flex flex-wrap items-center gap-4 mb-6">
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
                                 — فقط اگر حداقل یکی از بخش‌ها (امتیاز / بازدیدکننده / آیتم اعتماد) موجود باشد نمایش داده می‌شود
                            ========================== --}}
                            @if($slide->hasRating() || $slide->viewers > 0 || ! empty($slide->trustItems()))
                            <div class="hero-trust flex flex-wrap items-center gap-5">
                                {{-- Rating --}}
                                @if($slide->hasRating())
                                    <div class="inline-flex items-center gap-3">
                                        {{-- ستاره‌ها — درصد پر شدن هر ستاره از HeroSlide::ratingStars() --}}
                                        <div class="hero-rating-stars flex items-center gap-[3px] text-base leading-none" aria-hidden="true">
                                            @foreach($slide->ratingStars() as $fill)
                                                <span class="hero-star">
                                                    <span class="hero-star-bg">★</span>
                                                    <span class="hero-star-fill" style="width: {{ $fill }}%;">★</span>
                                                </span>
                                            @endforeach
                                        </div>
                                        <div class="mono font-black text-lg leading-none" style="color: var(--accent);">
                                            <bdi dir="ltr">{{ $slide->rating }}</bdi>
                                        </div>
                                    </div>
                                @endif

                                {{-- Viewer Count --}}
                                @if($slide->viewers > 0)
                                    <div class="flex items-center gap-2 text-[11px] font-semibold" style="color: var(--text-secondary);">
                                        <span class="live-dot"></span>
                                        <span class="live-viewers mono" dir="ltr">{{ $slide->viewers }}</span>
                                        <span>{{ __('messages.viewing_now') }}</span>
                                    </div>
                                @endif

                                {{-- Trust Items (قابل تنظیم برای هر اسلاید — ۰ تا ۵ آیتم) --}}
                                @foreach($slide->trustItems() as $trust)
                                    <div class="hidden sm:flex items-center gap-2">
                                        <span class="neu-icon-circle-sm w-8 h-8 grid place-items-center text-xs font-bold" style="color: var(--accent);">
                                            <x-trust-icon :icon="$trust['icon']" />
                                        </span>
                                        <span class="text-xs font-bold" style="color: var(--text-secondary);">{{ $trust['text'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @endif

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
                        // باگ شناخته‌شدهٔ سوییپر: loop:true با effect:'fade' بعد از چند بار جابه‌جایی،
                        // پرچم داخلی animating را گیر می‌اندازد و دکمه‌های قبلی/بعدی دیگر کار نمی‌کنند.
                        // rewind همان چرخش بی‌پایان را بدون اسلایدهای کلون (و بدون این باگ) می‌دهد.
                        rewind: true,
                        autoplay: {
                            delay: 6500,
                            disableOnInteraction: false,
                            // pauseOnMouseEnter حذف شد: در fade، رویداد transitionend روی wrapperEl
                            // شلیک نمی‌شود و اتوپلی هنگام هاور برای همیشه در حالت paused گیر می‌کرد
                            // (تا وقتی موس بیرون می‌رفت). هاور حالا هیچ اثری روی اتوپلی ندارد.
                            // waitForTransition:false — تایمر اتوپلی مستقل از رویداد transition است؛
                            // بعد از هر جابه‌جایی بلافاصله تایمر بعدی تنظیم می‌شود (چرخش بی‌وقفه و نرم).
                            waitForTransition: false,
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
                    });                        // دات پیشرفت زمان: در طول هر اسلاید، نوار داخل دات فعال از رویداد
                        // autoplayTimeLeft سوییپر پر می‌شود
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

                    // توقف اتوپلی در تب پس‌زمینه: مرورگر تایمرهای تب مخفی را throttle می‌کند و
                    // جابه‌جایی‌ای که کسی نمی‌بیند بی‌فایده است؛ با برگشت به تب، چرخه از ابتدای
                    // تأخیر (۶.۵ ثانیه) ادامه می‌یابد. سوییپر خودش visibilitychange را مدیریت می‌کند،
                    // اما چون waitForTransition:false گذاشته‌ایم، pause داخلی‌اش بلافاصله resume
                    // می‌شود و عملاً بی‌اثر است — پس این‌جا با stop/start خودمان انجامش می‌دهیم.
                    let heroHidden = document.hidden;
                    const onHeroVisibility = () => {
                        if (document.hidden) {
                            heroHidden = true;
                            if (heroSwiper.autoplay.running) heroSwiper.autoplay.stop();
                        } else if (heroHidden) {
                            heroHidden = false;
                            heroSwiper.autoplay.start();
                        }
                    };
                    document.addEventListener('visibilitychange', onHeroVisibility);
                    // اگر صفحه در تب پس‌زمینه لود شد، همین ابتدا متوقفش کن
                    if (heroHidden && heroSwiper.autoplay.running) heroSwiper.autoplay.stop();

                    // پارالاکس چندلایهٔ ماوس حذف شد — هیروی تمام‌کادر حالا فقط زوم آرام
                    // سینمایی (Ken Burns) دارد که کاملاً CSS-محور است و توسط همان کلاس
                    // swiper-slide-active که سوییپر خودش مدیریت می‌کند فعال می‌شود.
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
