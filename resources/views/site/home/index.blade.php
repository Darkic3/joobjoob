@extends('layouts.site.app')

@section('title', __('messages.site_name') . ' - ' . __('messages.home'))

@section('content')

    {{-- ۱. اسلایدر هیرو --}}
    @include('site.home.hero', ['slides' => $slides])

    {{-- ۲. دسته‌بندی‌ها --}}
    @if($categories->isNotEmpty())
        <section class="reveal-on-scroll mt-10 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-extrabold">{{ setting('home_categories_title', __('messages.categories')) }}</h2>
                <a href="{{ localizedRoute('products.index') }}" class="text-xs font-semibold hover:underline" style="color: var(--accent);">
                    {{ setting('home_view_all', __('messages.view_all')) }} ←
                </a>
            </div>
            {{-- گرید خودکار: با تعداد کم وسط‌چین و با تعداد زیاد تمام‌عرض --}}
            <div class="grid grid-cols-[repeat(auto-fit,minmax(130px,150px))] justify-center gap-4">
                @foreach($categories as $cat)
                    <x-category-card :category="$cat"/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۳. پیشنهادهای داغ با تایمر --}}
    @if($deals->isNotEmpty())
        <section id="deals" class="reveal-on-scroll py-6">
            <div class="neu-flat-lg p-5 md:p-7">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🔥</span>
                        <div>
                            <h2 class="font-extrabold text-lg">{{ setting('home_deals_title', __('messages.special_offers')) }}</h2>
                            <p class="text-xs" style="color: var(--text-secondary);">{{ setting('home_deals_subtitle', __('messages.dont_miss_out')) }}</p>
                        </div>
                    </div>
                    {{-- فرض بر این است که کامپوننت تایمر وجود دارد --}}
                    <x-countdown-timer :ends-in-seconds="9912" id="deal-cd"/>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    @foreach($deals as $product)
                        <x-product-card :product="$product" :soldPercent="$product->sold_percent" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ۴. نشان‌های اعتماد --}}
    <section class="reveal-on-scroll py-6">
        <x-trust-badges/>
    </section>

    {{-- ۵. محصولات ویژه --}}
    @if($featured->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-extrabold">{{ setting('home_featured_title', __('messages.featured_products')) }}</h2>
                <a href="{{ localizedRoute('products.index') }}" class="text-xs font-semibold hover:underline" style="color: var(--accent);">
                    {{ setting('home_view_all', __('messages.view_all')) }} ←
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۶. پرفروش‌ترین‌ها --}}
    @if($bestSellers->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <h2 class="text-lg font-extrabold mb-5">{{ setting('home_best_sellers_title', __('messages.best_sellers')) }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($bestSellers as $index => $product)
                    {{-- استفاده هوشمندانه از کامپوننت product-card با ارسال رتبه --}}
                    <x-product-card :product="$product" :rank="$index + 1" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۷. نظرات مشتریان --}}
    @if($reviews->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <h2 class="text-lg font-extrabold mb-5">{{ setting('home_reviews_title', __('messages.customer_reviews')) }}</h2>
            <div class="grid md:grid-cols-3 gap-5">
                @foreach($reviews as $review)
                    <div class="neu-card p-5 flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="neu-icon-circle w-11 h-11 grid place-items-center font-bold text-sm" style="color: var(--accent);">
                                {{ mb_substr($review->user->name ?? 'کاربر', 0, 1) }}
                            </span>
                            <div>
                                <p class="text-sm font-bold">{{ $review->user->name ?? __('messages.user') }}</p>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: var(--success); color: #fff;">
                                    {{ __('messages.verified_purchase') }} ✓
                                </span>
                            </div>
                        </div>

                        {{-- نمایش نام محصولی که نظر داده شده (اختیاری اما حرفه‌ای) --}}
                        @if($review->product)
                            <p class="text-[10px] mb-2 truncate" style="color: var(--text-secondary);">
                                درباره: {{ $review->product->trans('name') }}
                            </p>
                        @endif

                        <p class="stars text-sm mb-2 text-[var(--gold)]">
                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                        </p>
                        <p class="text-xs leading-relaxed flex-1" style="color: var(--text-secondary);">
                            "{{ $review->comment }}"
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۸. بنر خبرنامه یا اخبار --}}
    @include('site.home.news')

@endsection
