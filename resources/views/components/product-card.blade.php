@props(['product'])

<div class="neu-card neu-interactive">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="relative rounded-xl overflow-hidden mb-3" style="aspect-ratio: 1/1;">
            <img src="{{ $product->images->first()->url ?? 'https://picsum.photos/400' }}"
                 alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">

            @if($product->discount_price)
                <span class="absolute top-2 start-2 neu-badge">
                    {{ round((1 - $product->discount_price / $product->price) * 100) }}%-
                </span>
            @endif
        </div>

        <h3 class="font-semibold text-sm mb-1 line-clamp-2">{{ $product->name }}</h3>

        <div class="flex items-center gap-2 mb-2">
            @if($product->discount_price)
                <span class="font-bold" style="color: var(--accent);">{{ number_format($product->discount_price) }}</span>
                <span class="text-xs line-through" style="color: var(--text-secondary);">{{ number_format($product->price) }}</span>
            @else
                <span class="font-bold" style="color: var(--accent);">{{ number_format($product->price) }}</span>
            @endif
        </div>
    </a>

    <form action="{{ route('cart.add', $product) }}" method="POST">
        @csrf
        <button type="submit" class="neu-btn neu-btn-primary w-full text-xs">{{ __('messages.add_to_cart') }}</button>
    </form>
</div>
