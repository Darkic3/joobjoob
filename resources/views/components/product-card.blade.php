@props(['product'])

@php
    $lowStock = $product->stock > 0 && $product->stock <= 5;
    $reviewsCount = $product->reviews_count ?? $product->reviews()->count();
@endphp

<div class="neu-card neu-interactive relative group">
    {{-- ۱. نشانگر کمبود موجودیت (Scarcity) --}}
    @if($lowStock)
        <div class="absolute -top-2 -end-2 z-20 animate-bounce">
            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-lg flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                فقط {{ $product->stock }} عدد!
            </span>
        </div>
    @elseif($product->discount_price)
        <span class="absolute top-2 start-2 z-10 neu-badge">
            {{ round((1 - $product->discount_price / $product->price) * 100) }}٪ تخفیف
        </span>
    @endif

    {{-- ۲. دکمه علاقه‌مندی (Wishlist) --}}
    <button class="absolute top-2 end-2 z-10 w-8 h-8 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur flex items-center justify-center text-gray-400 hover:text-red-500 hover:scale-110 transition-all shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
    </button>

    {{-- بقیه کدهای کارت محصول (تصویر، نام، قیمت و دکمه افزودن به سبد) --}}
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="relative rounded-xl overflow-hidden mb-3" style="aspect-ratio: 1/1;">
            <img src="{{ $product->images->first()->url ?? 'https://picsum.photos/400' }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-500">
        </div>
        <h3 class="font-semibold text-sm mb-1 line-clamp-2">{{ $product->name }}</h3>

        {{-- ۳. اثبات اجتماعی (Social Proof) --}}
        @if($reviewsCount > 0)
            <div class="flex items-center gap-1 mb-2 text-xs">
                <span class="text-amber-400">★</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($product->getAverageRatingAttribute(), 1) }}</span>
                <span class="text-gray-400">({{ $reviewsCount }} نظر)</span>
            </div>
        @endif

        <div class="flex items-center gap-2 mb-3">
            @if($product->discount_price)
                <span class="font-bold text-lg" style="color: var(--accent);">{{ number_format($product->discount_price) }}</span>
                <span class="text-xs line-through text-gray-400">{{ number_format($product->price) }}</span>
            @else
                <span class="font-bold text-lg" style="color: var(--accent);">{{ number_format($product->price) }}</span>
            @endif
        </div>
    </a>

    <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-auto">
        @csrf
        <button type="submit" class="neu-btn neu-btn-primary w-full text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __('messages.add_to_cart') }}
        </button>
    </form>
</div>
