@extends('layouts.site.app')

@section('title', __('messages.site_name') . ' - ' . __('messages.home'))

@section('content')

    {{-- ۱. اسلایدر هیرو --}}
    @include('site.home.hero', ['slides' => $slides])

    {{-- ۲. دسته‌بندی‌ها --}}
    @if($categories->isNotEmpty())
        <section class="reveal-on-scroll mt-10 mb-8">
            <div class="section-head mb-6">
                <div class="section-head-main">
                    <span class="section-icon" aria-hidden="true">🗂</span>
                    <div>
                        <h2 class="section-title">{{ settingOverride('home_categories_title', __('messages.categories')) }}</h2>
                    </div>
                </div>
                <a href="{{ localizedRoute('products.index') }}" class="section-link">
                    {{ settingOverride('home_view_all', __('messages.view_all')) }} ←
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
            <div class="deals-shell relative overflow-hidden p-5 md:p-8">
                {{-- هالهٔ آتش --}}
                <div class="deals-glow" aria-hidden="true"></div>

                {{-- ذرات آتش بالارونده --}}
                <div class="deals-embers" aria-hidden="true">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>

                <div class="relative z-10">
                    <div class="flex flex-wrap items-center justify-between gap-5 mb-8">
                        <div class="flex items-center gap-3.5">
                            <span class="deals-flame" aria-hidden="true">🔥</span>
                            <div>
                                <h2 class="deals-title font-black text-xl leading-tight">{{ settingOverride('home_deals_title', __('messages.special_offers')) }}</h2>
                                <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ settingOverride('home_deals_subtitle', __('messages.dont_miss_out')) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-bold hidden sm:block" style="color: var(--text-secondary);">
                                {{ __('messages.deal_ends_in') }}
                            </span>
                            <x-countdown-timer :ends-at="$dealEndsAt" id="deal-cd"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                        @foreach($deals as $product)
                            <x-product-card :product="$product" :soldPercent="$product->sold_percent" />
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۴. نشان‌های اعتماد --}}
    <section class="reveal-on-scroll py-6">
        <h2 class="text-lg font-extrabold mb-6">{{ __('messages.trust_title') }}</h2>
        <x-trust-badges :badges="$trustBadges"/>
    </section>

    {{-- ۵. محصولات ویژه --}}
    @if($featured->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="section-shell section-shell--featured p-5 md:p-7">                    {{-- اسکرول افقی: data-hscroll-step کسر عرض دید (۰٫۳۳ تا ۱) یا data-hscroll-cards تعداد کارت — پیش‌فرض ۰٫۸۵ --}}
                    <div class="relative z-10" data-hscroll data-hscroll-step="0.85">
                        <div class="section-head mb-7">
                            <div class="section-head-main">
                                <span class="section-icon" aria-hidden="true">✦</span>
                            <div>
                                <h2 class="section-title">{{ settingOverride('home_featured_title', __('messages.featured_products')) }}</h2>
                                <p class="section-subtitle">{{ __('messages.featured_subtitle') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2" role="group" aria-label="{{ __('messages.product_carousel_nav') }}">
                                <button type="button" class="hscroll-btn" data-hscroll-prev aria-label="{{ __('messages.scroll_prev') }}">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                <button type="button" class="hscroll-btn" data-hscroll-next aria-label="{{ __('messages.scroll_next') }}">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                            <a href="{{ localizedRoute('products.index') }}" class="section-link">
                                {{ settingOverride('home_view_all', __('messages.view_all')) }}
                            </a>
                        </div>
                    </div>

                    <div class="hscroll-rail">
                        @foreach($featured as $product)
                            <div class="hscroll-item">
                                <x-product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۶. پرفروش‌ترین‌ها --}}
    @if($bestSellers->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="section-shell section-shell--bestsellers p-5 md:p-7">                    {{-- اسکرول افقی: data-hscroll-step کسر عرض دید (۰٫۳۳ تا ۱) یا data-hscroll-cards تعداد کارت — پیش‌فرض ۰٫۸۵ --}}
                    <div class="relative z-10" data-hscroll data-hscroll-step="0.85">
                        <div class="section-head mb-7">
                            <div class="section-head-main">
                                <span class="section-icon" aria-hidden="true">🏆</span>
                            <div>
                                <h2 class="section-title">{{ settingOverride('home_best_sellers_title', __('messages.best_sellers')) }}</h2>
                                <p class="section-subtitle">{{ __('messages.best_sellers_subtitle') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2" role="group" aria-label="{{ __('messages.product_carousel_nav') }}">
                                <button type="button" class="hscroll-btn" data-hscroll-prev aria-label="{{ __('messages.scroll_prev') }}">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                <button type="button" class="hscroll-btn" data-hscroll-next aria-label="{{ __('messages.scroll_next') }}">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                            <a href="{{ localizedRoute('products.index') }}" class="section-link">
                                {{ settingOverride('home_view_all', __('messages.view_all')) }}
                            </a>
                        </div>
                    </div>

                    <div class="hscroll-rail">
                        @foreach($bestSellers as $index => $product)
                            <div class="hscroll-item">
                                @if($index === 0)
                                    {{-- سکوی طلایی برای رتبهٔ اول --}}
                                    <div class="podium-wrap h-full">
                                        <x-product-card :product="$product" :rank="$index + 1" />
                                    </div>
                                @else
                                    <x-product-card :product="$product" :rank="$index + 1" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۷. نظرات مشتریان --}}
    @if($reviews->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="section-head mb-6">
                <div class="section-head-main">
                    <span class="section-icon" aria-hidden="true">💬</span>
                    <div>
                        <h2 class="section-title">{{ settingOverride('home_reviews_title', __('messages.customer_reviews')) }}</h2>
                    </div>
                </div>
            </div>
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
