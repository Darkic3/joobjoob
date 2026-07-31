@extends('layouts.app')

@section('title', __('messages.site_name'))

@php
    /**
     * داده‌های نمایشی — این آرایه‌ها را با نتیجه‌ی کوئری‌های Eloquent واقعی
     * (Product::where(...)->get() و ...) جایگزین کنید. ساختار هر آیتم باید
     * با پراپ‌های x-product-card مطابقت داشته باشد.
     */
    $categories = [
        ['name' => 'موبایل و تبلت', 'icon' => '📱'],
        ['name' => 'لپ‌تاپ و کامپیوتر', 'icon' => '💻'],
        ['name' => 'پوشاک', 'icon' => '👕'],
        ['name' => 'کفش و کیف', 'icon' => '👟'],
        ['name' => 'لوازم خانگی', 'icon' => '🏠'],
        ['name' => 'زیبایی و سلامت', 'icon' => '💄'],
        ['name' => 'کتاب', 'icon' => '📚'],
        ['name' => 'ورزش', 'icon' => '⚽'],
    ];

    $deals = [
        ['name' => 'هدفون بی‌سیم نویزکنسلینگ مدل ایر پرو', 'image' => 'https://picsum.photos/seed/deal1/300/300', 'price' => 890000, 'old_price' => 1480000, 'rating' => 4, 'reviews' => 112, 'stock' => 9, 'sold_percent' => 82],
        ['name' => 'ساعت هوشمند اسپرت واترپروف', 'image' => 'https://picsum.photos/seed/deal2/300/300', 'price' => 2100000, 'old_price' => 2800000, 'rating' => 5, 'reviews' => 305, 'stock' => 20, 'sold_percent' => 54],
        ['name' => 'کوله‌پشتی سفری ضدآب ۳۵ لیتری', 'image' => 'https://picsum.photos/seed/deal3/300/300', 'price' => 1250000, 'old_price' => 1690000, 'rating' => 4, 'reviews' => 87, 'stock' => 2, 'sold_percent' => 96],
        ['name' => 'عطر مردانه امبروود ۱۰۰ میلی‌لیتر', 'image' => 'https://picsum.photos/seed/deal4/300/300', 'price' => 1890000, 'old_price' => 2700000, 'rating' => 5, 'reviews' => 196, 'stock' => 15, 'sold_percent' => 38],
    ];

    $featured = [
        ['name' => 'گوشی هوشمند مدل نوا X۲', 'image' => 'https://picsum.photos/seed/f1/300/300', 'price' => 14900000, 'rating' => 5, 'reviews' => 421, 'is_new' => true],
        ['name' => 'لپ‌تاپ اولترابوک ۱۴ اینچ', 'image' => 'https://picsum.photos/seed/f2/300/300', 'price' => 32500000, 'rating' => 4, 'reviews' => 158, 'stock' => 4],
        ['name' => 'پیراهن مردانه کلاسیک لینن', 'image' => 'https://picsum.photos/seed/f3/300/300', 'price' => 980000, 'rating' => 5, 'reviews' => 96],
        ['name' => 'کیف چرم زنانه دست‌دوز', 'image' => 'https://picsum.photos/seed/f4/300/300', 'price' => 2300000, 'rating' => 5, 'reviews' => 214],
    ];

    $bestSellers = [
        ['rank' => 1, 'name' => 'هدفون بی‌سیم نویزکنسلینگ', 'image' => 'https://picsum.photos/seed/b1/300/300', 'price' => 890000],
        ['rank' => 2, 'name' => 'کتانی رانینگ ایرفلوی پرو', 'image' => 'https://picsum.photos/seed/b2/300/300', 'price' => 2490000],
        ['rank' => 3, 'name' => 'ساعت هوشمند اسپرت', 'image' => 'https://picsum.photos/seed/b3/300/300', 'price' => 2100000],
        ['rank' => 4, 'name' => 'عطر مردانه امبروود', 'image' => 'https://picsum.photos/seed/b4/300/300', 'price' => 1890000],
    ];

    $reviews = [
        ['initials' => 'ع.ر', 'name' => 'علی رضایی', 'rating' => 5, 'comment' => 'کیفیت فوق‌العاده بود و بسته‌بندی خیلی شیک. ارسال هم سریع‌تر از چیزی بود که فکر می‌کردم.'],
        ['initials' => 'س.م', 'name' => 'سارا محمدی', 'rating' => 5, 'comment' => 'دومین خریدمه از این فروشگاه و هر دو بار عالی بود. پشتیبانیشون هم خیلی سریع جواب داد.'],
        ['initials' => 'م.ک', 'name' => 'محمد کریمی', 'rating' => 4, 'comment' => 'محصول دقیقاً مثل عکس بود. فقط زمان ارسال یک روز بیشتر از تعیین‌شده طول کشید.'],
    ];
