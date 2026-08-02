<section class="relative w-full mb-12 overflow-hidden rounded-[var(--radius-lg)]">
    {{-- هاله‌های پس‌زمینه برای عمق دادن به نئومورفیسم --}}
    <div
        class="absolute top-0 end-0 w-96 h-96 bg-[var(--accent)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob pointer-events-none"></div>
    <div
        class="absolute bottom-0 start-0 w-96 h-96 bg-[var(--success)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000 pointer-events-none"></div>

    <div class="swiper hero-swiper rounded-[var(--radius-lg)] relative z-10">
        <div class="swiper-wrapper">
            @forelse($slides as $slide)
                <div class="swiper-slide">
                    <div class="grid lg:grid-cols-2 items-center gap-8 p-6 md:p-12 min-h-[450px] md:min-h-[550px]">

                        {{-- ستون متن --}}
                        <div class="order-2 lg:order-1 z-10 text-center lg:text-start">
                            @if($slide->trans('badge'))
                                <span class="hero-anim hero-badge badge-new inline-block mb-4 px-4 py-1.5 text-xs">
                                    ✦ {{ $slide->trans('badge') }}
                                </span>
                            @endif

                            <h1 class="hero-anim hero-title text-3xl md:text-5xl lg:text-6xl font-black leading-[1.2] mb-6">
                                {!! $slide->trans('title') !!}
                            </h1>

                            <p class="hero-anim hero-desc text-sm md:text-base mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed"
                               style="color: var(--text-secondary);">
                                {{ $slide->trans('desc') }}
                            </p>

                            {{-- دکمه‌ها --}}
                            <div
                                class="hero-anim hero-btns flex flex-wrap items-center justify-center lg:justify-start gap-4">
                                <a href="{{ $slide->url }}"
                                   class="neu-btn neu-btn-primary px-8 py-3.5 font-bold text-sm shadow-lg hover:shadow-xl transition-all">
                                    {{ $slide->trans('btn_text') ?: (app()->getLocale() === 'fa' ? 'مشاهده محصولات' : 'View Products') }}
                                </a>
                                <a href="#deals" class="neu-btn px-8 py-3.5 font-bold text-sm">
                                    {{ app()->getLocale() === 'fa' ? 'پیشنهادات ویژه 🔥' : 'Special Deals 🔥' }}
                                </a>
                            </div>
                        </div>

                        {{-- ستون تصویر با افکت شناوری --}}
                        <div
                            class="hero-anim hero-image-wrap order-1 lg:order-2 relative flex justify-center items-center">
                            {{-- کارت پایه نئومورفیک --}}
                            <div class="neu-flat-lg p-6 md:p-10 w-full max-w-sm relative">
                                <span class="badge-urgency absolute -top-3 start-6 z-20 animate-bounce">
                                    {{ $slide->old_price > $slide->price ? round((($slide->old_price - $slide->price) / $slide->old_price) * 100) . '٪ تخفیف' : 'پیشنهاد ویژه' }}
                                </span>

                                {{-- تصویر با انیمیشن شناور --}}
                                <div class="relative overflow-hidden rounded-2xl float-anim bg-[var(--bg-elevated)]/50">
                                    @if(filter_var($slide->image, FILTER_VALIDATE_URL))
                                        <img src="{{ $slide->image }}" class="w-full rounded-2xl object-contain p-4"
                                             style="aspect-ratio:1/1;" alt="{{ strip_tags($slide->trans('title')) }}">
                                    @else
                                        <img
                                            src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $slide->image) }}"
                                            class="w-full rounded-2xl object-contain p-4"
                                            style="aspect-ratio:1/1;"
                                            alt="{{ strip_tags($slide->trans('title')) }}"
                                            onerror="this.src='https://picsum.photos/seed/hero-fallback/600/600'">
                                    @endif
                                </div>

                                {{-- کارت شناور روی تصویر (فقط اگر امتیاز فعال باشد) --}}
                                @if($slide->hasRating())
                                    <div
                                        class="absolute -bottom-4 -end-4 neu-flat-sm p-3 flex items-center gap-3 z-20 backdrop-blur-sm"
                                        style="background: color-mix(in srgb, var(--bg-elevated) 90%, transparent);">
                                        <div
                                            class="neu-icon-circle-sm w-10 h-10 grid place-items-center text-sm flex-shrink-0">
                                            ⭐
                                        </div>

                                        {{-- استفاده از dir="auto" برای کانتینر و dir="ltr" برای اعداد جهت جلوگیری از برعکس شدن --}}
                                        <div class="text-start" dir="auto">
                                            <p class="text-[10px] font-bold" style="color: var(--text-secondary);">
                                                {{ app()->getLocale() === 'fa' ? 'امتیاز کاربران' : 'User Rating' }}
                                            </p>
                                            <p class="text-sm font-black" style="color: var(--accent);" dir="ltr">
                                                @if($slide->rating)
                                                    {{ app()->getLocale() === 'fa' ? "{$slide->rating} از ۵.۰" : "{$slide->rating} / 5.0" }}
                                                @else
                                                    No Rate
{{--                                                    {{ app()->getLocale() === 'fa' ? '۴.۹ از ۵.۰' : '4.9 / 5.0' }}--}}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                {{-- قیمت زیر تصویر --}}
                                {{-- قیمت و بازدید زیر تصویر (فقط اگر قیمت بیشتر از صفر باشد نمایش داده می‌شود) --}}
                                <div class="mt-6 flex items-center justify-between">
                                    @if($slide->price > 0)

                                        <div class="flex flex-col">
                                            @if($slide->old_price > $slide->price)
                                                <span class="mono text-xs line-through mb-1"
                                                      style="color: var(--text-secondary);" dir="ltr">
                                                        {{ number_format($slide->old_price) }} {{ $slide->currency === 'IRT' ? (app()->getLocale() === 'fa' ? 'تومان' : 'IRT') : $slide->currency }}
                                                    </span>
                                            @endif

                                            <span class="mono font-extrabold text-xl" style="color: var(--accent);"
                                                  dir="ltr">
                                                    {{ number_format($slide->price) }} {{ $slide->currency === 'IRT' ? (app()->getLocale() === 'fa' ? 'تومان' : 'IRT') : $slide->currency }}
                                            </span>
                                        </div>
                                    @endif


                                    {{-- نمایش بازدیدکننده فقط اگر مقدارش بیشتر از صفر باشد --}}
                                    @if($slide->viewers > 0)
                                        <span class="flex items-center gap-1.5 text-[11px] font-semibold"
                                              style="color: var(--text-secondary);">
                                                <span class="live-dot"></span>
                                                <span class="live-viewers" dir="ltr">{{ $slide->viewers }}</span>
                                                {{ app()->getLocale() === 'fa' ? 'نفر در حال مشاهده' : 'viewing now' }}
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="swiper-slide">
                    <div class="text-center py-20 text-gray-500">
                        <p>هنوز بنری تعریف نشده است.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- ناوبری --}}
        <div class="swiper-pagination !bottom-6"></div>
        <div
            class="swiper-button-next !w-12 !h-12 !rounded-full neu-flat flex items-center justify-center !text-[var(--accent)] after:!text-xl hover:neu-pressed transition-all"></div>
        <div
            class="swiper-button-prev !w-12 !h-12 !rounded-full neu-flat flex items-center justify-center !text-[var(--accent)] after:!text-xl hover:neu-pressed transition-all"></div>
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
                        rtl: true,
                        effect: 'fade',
                        fadeEffect: {crossFade: true},
                        speed: 1500, // کمی سریع‌تر برای حس پویایی بیشتر
                        loop: true,
                        autoplay: {
                            delay: 5000,
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
                        watchSlidesProgress: true,
                    });
                }
            }

            // اسکریپت تغییر زنده تعداد بازدیدکنندگان
            const viewersElements = document.querySelectorAll('.live-viewers');
            if (viewersElements.length > 0) {
                setInterval(() => {
                    viewersElements.forEach(el => {
                        let current = parseInt(el.innerText.replace(/[^0-9]/g, '')) || 40;
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
