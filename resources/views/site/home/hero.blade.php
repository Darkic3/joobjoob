@php
    use App\Models\HeroSlide;

    // ۱. ایمن‌سازی متغیر $slides در برابر Builder، Collection یا آرایه
    if (isset($slides) && $slides instanceof \Illuminate\Database\Eloquent\Builder) {
        $slides = $slides->get(); // اگر بیلدر بود، اجراش کن تا کالکشن بشه
    } elseif (!isset($slides)) {
        $slides = collect(); // اگر اصلاً تعریف نشده بود، کالکشن خالی
    } else {
        $slides = collect($slides); // تبدیل به کالکشن برای اطمینان صددرصد
    }

    // ۲. اگر کالکشن خالی بود، داده‌های نمونه (Dummy) می‌سازیم تا قالب خراب نشود
    if ($slides->isEmpty()) {
        $dummySlide = new HeroSlide();
        $dummySlide->title = ['fa' => 'جشنواره فروش ویژه فصل', 'en' => 'Special Season Sale'];
        $dummySlide->desc = ['fa' => 'بهترین محصولات با تخفیف‌های باورنکردنی تا ۵۰ درصد. همین حالا خرید کنید!', 'en' => 'Best products with incredible discounts up to 50%.'];
        $dummySlide->badge = ['fa' => 'پیشنهاد شگفت‌انگیز', 'en' => 'Amazing Offer'];
        $dummySlide->btn_text = ['fa' => 'مشاهده محصولات', 'en' => 'View Products'];
        $dummySlide->price = 2500000;
        $dummySlide->currency = 'IRT';
        $dummySlide->image = 'https://placehold.co/600x600/EEE/31343C?text=Hero+Image';
        $dummySlide->linkable_type = 'url';
        $dummySlide->linkable_id = '#';

        $slides = collect([$dummySlide]);
    }
@endphp

<section class="relative w-full mb-12 overflow-hidden rounded-[var(--radius-lg)]">
    <div class="absolute top-0 end-0 w-96 h-96 bg-[var(--accent)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob"></div>
    <div class="absolute bottom-0 start-0 w-96 h-96 bg-[var(--success)] rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000"></div>

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

                            <p class="hero-anim hero-desc text-sm md:text-base mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed" style="color: var(--text-secondary);">
                                {{ $slide->trans('desc') }}
                            </p>

                            <div class="hero-anim hero-btns flex flex-wrap items-center justify-center lg:justify-start gap-4">
                                {{-- ✅ استفاده از url به جای btn_link --}}
                                <a href="{{ $slide->url }}" class="neu-btn neu-btn-primary px-8 py-3.5 font-bold text-sm shadow-lg hover:shadow-xl transition-all">
                                    {{ $slide->trans('btn_text') ?: 'مشاهده' }}
                                </a>
                                <a href="#deals" class="neu-btn px-8 py-3.5 font-bold text-sm">
                                    پیشنهادات ویژه 🔥
                                </a>
                            </div>
                        </div>

                        {{-- ستون تصویر با افکت شناوری --}}
                        <div class="hero-anim hero-image-wrap order-1 lg:order-2 relative flex justify-center items-center">
                            <div class="neu-flat-lg p-6 md:p-10 w-full max-w-sm relative">
                                <span class="badge-urgency absolute -top-3 start-6 z-20 animate-bounce">۳۵٪ تخفیف</span>

                                <div class="relative overflow-hidden rounded-2xl float-anim">
                                    {{-- ✅ اصلاح مسیر عکس برای هماهنگی با ImageHandler اختصاصی شما --}}
                                    @if(filter_var($slide->image, FILTER_VALIDATE_URL))
                                        {{-- برای تصویر نمونه (Dummy) --}}
                                        <img src="{{ $slide->image }}" class="w-full rounded-2xl object-cover" style="aspect-ratio:1/1;" alt="{{ $slide->trans('title') }}">
                                    @else
                                        {{-- برای تصاویر آپلود شده واقعی --}}
                                        <img src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $slide->image) }}"
                                             class="w-full rounded-2xl object-cover"
                                             style="aspect-ratio:1/1;"
                                             alt="{{ $slide->trans('title') }}"
                                             onerror="this.src='https://placehold.co/600x600/EEE/31343C?text=No+Image'">
                                    @endif
                                </div>

                                {{-- کارت شناور روی تصویر --}}
                                @if($slide->price > 0)
                                    <div class="absolute -bottom-4 -end-4 neu-flat-sm p-3 flex items-center gap-3 z-20 backdrop-blur-sm" style="background: color-mix(in srgb, var(--bg-elevated) 90%, transparent);">
                                        <div class="neu-icon-circle-sm w-10 h-10 grid place-items-center text-sm">⭐</div>
                                        <div>
                                            <p class="text-[10px] font-bold" style="color: var(--text-secondary);">شروع قیمت از</p>
                                            <p class="text-sm font-black" style="color: var(--accent);">
                                                {{ number_format($slide->price) }} تومان
                                            </p>
                                        </div>
                                    </div>
                                @endif
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
        <div class="swiper-button-next !w-12 !h-12 !rounded-full neu-flat flex items-center justify-center !text-[var(--accent)] after:!text-xl hover:neu-pressed transition-all"></div>
        <div class="swiper-button-prev !w-12 !h-12 !rounded-full neu-flat flex items-center justify-center !text-[var(--accent)] after:!text-xl hover:neu-pressed transition-all"></div>
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
                        fadeEffect: { crossFade: true },
                        speed: 1000, // کمی کاهش دادم برای روان‌تر شدن
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
