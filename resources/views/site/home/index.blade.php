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
                    <span class="section-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    </span>
                    <div>
                        <h2 class="section-title">{{ settingOverride('home_categories_title', __('messages.categories')) }}</h2>
                    </div>
                </div>
                <a href="{{ localizedRoute('products.index') }}" class="section-link">
                    {{ settingOverride('home_view_all', __('messages.view_all')) }}
                </a>
            </div>
            {{-- گرید خودکار: با تعداد کم وسط‌چین و با تعداد زیاد تمام‌عرض — ورود پله‌ای (data-stagger) --}}
            <div class="grid grid-cols-[repeat(auto-fit,minmax(130px,150px))] justify-center gap-4" data-stagger>
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
                            <span class="deals-flame" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c4.4 0 7-2.8 7-6.5 0-3.5-2.3-5.5-3.7-7.3-.5-.7-1.2-.2-1 .6.2.9.3 1.7-.1 2.5-.7-.6-1.5-1.6-1.9-3C11.5 5.3 12 3.5 12 2c-1.8 1.4-3 3.4-3.4 5.3C8.2 9.4 9 11 10 12.5 8.5 12 7 10.8 7 8.5 5.4 10.7 5 13 5 15.5 5 19.2 7.6 22 12 22z"/></svg>
                            </span>
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

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-5" data-stagger>
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
            <div class="section-shell section-shell--featured p-5 pb-8 md:p-7 md:pb-10">
                {{-- محو نرم لبهٔ پایین باکس — لبهٔ سخت/مربعی را گرادیانت‌وار محو می‌کند --}}
                <div class="section-shell-fade" aria-hidden="true"></div>
                {{-- اسکرول افقی حرفه‌ای: درگ با اینرسی + فلش‌های اسنپ‌به‌کارت؛ data-hscroll-cards="2" تعداد کارت در هر کلیک --}}
                <div class="relative z-10" data-hscroll>
                        <div class="section-head mb-7">                        <div class="section-head-main">
                            <span class="section-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M19 15l.9 2.4L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.6z"/></svg>
                            </span>
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

                    <div class="hscroll-wrap">
                        <div class="hscroll-rail">
                            @foreach($featured as $product)
                                <div class="hscroll-item">
                                    <x-product-card :product="$product" />
                                </div>
                            @endforeach
                        </div>
                        {{-- محو دائمی لبه‌ها — زیر بج‌های رتبه (z پایین‌تر)، بالای کارت‌ها --}}
                        <div class="hscroll-edge-fade hscroll-edge-fade--start" aria-hidden="true"></div>
                        <div class="hscroll-edge-fade hscroll-edge-fade--end" aria-hidden="true"></div>
                        {{-- نوار پیشرفت اسکرول --}}
                        <div class="hscroll-progress-wrap" aria-hidden="true"><div class="hscroll-progress"></div></div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۶. پرفروش‌ترین‌ها --}}
    @if($bestSellers->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="section-shell section-shell--bestsellers p-5 pb-8 md:p-7 md:pb-10">
                {{-- محو نرم لبهٔ پایین باکس — لبهٔ سخت/مربعی را گرادیانت‌وار محو می‌کند --}}
                <div class="section-shell-fade" aria-hidden="true"></div>
                {{-- اسکرول افقی حرفه‌ای: درگ با اینرسی + فلش‌های اسنپ‌به‌کارت؛ data-hscroll-cards="2" تعداد کارت در هر کلیک --}}
                <div class="relative z-10" data-hscroll>
                        <div class="section-head mb-7">                        <div class="section-head-main">
                            <span class="section-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM7 5H4a3 3 0 0 0 3 4M17 5h3a3 3 0 0 1-3 4"/></svg>
                            </span>
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

                    <div class="hscroll-wrap">
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
                        {{-- محو دائمی لبه‌ها — زیر بج‌های رتبه (z پایین‌تر)، بالای کارت‌ها --}}
                        <div class="hscroll-edge-fade hscroll-edge-fade--start" aria-hidden="true"></div>
                        <div class="hscroll-edge-fade hscroll-edge-fade--end" aria-hidden="true"></div>
                        {{-- نوار پیشرفت اسکرول --}}
                        <div class="hscroll-progress-wrap" aria-hidden="true"><div class="hscroll-progress"></div></div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۶.۵. بنرهای میانی تبلیغاتی — از تنظیمات (تب بنرها)؛ پرکنندهٔ فضای بین بخش‌ها --}}
    @if(setting('promo_banners_enabled') && (setting('promo_banner_1_title') || setting('promo_banner_2_title')))
        <section class="reveal-on-scroll py-6">
            <div class="promo-banners grid md:grid-cols-2 gap-5 promo-banners--{{ setting('promo_banners_style', 'gradient') }}">
                @if(setting('promo_banner_1_title'))
                    <a href="{{ setting('promo_banner_1_url') ?: localizedRoute('products.index') }}"
                       class="promo-banner promo-banner--accent group">
                        <span class="promo-banner-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        </span>
                        <span class="promo-banner-body">
                            {{-- data-digit-raw: کدهای تخفیف مثل WELCOME10 نباید ارقامش فارسی شود --}}
                            <span class="promo-banner-title" data-digit-raw>{{ settingOverride('promo_banner_1_title', __('messages.promo_banner_1_title')) }}</span>
                            <span class="promo-banner-subtitle">{{ settingOverride('promo_banner_1_subtitle', __('messages.promo_banner_1_subtitle')) }}</span>
                        </span>
                        <span class="promo-banner-arrow" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endif
                @if(setting('promo_banner_2_title'))
                    <a href="{{ setting('promo_banner_2_url') ?: localizedRoute('products.index') }}"
                       class="promo-banner promo-banner--gold group">
                        <span class="promo-banner-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v3a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
                        </span>
                        <span class="promo-banner-body">
                            <span class="promo-banner-title" data-digit-raw>{{ settingOverride('promo_banner_2_title', __('messages.promo_banner_2_title')) }}</span>
                            <span class="promo-banner-subtitle">{{ settingOverride('promo_banner_2_subtitle', __('messages.promo_banner_2_subtitle')) }}</span>
                        </span>
                        <span class="promo-banner-arrow" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- ۷. نظرات مشتریان — سوشال پروف: خلاصه امتیاز + کارت برجسته (Double-Bezel) + کارت‌های ثانویه --}}
    @if($reviews->isNotEmpty())
        <section class="reveal-on-scroll py-8">
            <div class="reviews-shell relative overflow-hidden">
                {{-- حباب‌های رنگی شناور پس‌زمینه --}}
                <div class="reviews-blobs" aria-hidden="true">
                    <span class="reviews-blob reviews-blob--1"></span>
                    <span class="reviews-blob reviews-blob--2"></span>
                    <span class="reviews-blob reviews-blob--3"></span>
                </div>

                <div class="relative z-10">
                    {{-- سربرگ: عنوان + خلاصه امتیاز کلی --}}
                    <div class="flex flex-wrap items-end justify-between gap-5 mb-8">
                        <div class="section-head-main">
                            <span class="section-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.9 8.9 0 0 1-3.6-.7L3 21l1.9-5.3a8 8 0 0 1-.9-3.7A8.4 8.4 0 0 1 12.5 3.5 8.4 8.4 0 0 1 21 11.5z"/></svg>
                            </span>
                            <div>
                                <h2 class="section-title">{{ settingOverride('home_reviews_title', __('messages.customer_reviews')) }}</h2>
                                <p class="section-subtitle">{{ __('messages.review_average') }}</p>
                            </div>
                        </div>
                        {{-- عدد میانگین + ستاره‌ها --}}
                        <div class="reviews-rating">
                            <span class="reviews-rating-value">{{ $reviewsAvg }}</span>
                            <div>
                                <p class="reviews-rating-stars" aria-hidden="true">
                                    {{ str_repeat('★', (int) round($reviewsAvg)) }}{{ str_repeat('☆', 5 - (int) round($reviewsAvg)) }}
                                </p>
                                <p class="text-[11px] font-semibold" style="color: var(--text-secondary);">
                                    {{ __('messages.based_on_recent', ['count' => $reviews->count()]) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- گرید: کارت برجسته تمام‌عرض + بقیه دوتایی — ورود پله‌ای --}}
                    <div class="grid lg:grid-cols-2 gap-5" data-stagger>
                        @foreach($reviews as $review)
                            @if($loop->first)
                                {{-- کارت برجسته (سوشال پروف اصلی) — Double-Bezel --}}
                                <div class="review-card review-card--featured lg:col-span-2 group">
                                    <div class="review-card-inner">
                                        {{-- کوتیشن تزئینی بزرگ --}}
                                        <svg class="review-quote-mark" viewBox="0 0 24 24" width="92" height="92" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 11H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4"/><path d="M20 11h-4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4"/></svg>

                                        <p class="review-stars review-stars--lg" aria-hidden="true">
                                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                        </p>
                                        <blockquote class="review-quote">
                                            «{{ $review->comment }}»
                                        </blockquote>

                                        <div class="review-meta">
                                            <span class="review-avatar" aria-hidden="true">
                                                {{ mb_substr($review->user->name ?? __('messages.user'), 0, 1) }}
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold">{{ $review->user->name ?? __('messages.user') }}</p>
                                                <span class="review-verified">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                                    {{ __('messages.verified_purchase') }}
                                                </span>
                                            </div>
                                            <span class="review-date">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                                {{ show_date($review->created_at, 'd F Y') }}
                                            </span>
                                            @if($review->product)
                                                <a href="{{ localizedRoute('products.show', ['product' => $review->product]) }}" class="review-product-chip">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                                    {{ mb_strimwidth($review->product->trans('name'), 0, 42, '…') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- کارت‌های ثانویه --}}
                                <div class="review-card group">
                                    <div class="review-card-inner">
                                        <div class="flex items-center gap-3 mb-4">
                                            <span class="review-avatar review-avatar--sm" aria-hidden="true">
                                                {{ mb_substr($review->user->name ?? __('messages.user'), 0, 1) }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold truncate">{{ $review->user->name ?? __('messages.user') }}</p>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="review-verified">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                                        {{ __('messages.verified_purchase') }}
                                                    </span>
                                                    <span class="review-date">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                                        {{ show_date($review->created_at, 'd F Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="review-stars" aria-hidden="true">
                                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                            </p>
                                        </div>

                                        <p class="review-quote review-quote--sm">
                                            «{{ $review->comment }}»
                                        </p>

                                        @if($review->product)
                                            <a href="{{ localizedRoute('products.show', ['product' => $review->product]) }}" class="review-product-chip review-product-chip--sm mt-4">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                                {{ __('messages.about_product') }}: {{ mb_strimwidth($review->product->trans('name'), 0, 42, '…') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ۸. آخرین مقالات --}}
    @if($latestBlogs->isNotEmpty())
        <section class="reveal-on-scroll py-8">
            <div class="section-head mb-7">
                <div class="section-head-main">
                    <span class="section-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M8 8h8M8 12h5M8 16h3"/></svg>
                    </span>
                    <div>
                        <h2 class="section-title">{{ settingOverride('home_blogs_title', __('messages.latest_articles')) }}</h2>
                        <p class="section-subtitle">{{ __('messages.latest_articles_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ localizedRoute('blogs.index') }}" class="section-link">
                    {{ __('messages.view_all') }}
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-5" data-stagger>
                @foreach($latestBlogs as $blog)
                    <article class="blog-card neu-card group overflow-hidden flex flex-col">
                        <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}" class="block overflow-hidden">
                            <img src="{{ $blog->image_url ?? 'https://picsum.photos/seed/blog-'.$blog->id.'/600/400' }}"
                                 alt="{{ $blog->title }}"
                                 width="600" height="400"
                                 loading="lazy"
                                 class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] will-change-transform">
                        </a>
                        <div class="p-5 flex flex-col gap-2.5 flex-1">
                            {{-- ردیف بالای بدنه: دسته‌بندی + تاریخ مقاله --}}
                            <div class="flex items-center justify-between gap-3">
                                @if($blog->category)
                                    <a href="{{ localizedRoute('blogs.category', ['category' => $blog->category->slug]) }}"
                                       class="text-[11px] font-bold uppercase tracking-wide truncate" style="color: var(--accent);">
                                        {{ $blog->category->title }}
                                    </a>
                                @endif
                                <span class="blog-date" title="{{ $blog->localizedDate() }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                    </svg>
                                    {{ $blog->localizedDate() }}
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm leading-snug line-clamp-2 min-h-[2.6rem]">
                                <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}"
                                   class="hover:text-[var(--accent)] transition-colors">
                                    {{ $blog->title }}
                                </a>
                            </h3>
                            @if($blog->excerpt)
                                <p class="text-xs leading-relaxed line-clamp-2" style="color: var(--text-secondary);">
                                    {{ $blog->excerpt }}
                                </p>
                            @endif
                            {{-- فوتر فشرده کارت: آمار + ادامه مطلب با خط جداکننده --}}
                            <div class="mt-auto pt-3 flex items-center justify-between gap-3"
                                 style="border-top: 1px solid color-mix(in srgb, var(--text-secondary) 20%, transparent);">
                                <div class="flex items-center gap-2 text-[11px] font-semibold" style="color: var(--text-secondary);">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        <span class="mono" dir="ltr">{{ number_format($blog->views) }}</span>
                                    </span>
                                    <span aria-hidden="true" style="opacity: .45;">·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        {{ __('messages.reading_time', ['minutes' => $blog->readingMinutes()]) }}
                                    </span>
                                </div>
                                <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}"
                                   class="text-[11px] font-bold inline-flex items-center gap-1 hover:text-[var(--accent)] transition-colors shrink-0">
                                    {{ __('messages.read_more') }}
                                    <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۹. بنر خبرنامه یا اخبار --}}
    @include('site.home.news')

@endsection
