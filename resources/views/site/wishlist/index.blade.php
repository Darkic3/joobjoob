@extends('layouts.site.app')

@section('title', __('messages.wishlist'))

@section('content')
    <div class="reveal-on-scroll">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-8">
            <h1 class="text-2xl font-black" style="color: var(--text-primary);">{{ __('messages.wishlist') }}</h1>
            @if($items->isNotEmpty())
                <span class="neu-flat-sm px-4 py-2 text-sm font-bold rounded-full" style="color: var(--accent);">
                    {{ number_format($items->count()) }}
                </span>
            @endif
        </div>

        @if($items->isEmpty())
            <div class="neu-card text-center py-20 px-6">
                <div class="neu-icon-circle w-20 h-20 mx-auto grid place-items-center mb-5">
                    <svg class="w-9 h-9" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 0 1 0-6.364Z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold mb-2">{{ __('messages.wishlist_empty') }}</h2>
                <p class="text-sm mb-6" style="color: var(--text-secondary);">
                    {{ __('messages.wishlist_empty_desc') }}
                </p>
                <a href="{{ localizedRoute('products.index') }}" class="neu-btn px-6 py-3 text-sm font-bold rounded-full"
                   style="background: var(--accent); color: var(--accent-contrast);">
                    {{ __('messages.continue_shopping') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5" data-stagger>
                @foreach($items as $product)
                    <x-product-card :product="$product"/>
                @endforeach
            </div>
        @endif
    </div>
@endsection
