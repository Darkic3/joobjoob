{{--@props(['product'])--}}

{{--<div class="neu-card neu-interactive">--}}
{{--    <a href="{{ route('products.show', $product) }}" class="block">--}}
{{--        <div class="relative rounded-xl overflow-hidden mb-3" style="aspect-ratio: 1/1;">--}}
{{--            <img src="{{ $product->images->first()->url ?? 'https://picsum.photos/400' }}"--}}
{{--                 alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">--}}

{{--            @if($product->discount_price)--}}
{{--                <span class="absolute top-2 start-2 neu-badge">--}}
{{--                    {{ round((1 - $product->discount_price / $product->price) * 100) }}%---}}
{{--                </span>--}}
{{--            @endif--}}
{{--        </div>--}}

{{--        <h3 class="font-semibold text-sm mb-1 line-clamp-2">{{ $product->name }}</h3>--}}

{{--        <div class="flex items-center gap-2 mb-2">--}}
{{--            @if($product->discount_price)--}}
{{--                <span class="font-bold" style="color: var(--accent);">{{ number_format($product->discount_price) }}</span>--}}
{{--                <span class="text-xs line-through" style="color: var(--text-secondary);">{{ number_format($product->price) }}</span>--}}
{{--            @else--}}
{{--                <span class="font-bold" style="color: var(--accent);">{{ number_format($product->price) }}</span>--}}
{{--            @endif--}}
{{--        </div>--}}
{{--    </a>--}}

{{--    <form action="{{ route('cart.add', $product) }}" method="POST">--}}
{{--        @csrf--}}
{{--        <button type="submit" class="neu-btn neu-btn-primary w-full text-xs">{{ __('messages.add_to_cart') }}</button>--}}
{{--    </form>--}}
{{--</div>--}}

<div class="neu-card neu-product-card group relative">
    {{-- بج تخفیف --}}
    @if($product->discount_percentage)
        <div class="absolute -top-2 -end-2 neu-badge z-10">
            {{ $product->discount_percentage }}%
        </div>
    @endif

    {{-- دکمه علاقه‌مندی --}}
    <button data-toggle-wishlist class="absolute top-3 end-3 w-8 h-8 neu-flat-sm flex items-center justify-center rounded-full z-10 transition-transform hover:scale-110">
        <i class="far fa-heart text-sm" style="color: var(--text-secondary)"></i>
    </button>

    {{-- تصویر محصول --}}
    <div class="relative overflow-hidden rounded-xl mb-3">
        <img src="{{ $product->images->first()->url ?? 'https://picsum.photos/400' }}"
             alt="{{ $product->name }}"  loading="lazy"
             class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110">

        {{-- دکمه‌های سریع --}}
        <div class="absolute inset-x-0 bottom-0 p-3 flex gap-2 opacity-0 translate-y-full group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
            <button data-quick-view data-product-id="{{ $product->id }}"
                    class="flex-1 neu-btn neu-btn-sm py-2 text-xs">
                <i class="fas fa-eye"></i> {{ __('messages.quick_view') }}
            </button>
        </div>
    </div>

    {{-- اطلاعات محصول --}}
    <h3 class="font-bold text-sm mb-2 line-clamp-2">{{ $product->name }}</h3>

    <div class="flex items-center justify-between mb-3">
        @if($product->discount_percentage)
            <div>
                <span class="text-xs line-through" style="color: var(--text-secondary)">
                    {{ number_format($product->original_price) }}
                </span>
                <div class="font-bold text-sm" style="color: var(--accent)">
                    {{ number_format($product->price) }} تومان
                </div>
            </div>
        @else
            <div class="font-bold text-sm">
                {{ number_format($product->price) }} تومان
            </div>
        @endif
    </div>

    {{-- دکمه افزودن به سبد --}}
    <button data-add-to-cart data-product-id="{{ $product->id }}"
            class="w-full neu-btn neu-btn-primary py-2 text-sm">
        <i class="fas fa-cart-plus"></i> {{ __('messages.add_to_cart') }}
    </button>
</div>
