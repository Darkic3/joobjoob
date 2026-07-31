<section class="relative w-full mb-12 overflow-hidden rounded-[var(--radius-lg)]">
    <div
        class="absolute top-0 end-0 w-96 h-96 bg-[var(--accent)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob"></div>
    <div
        class="absolute bottom-0 start-0 w-96 h-96 bg-[var(--success)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000"></div>

    <div class="swiper hero-swiper rounded-[var(--radius-lg)] relative z-10">
        <div class="swiper-wrapper">
            @forelse($slides as $slide)
                <div class="swiper-slide">
                    <div class="grid lg:grid-cols-2 items-center gap-8 p-6 md:p-12 min-h-[450px] md:min-h-[550px]">

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

                            <div
                                class="hero-anim hero-btns flex flex-wrap items-center justify-center lg:justify-start gap-4">
                                <a href="{{ $slide->btn_link }}"
                                   class="neu-btn neu-btn-primary px-8 py-3.5 font-bold text-sm shadow-lg hover:shadow-xl transition-all">
                                    {{ $slide->trans('btn_text') ?: 'مشاهده' }}
                                </a>
                                <a href="#deals" class="neu-btn px-8 py-3.5 font-bold text-sm">
                                    پیشنهادات ویژه 🔥
                                </a>
                            </div>
                        </div>

                        {{-- ستون تصویر با افکت شناوری --}}
                        <div
                            class="hero-anim hero-image-wrap order-1 lg:order-2 relative flex justify-center items-center">
                            <div class="neu-flat-lg p-6 md:p-10 w-full max-w-sm relative">
                                <span class="badge-urgency absolute -top-3 start-6 z-20 animate-bounce">۳۵٪ تخفیف</span>

                                <div class="relative overflow-hidden rounded-2xl float-anim">
                                    {{-- استفاده از Storage::url برای نمایش تصویر --}}
                                    <img src="{{ Storage::url($slide->image) }}"
                                         class="w-full rounded-2xl object-cover"
                                         style="aspect-ratio:1/1;" alt="{{ $slide->trans('title') }}">
                                </div>

                                {{-- کارت شناور روی تصویر --}}
                                @if($slide->price || $slide->old_price)
                                    <div
                                        class="absolute -bottom-4 -end-4 neu-flat-sm p-3 flex items-center gap-3 z-20 backdrop-blur-sm"
                                        style="background: color-mix(in srgb, var(--bg-elevated) 90%, transparent);">
                                        <div class="neu-icon-circle-sm w-10 h-10 grid place-items-center text-sm">⭐
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold" style="color: var(--text-secondary);">شروع
                                                قیمت از</p>
                                            <p class="text-sm font-black"
                                               style="color: var(--accent);">{{ $slide->price }} تومان</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- قیمت زیر تصویر --}}
                                <div class="mt-6 flex items-center justify-between">
                                    <div class="flex flex-col">
                                        @if($slide->trans('old_price'))
                                            <span class="mono text-xs line-through"
                                                  style="color: var(--text-secondary);">
                                                {{ $slide->trans('old_price') }}
                                            </span>
                                        @endif

                                        @if($slide->trans('price'))
                                            <span class="mono font-extrabold text-xl" style="color: var(--accent);">
                                                {{ $slide->trans('price') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="flex items-center gap-1.5 text-[11px] font-semibold"
                                          style="color: var(--text-secondary);">
                                    <span class="live-dot"></span> <span class="live-viewers">۴۲</span> نفر در حال مشاهده
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- حالت fallback اگر هیچ اسلایدی در دیتابیس نبود --}}
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
            // ۱. راه‌اندازی سوئیپر (نسخه اصلاح شده برای CDN)
            if (typeof Swiper !== 'undefined') {
                const heroSwiperEl = document.querySelector('.hero-swiper');
                if (heroSwiperEl) {
                    new Swiper(heroSwiperEl, {
                        // در نسخه CDN نیازی به تعریف modules نیست، همه چیز پیش‌فرض فعال است
                        rtl: true, // فعال‌سازی حالت راست‌چین برای سایت‌های فارسی
                        effect: 'fade', // افکت محو شدن برای جلوگیری از باگ روی‌هم‌افتادگی
                        fadeEffect: {
                            crossFade: true,
                        },
                        speed: 3000,
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
                        watchSlidesProgress: true, // حیاتی برای انیمیشن‌های CSS
                    });
                }
            }

            // ۲. اسکریپت تغییر زنده تعداد بازدیدکنندگان
            const viewersElement = document.querySelector('.live-viewers');
            if (viewersElement) {
                setInterval(() => {
                    let current = parseInt(viewersElement.innerText.replace(/[^0-9]/g, '')) || 40;
                    let change = Math.floor(Math.random() * 7) - 3;
                    let newValue = Math.max(20, current + change);
                    viewersElement.innerText = newValue;
                    viewersElement.classList.add('count-pop');
                    setTimeout(() => viewersElement.classList.remove('count-pop'), 300);
                }, 3000);
            }
        });
    </script>
@endpush
