@extends('layouts.app')
@section('title', __('messages.site_name'))

@section('content')
    {{-- ۱. Hero Section (بنر اصلی) --}}
    <section class="neu-flat-lg p-8 md:p-12 mb-12 relative overflow-hidden">
        {{-- دکوراسیون پس‌زمینه --}}
        <div class="absolute -top-20 -end-20 w-72 h-72 rounded-full opacity-20 pointer-events-none" style="background: var(--accent); filter: blur(80px);"></div>
        <div class="absolute -bottom-20 -start-20 w-64 h-64 rounded-full opacity-10 pointer-events-none" style="background: var(--success); filter: blur(60px);"></div>

        <div class="grid md:grid-cols-2 items-center gap-8 relative z-10">
            <div>
                <span class="neu-badge neu-badge-success mb-4 inline-block">{{ __('messages.new_arrivals') ?? 'جدید' }}</span>
                <h1 class="text-3xl md:text-5xl font-extrabold mb-4 leading-tight">
                    {{ __('messages.hero_title') ?? 'بهترین محصولات با بهترین قیمت' }}
                </h1>
                <p class="text-lg mb-8" style="color: var(--text-secondary);">
                    {{ __('messages.hero_subtitle') ?? 'خرید آسان، ارسال سریع، ضمانت اصالت کالا' }}
                </p>
                <div class="flex gap-4">
                    <a href="{{ route('products.index') }}" class="neu-btn neu-btn-primary text-base px-8 py-3">
                        {{ __('messages.view_all') ?? 'مشاهده محصولات' }}
                    </a>
                    <a href="#special-offers" class="neu-btn text-base px-8 py-3">
                        {{ __('messages.special_offers') ?? 'پیشنهادات ویژه' }}
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="neu-flat p-4 rounded-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    <img src="https://picsum.photos/seed/hero-main/700/400" class="rounded-xl w-full object-cover shadow-inner" alt="banner">
                </div>
            </div
        </div>
    </section>

    {{-- ۲. Trust Badges (سیگنال‌های اعتماد - روانشناسی فروش) --}}
    <x-trust-badges />

    {{-- ۳. دسته‌بندی محصولات --}}
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">{{ __('messages.categories') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold hover:underline" style="color: var(--accent);">{{ __('messages.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($categories as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    </section>

    {{-- ۴. پیشنهادات ویژه با تایمر --}}
    @if($deals->isNotEmpty())
        <section id="special-offers" class="mb-16">
            <div class="neu-flat-lg p-6 md:p-8 relative overflow-hidden" style="background: linear-gradient(135deg, var(--bg-elevated) 0%, color-mix(in srgb, var(--danger) 5%, var(--bg-elevated)) 100%);">
                <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold flex items-center gap-2">
                            <span class="text-3xl">🔥</span> {{ __('messages.special_offers') }}
                        </h2>
                        <p class="text-sm mt-1" style="color: var(--text-secondary);">{{ __('messages.deal_ends_in') }}</p>
                    </div>
                    {{-- تایمر کلی --}}
                    <div class="neu-pressed px-6 py-3 rounded-xl font-mono font-bold text-xl tracking-widest" style="color: var(--danger);" data-countdown="{{ $deals->first()->deal_ends_at->toIso8601String() }}">
                        --:--:--
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    @foreach($deals as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ۵. محصولات ویژه --}}
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">{{ __('messages.featured_products') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold hover:underline" style="color: var(--accent);">{{ __('messages.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div
    </section>

    {{-- ۶. بنر تبلیغاتی میانی --}}
    <section class="mb-16">
        <div class="grid md:grid-cols-2 gap-6">
            <div class="neu-card relative overflow-hidden group cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-black/10 group-hover:to-black/20 transition-all"></div>
                <div class="relative z-10 p-6">
                    <h3 class="text-xl font-bold mb-2">{{ __('messages.new_arrivals') }}</h3>
                    <p class="text-sm mb-4" style="color: var(--text-secondary);">جدیدترین محصولات رو همین حالا ببینید</p>
                    <span class="neu-btn neu-btn-primary text-sm">مشاهده کنید &larr;</span>
                </div>
                <img src="https://picsum.photos/seed/banner1/400/200" class="absolute -bottom-10 -left-10 w-48 h-48 object-cover rounded-2xl opacity-80 group-hover:scale-110 transition-transform duration-500" alt="">
            </div>

            <div class="neu-card relative overflow-hidden group cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-l from-transparent to-black/10 group-hover:to-black/20 transition-all"></div>
                <div class="relative z-10 p-6 text-right md:text-left"> {{-- برای RTL درست کار کند --}}
                    <h3 class="text-xl font-bold mb-2">{{ __('messages.best_sellers') }}</h3>
                    <p class="text-sm mb-4" style="color: var(--text-secondary);">محبوب‌ترین انتخاب‌های مشتریان</p>
                    <span class="neu-btn neu-btn-primary text-sm">مشاهده کنید &larr;</span>
                </div>
                <img src="https://picsum.photos/seed/banner2/400/200" class="absolute -top-10 -right-10 w-48 h-48 object-cover rounded-2xl opacity-80 group-hover:scale-110 transition-transform duration-500" alt="">
            </div>
        </div>
    </section>

    {{-- ۷. نظرات مشتریان --}}
    @if($reviews->isNotEmpty())
        <section class="mb-16">
            <h2 class="text-2xl font-bold mb-6 text-center">{{ __('messages.customer_reviews') }}</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($reviews as $review)
                    <div class="neu-card relative">
                        <div class="absolute -top-3 -end-3 text-4xl opacity-20" style="color: var(--accent);">❝</div>
                        <div class="mb-3 flex items-center gap-1" style="color: var(--accent);">
                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                        </div>
                        <p class="text-sm mb-4 leading-relaxed" style="color: var(--text-secondary);">{{ $review->comment }}</p>
                        <div class="flex items-center gap-3 pt-3 border-t" style="border-color: var(--shadow-dark);">
                            <div class="w-10 h-10 rounded-full neu-pressed flex items-center justify-center font-bold text-sm">
                                {{ mb_substr($review->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold">{{ $review->user->name ?? 'کاربر' }}</p>
                                <p class="text-[10px]" style="color: var(--text-secondary);">{{ $review->product->name ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
