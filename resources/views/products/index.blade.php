@extends('layouts.site.app')

@section('title', __('messages.products'))

@php
    $products = [
        ['name' => 'گوشی هوشمند مدل نوا X۲ رنگ مشکی', 'image' => 'https://picsum.photos/seed/p1/300/300', 'price' => 14900000, 'old_price' => 18600000, 'rating' => 5, 'reviews' => 421],
        ['name' => 'لپ‌تاپ اولترابوک ۱۴ اینچ نسل جدید', 'image' => 'https://picsum.photos/seed/p2/300/300', 'price' => 32500000, 'rating' => 4, 'reviews' => 158, 'stock' => 3],
        ['name' => 'هدفون بی‌سیم نویزکنسلینگ ایر پرو', 'image' => 'https://picsum.photos/seed/p3/300/300', 'price' => 890000, 'old_price' => 1480000, 'rating' => 4, 'reviews' => 112, 'is_new' => true],
        ['name' => 'ساعت هوشمند اسپرت واترپروف', 'image' => 'https://picsum.photos/seed/p4/300/300', 'price' => 2100000, 'rating' => 5, 'reviews' => 305],
        ['name' => 'کوله‌پشتی سفری ضدآب ۳۵ لیتری', 'image' => 'https://picsum.photos/seed/p5/300/300', 'price' => 1250000, 'old_price' => 1690000, 'rating' => 4, 'reviews' => 87],
        ['name' => 'کیف چرم زنانه دست‌دوز طبیعی', 'image' => 'https://picsum.photos/seed/p6/300/300', 'price' => 2300000, 'rating' => 5, 'reviews' => 214],
    ];

    $categories = [
        ['name' => 'موبایل و تبلت', 'count' => 124],
        ['name' => 'لپ‌تاپ و کامپیوتر', 'count' => 86],
        ['name' => 'پوشاک', 'count' => 210],
        ['name' => 'کفش و کیف', 'count' => 74],
    ];

    $brands = ['همه', 'سامسونگ', 'اپل', 'شیائومی'];
@endphp

@section('content')

    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        {{ __('messages.home') }} <span class="mx-1">/</span> <span style="color: var(--text-primary);">{{ __('messages.products') }}</span>
    </p>

    <div class="flex items-center justify-between mb-5 lg:hidden">
        <h1 class="font-extrabold text-lg">{{ __('messages.products') }}</h1>
        <button type="button" onclick="document.getElementById('filters-panel').classList.toggle('hidden-mobile')"
                class="neu-btn px-4 py-2 text-xs font-bold">{{ __('messages.filter') }} ⚙</button>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">

        {{-- سایدبار فیلتر --}}
        <aside id="filters-panel" class="hidden-mobile lg:block neu-flat p-5 h-fit lg:sticky lg:top-24">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-extrabold text-sm">{{ __('messages.filter') }}</h3>
                <button type="button" class="text-xs font-semibold" style="color: var(--accent);">پاک کردن</button>
            </div>

            <form method="GET" action="{{ route('products.index') }}">
                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.categories') }}</p>
                    <div class="space-y-2 text-sm">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="category[]" value="{{ $cat['name'] }}">
                                {{ $cat['name'] }}
                                <span class="mono text-[11px] ms-auto" style="color: var(--text-secondary);">{{ $cat['count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.price') }} (تومان)</p>
                    <input type="range" name="max_price" min="0" max="50000000" value="18000000" class="range-track mb-2">
                    <div class="flex justify-between text-[11px] mono" style="color: var(--text-secondary);">
                        <span>۰</span><span>۱۸,۰۰۰,۰۰۰</span>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.brand') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($brands as $i => $brand)
                            <button type="button" data-brand-chip class="chip px-3 py-1.5 text-[11px] {{ $i === 0 ? 'active' : '' }}">{{ $brand }}</button>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="neu-btn neu-btn-primary w-full text-sm py-2.5">{{ __('messages.filter') }}</button>
            </form>
        </aside>

        {{-- لیست محصولات --}}
        <div class="lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <p class="text-xs" style="color: var(--text-secondary);">
                    <span class="mono font-bold" style="color: var(--text-primary);">۴۹۴</span> {{ __('messages.products') }}
                </p>
                <div class="flex items-center gap-2">
                    <select name="sort" class="neu-input text-xs py-2 px-3">
                        <option>مرتبط‌ترین</option>
                        <option value="price_asc">{{ __('messages.price') }} ↑</option>
                        <option value="price_desc">{{ __('messages.price') }} ↓</option>
                        <option value="newest">جدیدترین</option>
                    </select>
                    <div class="neu-flat-sm flex p-1">
                        <button type="button" class="w-8 h-8 rounded-lg grid place-items-center" style="box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light);">▦</button>
                        <button type="button" class="w-8 h-8 rounded-lg grid place-items-center" style="color: var(--text-secondary);">☰</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            {{-- pagination نئومورفیک --}}
            <div class="flex items-center justify-center gap-2 mt-10">
                <button type="button" class="neu-page-item w-10 h-10 text-sm">›</button>
                <button type="button" class="neu-page-item active w-10 h-10 text-sm mono">۱</button>
                <button type="button" class="neu-page-item w-10 h-10 text-sm mono">۲</button>
                <button type="button" class="neu-page-item w-10 h-10 text-sm mono">۳</button>
                <span class="text-sm" style="color: var(--text-secondary);">...</span>
                <button type="button" class="neu-page-item w-10 h-10 text-sm mono">۱۲</button>
                <button type="button" class="neu-page-item w-10 h-10 text-sm">‹</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .range-track { -webkit-appearance: none; appearance: none; width: 100%; height: 6px; border-radius: 999px; background: var(--bg-elevated); box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light); }
        .range-track::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: var(--accent); box-shadow: 2px 2px 5px var(--shadow-dark); cursor: pointer; }
        .chip { background: var(--bg-elevated); box-shadow: 3px 3px 6px var(--shadow-dark), -3px -3px 6px var(--shadow-light); border-radius: 999px; }
        .chip.active { box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light); color: var(--accent); font-weight: 700; }
        @media (max-width: 1023px) { #filters-panel.hidden-mobile { display: none; } }
    </style>
@endpush