@endphp

@section('content')

    {{-- هیرو --}}
    <section class="grid lg:grid-cols-2 items-center gap-10 py-6 md:py-12">
        <div class="order-2 lg:order-1">
            <span class="badge-new inline-block mb-4">✦ کالکشن پاییزه رسید</span>
            <h1 class="text-3xl md:text-5xl font-black leading-[1.3] mb-5">
                خرید هوشمند،<br>
                <span style="color: var(--accent);">لذتِ بی‌پایان.</span>
            </h1>
            <p class="text-sm md:text-base mb-8 max-w-md" style="color: var(--text-secondary);">
                بیش از ۱۲ هزار کالای اورجینال، ارسال از همان روز سفارش و ضمانت بازگشت بدون قید و شرط تا ۷ روز.
            </p>
            <div class="flex flex-wrap items-center gap-3 mb-8">
                <a href="{{ route('products.index') }}"
                   class="neu-btn neu-btn-primary px-7 py-3 font-bold text-sm">{{ __('messages.products') }}</a>
                <a href="#deals" class="neu-btn px-7 py-3 font-bold text-sm">{{ __('messages.special_offers') }} 🔥</a>
            </div>
        </div>

        <div class="order-1 lg:order-2 relative flex justify-center hero-visual">
            <div class="neu-flat-lg neu-pulse p-6 w-full max-w-sm relative">
                <span class="badge-urgency absolute -top-3 start-6 z-10">۳۵٪ تخفیف</span>
                <img src="https://picsum.photos/seed/hero-shoe/500/500" class="w-full rounded-2xl object-cover mb-4"
                     style="aspect-ratio:1/1;" alt="محصول ویژه">
                <h3 class="font-bold text-sm mb-1">کتانی رانینگ ایرفلوی مدل پرو</h3>
                <div class="flex items-center gap-1 mb-2 text-xs">
                    <span class="stars">★★★★★</span>
                    <span style="color: var(--text-secondary);">(۲۴۸ نظر)</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-baseline gap-2">
                        <span class="mono font-extrabold text-lg" style="color: var(--accent);">۲,۴۹۰,۰۰۰</span>
                        <span class="mono text-xs line-through" style="color: var(--text-secondary);">۳,۸۰۰,۰۰۰</span>
                    </div>
                    <span class="flex items-center gap-1.5 text-[11px] font-semibold"
                          style="color: var(--text-secondary);">
                        <span class="live-dot"></span> ۳۱ نفر الان می‌بینند
                    </span>
                </div>
            </div>
        </div>
    </section>


    {{-- هیرو اسلایدر خفن --}}
    @php
        $heroSlides = [
            [
                'title' => 'خرید هوشمند، لذتِ بی‌پایان',
                'subtitle' => 'بیش از ۱۲ هزار کالای اورجینال با ضمانت بازگشت ۷ روزه',
                'image' => 'https://picsum.photos/seed/hero-shoe/600/600',
                'badge' => 'کالکشن پاییزه رسید',
                'btn_text' => 'مشاهده محصولات',
                'btn_link' => route('products.index')
            ],
            [
                'title' => 'تکنولوژی روز دنیا در دستان شما',
                'subtitle' => 'جدیدترین لپ‌تاپ‌ها و گجت‌های هوشمند با گارانتی اصلی',
                'image' => 'https://picsum.photos/seed/hero-laptop/600/600',
                'badge' => 'تخفیف‌های ویژه',
                'btn_text' => 'پیشنهادات شگفت‌انگیز',
                'btn_link' => '#deals'
            ],
            [
                'title' => 'استایل خودت رو بساز',
                'subtitle' => 'جدیدترین کالکشن پوشاک و اکسسوری با طراحی‌های منحصر به فرد',
                'image' => 'https://picsum.photos/seed/hero-fashion/600/600',
                'badge' => 'ارسال رایگان',
                'btn_text' => 'خرید پوشاک',
                'btn_link' => route('products.index')
            ],
        ];
    @endphp
    <section class="relative w-full mb-10 overflow-hidden rounded-[var(--radius-lg)]">
        <div class="swiper myHeroSwiper rounded-[var(--radius-lg)]">
            <div class="swiper-wrapper">
                @foreach($heroSlides as $slide)
                    <div class="swiper-slide">
                        <div class="grid lg:grid-cols-2 items-center gap-8 p-6 md:p-12 min-h-[400px] md:min-h-[500px]">
                            {{-- متن اسلاید --}}
                            <div class="order-2 lg:order-1 z-10 text-center lg:text-start">
                                <span class="badge-new inline-block mb-4 animate-pulse">✦ {{ $slide['badge'] }}</span>
                                <h1 class="text-3xl md:text-5xl font-black leading-[1.3] mb-5 text-primary">
                                    {{ $slide['title'] }}
                                </h1>
                                <p class="text-sm md:text-base mb-8 max-w-md mx-auto lg:mx-0"
                                   style="color: var(--text-secondary);">
                                    {{ $slide['subtitle'] }}
                                </p>
                                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                                    <a href="{{ $slide['btn_link'] }}"
                                       class="neu-btn neu-btn-primary px-7 py-3 font-bold text-sm shadow-lg hover:shadow-xl transition-all">
                                        {{ $slide['btn_text'] }}
                                    </a>
                                    <a href="#deals" class="neu-btn px-7 py-3 font-bold text-sm">
                                        {{ __('messages.special_offers') }} 🔥
                                    </a>
                                </div>
                            </div>

                            {{-- تصویر اسلاید با افکت نئومورفیسم --}}
                            <div class="order-1 lg:order-2 relative flex justify-center items-center">
                                <div class="neu-flat-lg p-4 md:p-8 w-full max-w-sm relative group cursor-pointer"
                                     id="tilt-card">
                                    <span
                                        class="badge-urgency absolute -top-3 start-6 z-20 animate-bounce">۳۵٪ تخفیف</span>
                                    <div class="relative overflow-hidden rounded-2xl">
                                        <img src="{{ $slide['image'] }}"
                                             class="w-full rounded-2xl object-cover transform group-hover:scale-105 transition-transform duration-500"
                                             style="aspect-ratio:1/1;" alt="محصول ویژه">
                                        {{-- افکت درخشش روی تصویر --}}
                                        <div
                                            class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between">
                                        <div>
                                            <h3 class="font-bold text-sm mb-1">محصول ویژه فصل</h3>
                                            <div class="flex items-center gap-1 text-xs">
                                                <span class="stars">★★★★★</span>
                                                <span style="color: var(--text-secondary);">(۲۴۸ نظر)</span>
                                            </div>
                                        </div>
                                        <span class="flex items-center gap-1.5 text-[11px] font-semibold"
                                              style="color: var(--text-secondary);">
                                        <span class="live-dot"></span> <span class="live-viewers">۳۱</span> نفر در حال مشاهده
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- دکمه‌های ناوبری و صفحه‌بندی نئومورفیک --}}
            <div class="swiper-pagination !bottom-4"></div>
            <div
                class="swiper-button-next !w-10 !h-10 !rounded-full neu-flat-sm flex items-center justify-center !text-[var(--accent)] after:!text-lg"></div>
            <div
                class="swiper-button-prev !w-10 !h-10 !rounded-full neu-flat-sm flex items-center justify-center !text-[var(--accent)] after:!text-lg"></div>
        </div>
    </section>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ۱. راه‌اندازی سوئیپر
                if (typeof Swiper !== 'undefined') {
                    const swiper = new Swiper('.myHeroSwiper', {
                        direction: 'horizontal',
                        rtl: true,
                        loop: true,
                        speed: 800,
                        effect: 'creative',
                        creativeEffect: {
                            prev: { shadow: true, translate: ['-20%', 0, -1] },
                            next: { translate: ['100%', 0, 0] },
                        },
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
                    });
                }

                // ۲. زنده کردن تعداد بازدیدکنندگان (Live Viewers)
                const viewersElement = document.querySelector('.live-viewers');
                if (viewersElement) {
                    setInterval(() => {
                        let current = parseInt(viewersElement.innerText.replace(/[^0-9]/g, '')) || 30;
                        let change = Math.floor(Math.random() * 5) - 2;
                        let newValue = Math.max(15, current + change);
                        viewersElement.innerText = newValue;

                        // انیمیشن کوچک هنگام تغییر عدد
                        viewersElement.classList.add('count-pop');
                        setTimeout(() => viewersElement.classList.remove('count-pop'), 300);
                    }, 3000);
                }
            });
        </script>
    @endpush

    {{-- دسته‌بندی‌ها --}}
    <section class="reveal-on-scroll mt-10 mb-8">
        <h2 class="text-lg font-extrabold mb-6">{{ __('messages.categories') }}</h2>
        <div class="flex flex-wrap justify-center gap-4">
            @foreach($categories as $cat)
                <x-category-card :category="$cat"/>
            @endforeach
        </div>
    </section>

    {{-- پیشنهادهای داغ با تایمر --}}
    <section id="deals" class="reveal-on-scroll py-6">
        <div class="neu-flat-lg p-5 md:p-7">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🔥</span>
                    <div>
                        <h2 class="font-extrabold text-lg">{{ __('messages.special_offers') }}</h2>
                        <p class="text-xs" style="color: var(--text-secondary);">تا تمام نشده، از دستش نده</p>
                    </div>
                </div>
                <x-countdown-timer :ends-in-seconds="9912" id="deal-cd"/>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($deals as $product)
                    <x-product-card :product="$product"/>
                @endforeach
            </div>
        </div>
    </section>

    {{-- نشان‌های اعتماد --}}
    <section class="reveal-on-scroll py-6">
        <x-trust-badges/>
    </section>

    {{-- محصولات ویژه --}}
    <section class="reveal-on-scroll py-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-extrabold">{{ __('messages.featured_products') }}</h2>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold"
               style="color: var(--accent);">{{ __('messages.view_all') }} ←</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($featured as $product)
                <x-product-card :product="$product"/>
            @endforeach
        </div>
    </section>

    {{-- پرفروش‌ترین‌ها: رتبه‌بندی واقعی، عدد رتبه اطلاعات معنادار می‌دهد --}}
    {{-- پرفروش‌ترین‌ها --}}
    <section class="reveal-on-scroll py-6">
        <h2 class="text-lg font-extrabold mb-5">{{ __('messages.best_sellers') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($bestSellers as $item)
                @php
                    $rankColors = [
                        1 => 'var(--gold)',
                        2 => 'var(--silver)',
                        3 => 'var(--bronze)',
                    ];
                    $rankTextColor = [
                        1 => '#000', 2 => '#000', 3 => '#fff'
                    ];
                    $color = $rankColors[$item['rank']] ?? 'var(--bg-elevated)';
                    $textColor = $rankTextColor[$item['rank']] ?? 'var(--text-primary)';
                @endphp
                <div class="neu-card neu-interactive p-4 relative group">
                    {{-- نشان رتبه --}}
                    <span class="neu-rank-badge" style="--rank-color: {{ $color }}; --rank-text: {{ $textColor }};">
                        {{ $item['rank'] }}
                    </span>

                    {{-- تصویر محصول --}}
                    <div class="relative mb-3 overflow-hidden rounded-xl" style="aspect-ratio:1/1;">
                        <img src="{{ $item['image'] ?? 'https://picsum.photos/seed/best'.$item['rank'].'/300/300' }}"
                             onerror="this.src='https://picsum.photos/seed/fallback/300/300'"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                             alt="{{ $item['name'] }}">
                    </div>

                    {{-- اطلاعات محصول --}}
                    <h4 class="text-xs font-semibold mb-2 line-clamp-2" style="color: var(--text-primary);">
                        {{ $item['name'] }}
                    </h4>
                    <div class="flex items-center justify-between">
                    <span class="mono font-bold text-sm" style="color: var(--accent);">
                        {{ number_format($item['price']) }}
                    </span>
                        <button class="neu-btn neu-btn-icon w-8 h-8 p-0 text-xs"
                                title="{{ __('messages.add_to_cart') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- نظرات مشتریان --}}
    <section class="reveal-on-scroll py-6">
        <h2 class="text-lg font-extrabold mb-5">{{ __('messages.customer_reviews') }}</h2>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($reviews as $review)
                <div class="neu-card p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="neu-icon-circle w-11 h-11 grid place-items-center font-bold text-sm"
                              style="color: var(--accent);">{{ $review['initials'] }}</span>
                        <div>
                            <p class="text-sm font-bold">{{ $review['name'] }}</p>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                  style="background: var(--success); color: #fff;">خرید تایید شده ✓</span>
                        </div>
                    </div>
                    <p class="stars text-sm mb-2">{{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', 5 - $review['rating']) }}</p>
                    <p class="text-xs" style="color: var(--text-secondary);">{{ $review['comment'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- بنر خبرنامه --}}
    <section class="reveal-on-scroll py-6 mb-4">
        <div class="neu-flat-lg p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-start">
                <h3 class="font-extrabold text-lg mb-2">۱۰٪ تخفیف خرید اول، هدیه‌ی ماست</h3>
                <p class="text-xs" style="color: var(--text-secondary);">با عضویت در خبرنامه، کد تخفیف رو همین الان
                    بگیر</p>
            </div>
            <form class="flex gap-2 w-full md:w-auto" action="#" method="POST">
                @csrf
                <input type="email" name="email" placeholder="ایمیل شما"
                       class="neu-input px-4 py-3 text-sm flex-1 md:w-64">
                <button type="submit" class="neu-btn neu-btn-primary px-6 py-3 text-sm font-bold whitespace-nowrap">
                    عضویت
                </button>
            </form>
        </div>
    </section>

@endsection
