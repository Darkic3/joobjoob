@extends('layouts.site.app')

@section('main_class', 'pt-0 pb-6')

@push('styles')
    {{-- فونت نمایشی تیتر هیرو (Estedad) — فقط در صفحهٔ اصلی --}}
    <link rel="preload" href="{{ asset('fonts/estedad/Estedad-Black.woff2') }}" as="font" type="font/woff2" crossorigin>
@endpush

@section('title', __('messages.site_name') . ' - ' . __('messages.home'))

@section('content')

    {{-- ۱. هیرو مینیمال (تک‌اسلاید) --}}
    @include('site.home.hero', ['heroSlide' => $heroSlide])

    {{-- ۲. دسته‌بندی‌ها — Playful Brutalism × Neo-Tokyo Minimal --}}
    @if($categories->isNotEmpty())
        <section class="categories-brutal reveal-on-scroll mt-12 mb-10">
            <header class="categories-brutal-head">
                <div class="categories-brutal-head-main">
                    <span class="categories-brutal-coords" aria-hidden="true">SOCK://SHOP — N°02</span>
                    <div class="categories-brutal-kicker">
                        <span class="categories-brutal-kicker-dot" aria-hidden="true"></span>
                        <span>{{ __('messages.categories') }}</span>
                        <span class="categories-brutal-kicker-num" aria-hidden="true">/ {{ $categories->count() }}</span>
                    </div>
                    <h2 class="categories-brutal-title">{{ settingOverride('home_categories_title', __('messages.categories')) }}</h2>
                </div>
                <a href="{{ localizedRoute('products.index') }}" class="categories-brutal-link">
                    <span>{{ settingOverride('home_view_all', __('messages.view_all')) }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </header>

            {{-- کالکشن کارت‌ها: چیدمان ریتمیک با چرخش متناوب، ورود پله‌ای --}}
            <div class="categories-brutal-grid" data-stagger>
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

    {{-- ۶.۵. بنرهای میانی تبلیغاتی — مدل PromoBanner؛ تک‌بنر تمام‌عرض یا اسلایدرِ بنرها (Swiper) --}}
    @if($promoBanners->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            @if($promoBanners->count() === 1)
                {{-- فقط یک بنر: تمام‌عرض، بدون کروم اسلایدر --}}
                <div class="promo-banners promo-banners--single">
                    <x-promo-banner-card :banner="$promoBanners->first()"/>
                </div>
            @else
                {{-- دو یا چند بنر: اسلایدر کشویی — در موبایل یکی‌یکی، در دسکتاپ چندتایی --}}
                <div class="promo-swiper" data-promo-swiper>
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            @foreach($promoBanners as $banner)
                                <div class="swiper-slide">
                                    <x-promo-banner-card :banner="$banner"/>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" class="promo-swiper__nav promo-swiper__nav--prev" data-promo-prev
                            aria-label="{{ __('messages.prev_slide') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m0 0 6-6m-6 6 6 6"/></svg>
                    </button>
                    <button type="button" class="promo-swiper__nav promo-swiper__nav--next" data-promo-next
                            aria-label="{{ __('messages.next_slide') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </button>

                    <div class="promo-swiper__pagination swiper-pagination" aria-hidden="true"></div>
                </div>

                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const wrap = document.querySelector('[data-promo-swiper]');
                            if (!wrap || typeof Swiper === 'undefined') return;

                            const slider = new Swiper(wrap.querySelector('.swiper'), {
                                // جهت از سند خوانده می‌شود تا در LTR (انگلیسی و…) هم درست بچرخد
                                rtl: document.documentElement.dir === 'rtl',
                                slidesPerView: 1,
                                spaceBetween: 20,
                                speed: 550,
                                loop: false,
                                breakpoints: {
                                    640: { slidesPerView: 2 },
                                    1024: { slidesPerView: 3 },
                                    1280: { slidesPerView: 4 },
                                },
                                pagination: {
                                    el: wrap.querySelector('.promo-swiper__pagination'),
                                    clickable: true,
                                },
                            });

                            const prev = wrap.querySelector('[data-promo-prev]');
                            const next = wrap.querySelector('[data-promo-next]');
                            if (prev) prev.addEventListener('click', () => slider.slidePrev());
                            if (next) next.addEventListener('click', () => slider.slideNext());
                        });
                    </script>
                @endpush
            @endif
        </section>
    @endif

    {{-- ۷. نظرات مشتریان — سوشال پروف: شل + کور Double-Bezel + ورود پله‌ای در دو لایه --}}
    @if($reviews->isNotEmpty())
        <section class="reveal-on-scroll py-8">
            {{-- شل بیرونی: قاب گرادینتی دورِ کور --}}
            <div class="reviews-shell">
                {{-- کور داخلی: محتوای اصلی --}}
                <div class="reviews-core relative overflow-hidden">
                    {{-- حباب‌های رنگی شناور پس‌زمینه --}}
                    <div class="reviews-blobs" aria-hidden="true">
                        <span class="reviews-blob reviews-blob--1"></span>
                        <span class="reviews-blob reviews-blob--2"></span>
                        <span class="reviews-blob reviews-blob--3"></span>
                    </div>

                    <div class="relative z-10">
                        {{-- سربرگ: برچسب + عنوان + خلاصه امتیاز کلی — ورود پله‌ای --}}
                        <div class="reviews-head" data-stagger>
                            <div class="reviews-head-main">
                                <span class="reviews-eyebrow">
                                    <span class="live-dot" aria-hidden="true"></span>
                                    {{ __('messages.reviews_eyebrow') }}
                                </span>
                                <h2 class="section-title">{{ settingOverride('home_reviews_title', __('messages.customer_reviews')) }}</h2>
                                <p class="section-subtitle">{{ __('messages.review_average') }}</p>
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

                        {{-- چرخش نظرات: بچ‌های ۳تایی (۱ برجسته + ۲ ثانویه) با کراس‌فید — data-reviews-rotator --}}
                        <div class="reviews-rotator" data-reviews-rotator data-reviews-interval="6000"
                             role="region" aria-roledescription="carousel" aria-label="{{ __('messages.customer_reviews') }}">
                            @foreach($reviewBatches as $batchIndex => $batch)
                                <div class="reviews-batch {{ $batchIndex === 0 ? 'is-active' : '' }}"
                                     data-reviews-batch="{{ $batchIndex }}"
                                     aria-hidden="{{ $batchIndex === 0 ? 'false' : 'true' }}">
                                    <div class="grid lg:grid-cols-2 gap-5">
                                        @foreach($batch as $review)
                                            @if($loop->first)
                                                {{-- کارت برجسته (سوشال پروف اصلی) — Double-Bezel کامل --}}
                                                <div class="review-card review-card--featured lg:col-span-2 group">
                                                    <div class="review-card-inner">
                                                        {{-- نوار گرادینتی بالای کور --}}
                                                        <span class="review-card-accent" aria-hidden="true"></span>
                                                        {{-- کوتیشن تزئینی بزرگ --}}
                                                        <svg class="review-quote-mark" viewBox="0 0 24 24" width="96" height="96" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 11H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4"/><path d="M20 11h-4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6a4 4 0 0 1-4 4"/></svg>

                                                        <div class="review-card-content" data-stagger>
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
                                                </div>
                                            @else
                                                {{-- کارت‌های ثانویه — Double-Bezel --}}
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
                            @endforeach

                            {{-- دات‌های پیشرفت بچ‌ها — کلیک = پرش به آن بچ --}}
                            <div class="reviews-dots" role="tablist" aria-label="{{ __('messages.customer_reviews') }}">
                                @foreach($reviewBatches as $batchIndex => $batch)
                                    <button type="button"
                                            class="reviews-dot {{ $batchIndex === 0 ? 'is-active' : '' }}"
                                            data-reviews-dot="{{ $batchIndex }}"
                                            role="tab"
                                            aria-selected="{{ $batchIndex === 0 ? 'true' : 'false' }}"
                                            aria-label="{{ __('messages.review_batch', ['num' => $batchIndex + 1]) }}"
                                            tabindex="{{ $batchIndex === 0 ? '0' : '-1' }}">
                                        <span class="reviews-dot-fill" aria-hidden="true"></span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- دکمه‌های قبلی/بعدی — هماهنگ با ناوبری هیرو (در موبایل فقط دات‌ها می‌مانند) --}}
                            <button type="button" class="reviews-nav reviews-nav--next" data-reviews-next
                                    aria-label="{{ __('messages.reviews_next') }}"></button>
                            <button type="button" class="reviews-nav reviews-nav--prev" data-reviews-prev
                                    aria-label="{{ __('messages.reviews_prev') }}"></button>
                        </div>
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

    {{-- ۹. مشاهده اخیر --}}
    @if($recentlyViewed->isNotEmpty())
        <section class="reveal-on-scroll py-6">
            <div class="section-head mb-6">
                <div class="section-head-main">
                    <span class="section-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h2 class="section-title">{{ __('messages.recently_viewed') }}</h2>
                        <p class="section-subtitle">{{ __('messages.recently_viewed_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="hscroll-wrap" data-hscroll>
                <div class="hscroll-rail flex gap-5 overflow-x-auto pb-4 custom-scrollbar scroll-smooth" dir="ltr">
                    @foreach($recentlyViewed as $rv)
                        <div class="hscroll-item shrink-0 w-[180px] md:w-[200px]">
                            <x-product-card :product="$rv" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ۱۰. بنر خبرنامه یا اخبار --}}
    @include('site.home.socials')

@endsection
