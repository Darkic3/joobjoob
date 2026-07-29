@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="grid md:grid-cols-2 gap-10 mb-14">

    {{-- گالری تصاویر --}}
    <div>
        <div class="neu-flat-lg p-4 mb-4">
            <img id="main-image" src="{{ $product->images->first()->url ?? 'https://picsum.photos/600' }}"
                 alt="{{ $product->name }}" class="w-full rounded-xl object-cover" style="aspect-ratio:1/1;">
        </div>
        <div class="flex gap-3">
            @foreach($product->images as $img)
                <button onclick="document.getElementById('main-image').src='{{ $img->url }}'"
                        class="neu-flat-sm p-1 w-20 h-20 overflow-hidden">
                    <img src="{{ $img->url }}" class="w-full h-full object-cover rounded-lg">
                </button>
            @endforeach
        </div>
    </div>

    {{-- اطلاعات محصول --}}
    <div>
        <h1 class="text-2xl font-extrabold mb-2">{{ $product->name }}</h1>

        <div class="flex items-center gap-2 mb-4">
            <span style="color: var(--accent);">{{ str_repeat('★', round($product->average_rating)) }}{{ str_repeat('☆', 5 - round($product->average_rating)) }}</span>
            <span class="text-sm" style="color: var(--text-secondary);">({{ $product->reviews->count() }} {{ __('messages.reviews') }})</span>
        </div>

        <div class="flex items-center gap-3 mb-6">
            @if($product->discount_price)
                <span class="text-2xl font-extrabold" style="color: var(--accent);">{{ number_format($product->discount_price) }}</span>
                <span class="text-lg line-through" style="color: var(--text-secondary);">{{ number_format($product->price) }}</span>
            @else
                <span class="text-2xl font-extrabold" style="color: var(--accent);">{{ number_format($product->price) }}</span>
            @endif
        </div>

        <p class="mb-6" style="color: var(--text-secondary);">{{ $product->description }}</p>

        <form action="{{ route('cart.add', $product) }}" method="POST" class="space-y-5">
            @csrf

            @if($product->colors)
                <div>
                    <label class="text-sm font-semibold block mb-2">{{ __('messages.select_color') }}</label>
                    <div class="flex gap-2">
                        @foreach($product->colors as $color)
                            <label class="neu-flat-sm px-4 py-2 cursor-pointer text-sm has-[:checked]:font-bold">
                                <input type="radio" name="color" value="{{ $color }}" class="hidden" @checked($loop->first)>
                                {{ $color }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($product->sizes)
                <div>
                    <label class="text-sm font-semibold block mb-2">{{ __('messages.select_size') }}</label>
                    <div class="flex gap-2">
                        @foreach($product->sizes as $size)
                            <label class="neu-flat-sm px-4 py-2 cursor-pointer text-sm has-[:checked]:font-bold">
                                <input type="radio" name="size" value="{{ $size }}" class="hidden" @checked($loop->first)>
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3">
                <input type="number" name="qty" value="1" min="1" class="neu-input w-24">
                <button type="submit" class="neu-btn neu-btn-primary flex-1">{{ __('messages.add_to_cart') }}</button>
            </div>

            <p class="text-sm" style="color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ $product->stock > 0 ? __('messages.in_stock') : __('messages.out_of_stock') }}
            </p>
        </form>
    </div>
</div>

{{-- نظرات کاربران --}}
<section class="mb-14">
    <h2 class="text-xl font-bold mb-5">{{ __('messages.reviews') }} ({{ $product->reviews->count() }})</h2>
    <div class="grid md:grid-cols-2 gap-4">
        @forelse($product->reviews as $review)
            <div class="neu-card">
                <div class="mb-2" style="color: var(--accent);">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                <p class="text-sm mb-2" style="color: var(--text-secondary);">{{ $review->comment }}</p>
                <p class="text-xs font-semibold">{{ $review->user->name ?? '' }}</p>
            </div>
        @empty
            <p style="color: var(--text-secondary);">—</p>
        @endforelse
    </div>
</section>

{{-- محصولات مرتبط --}}
@if($related->isNotEmpty())
    <section>
        <h2 class="text-xl font-bold mb-5">{{ __('messages.related_products') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($related as $item)
                <x-product-card :product="$item" />
            @endforeach
        </div>
    </section>
@endif
@endsection
