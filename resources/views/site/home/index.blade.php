@extends('layouts.site.app')

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



    @include('site.home.hero')

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

    {{-- News Banner --}}
    @include('site.home.news')

@endsection
