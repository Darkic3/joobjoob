@extends("layouts.site.app")
@section("title", __("messages.wishlist"))
@section("content")
<div class="grid md:grid-cols-4 gap-6">
    <div class="md:col-span-1">
        <div class="neu-card p-4 h-fit sticky top-24">
            @include("site.dashboard._sidebar")
        </div>
    </div>
    <div class="md:col-span-3 space-y-5">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl grid place-items-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </div>
            <h2 class="font-bold" style="color: var(--text-primary);">{{ __("messages.wishlist") }}</h2>
            @if($products->count() > 0)
                <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: color-mix(in srgb, var(--accent) 10%, transparent); color: var(--accent);">{{ $products->count() }}</span>
            @endif
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($products as $product)
                    <x-product-card :product="$product"/>
                @endforeach
            </div>
        @else
            <div class="neu-card text-center py-12">
                <div class="w-16 h-16 rounded-3xl grid place-items-center mx-auto mb-4" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                </div>
                <p class="text-sm font-bold mb-1" style="color: var(--text-primary);">{{ __("messages.wishlist_empty") }}</p>
                <p class="text-xs mb-3" style="color: var(--text-secondary);">{{ __("messages.wishlist_empty_desc") }}</p>
                <a href="{{ localizedRoute("products.index") }}" class="neu-btn neu-btn-primary px-5 py-2.5 text-xs font-bold rounded-full">{{ __("messages.continue_shopping") }}</a>
            </div>
        @endif
    </div>
</div>
@endsection
