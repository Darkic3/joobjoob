@extends('layouts.app')

@section('title', __('messages.site_name'))

@section('content')

    {{-- Hero Slider --}}
    <section class="neu-flat-lg p-6 md:p-10 mb-10 relative overflow-hidden">
        <div data-hero-slide class="grid md:grid-cols-2 items-center gap-6">
            <div>
                <h1 class="text-2xl md:text-4xl font-extrabold mb-4">{{ __('messages.hero_title') ?? 'بهترین محصولات با بهترین قیمت' }}</h1>
                <p class="mb-6" style="color: var(--text-secondary);">{{ __('messages.hero_subtitle') ?? 'خرید آسان، ارسال سریع، ضمانت اصالت کالا' }}</p>
                <a href="{{ route('products.index') }}" class="neu-btn neu-btn-primary">{{ __('messages.view_all') }}</a>
            </div>
            <img src="https://picsum.photos/seed/hero/700/400" class="rounded-2xl w-full object-cover" alt="banner">
        </div>
        <div data-hero-slide class="hidden grid md:grid-cols-2 items-center gap-6">
            <div>
                <h1 class="text-2xl md:text-4xl font-extrabold mb-4">{{ __('messages.special_offers') }}</h1>
                <p class="mb-6" style="color: var(--text-secondary);">تا ۵۰٪ تخفیف روی محصولات منتخب</p>
                <a href="{{ route('products.index') }}" class="neu-btn neu-btn-primary">{{ __('messages.view_all') }}</a>
            </div>
            <img src="https://picsum.photos/seed/hero2/700/400" class="rounded-2xl w-full object-cover" alt="banner">
        </div>
    </section>

    {{-- دسته‌بندی محصولات --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold mb-5">{{ __('messages.categories') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($categories as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    </section>

    {{-- پیشنهادات ویژه با تایمر --}}
    @if($deals->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-5">{{ __('messages.special_offers') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($deals as $product)
                    <div class="neu-card">
                        <x-product-card :product="$product" />
                        <div class="neu-pressed text-center py-2 mt-3 font-mono font-bold"
                             data-countdown="{{ $product->deal_ends_at->toIso8601String() }}">
                            --:--:--
                        </div>
                        <p class="text-xs text-center mt-1" style="color: var(--text-secondary);">{{ __('messages.deal_ends_in') }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- محصولات ویژه --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">{{ __('messages.featured_products') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm" style="color: var(--accent);">{{ __('messages.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- پرفروش‌ترین‌ها --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">{{ __('messages.best_sellers') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm" style="color: var(--accent);">{{ __('messages.view_all') }}</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($bestSellers as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- نظرات مشتریان --}}
    @if($reviews->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-xl font-bold mb-5">{{ __('messages.customer_reviews') }}</h2>
            <div class="grid md:grid-cols-3 gap-5">
                @foreach($reviews as $review)
                    <div class="neu-card">
                        <div class="mb-2" style="color: var(--accent);">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <p class="text-sm mb-3" style="color: var(--text-secondary);">{{ $review->comment }}</p>
                        <p class="text-xs font-semibold">{{ $review->user->name ?? '' }} — {{ $review->product->name ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

@endsection
