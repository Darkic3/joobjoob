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
                <a href="{{ route('products.index') }}" class="neu-btn neu-btn-primary px-7 py-3 font-bold text-sm">{{ __('messages.products') }}</a>
                <a href="#deals" class="neu-btn px-7 py-3 font-bold text-sm">{{ __('messages.special_offers') }} 🔥</a>
            </div>
        </div>

        <div class="order-1 lg:order-2 relative flex justify-center hero-visual">
            <div class="neu-flat-lg neu-pulse p-6 w-full max-w-sm relative">
                <span class="badge-urgency absolute -top-3 start-6 z-10">۳۵٪ تخفیف</span>
                <img src="https://picsum.photos/seed/hero-shoe/500/500" class="w-full rounded-2xl object-cover mb-4" style="aspect-ratio:1/1;" alt="محصول ویژه">
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
                    <span class="flex items-center gap-1.5 text-[11px] font-semibold" style="color: var(--text-secondary);">
                        <span class="live-dot"></span> ۳۱ نفر الان می‌بینند
                    </span>
                </div>
            </div>
        </div>
    </section>

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
                <x-countdown-timer :ends-in-seconds="9912" id="deal-cd" />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($deals as $product)
                    <x-product-card :product="$product" />
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
            <a href="{{ route('products.index') }}" class="text-xs font-semibold" style="color: var(--accent);">{{ __('messages.view_all') }} ←</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($featured as $product)
                <x-product-card :product="$product" />
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
                        1 => ['bg' => '#FFD700', 'text' => '#000'], // طلا
                        2 => ['bg' => '#C0C0C0', 'text' => '#000'], // نقره
                        3 => ['bg' => '#CD7F32', 'text' => '#fff'], // برنز
                    ];
                    $rankStyle = $rankColors[$item['rank']] ?? ['bg' => 'var(--bg-elevated)', 'text' => 'var(--text-primary)'];
                @endphp
                <div class="neu-card neu-interactive p-4 relative group">
                    {{-- نشان رتبه --}}
                    <span class="absolute -top-3 -end-3 w-8 h-8 rounded-full grid place-items-center font-bold text-xs shadow-lg z-10"
                          style="background: {{ $rankStyle['bg'] }}; color: {{ $rankStyle['text'] }};">
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
                        <button class="neu-btn neu-btn-icon w-8 h-8 p-0 text-xs">

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
                        <span class="neu-icon-circle w-11 h-11 grid place-items-center font-bold text-sm" style="color: var(--accent);">{{ $review['initials'] }}</span>
                        <div>
                            <p class="text-sm font-bold">{{ $review['name'] }}</p>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: var(--success); color: #fff;">خرید تایید شده ✓</span>
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
                <p class="text-xs" style="color: var(--text-secondary);">با عضویت در خبرنامه، کد تخفیف رو همین الان بگیر</p>
            </div>
            <form class="flex gap-2 w-full md:w-auto" action="#" method="POST">
                @csrf
                <input type="email" name="email" placeholder="ایمیل شما" class="neu-input px-4 py-3 text-sm flex-1 md:w-64">
                <button type="submit" class="neu-btn neu-btn-primary px-6 py-3 text-sm font-bold whitespace-nowrap">عضویت</button>
            </form>
        </div>
    </section>

@endsection
