@extends('layouts.site.app')

@section('title', $category->trans('name') ?? __('messages.categories'))

@section('content')
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ localizedRoute('products.index') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.products') }}</a>
        <span class="mx-1">/</span>
        <span style="color: var(--text-primary);">{{ $category->trans('name') ?? $category->slug }}</span>
    </p>

    {{-- سربرگ دسته‌بندی --}}
    <div class="neu-flat rounded-3xl p-6 md:p-8 mb-8 flex items-center gap-5">
        @if($category->image_url)
            <img src="{{ $category->image_url }}" alt="{{ $category->trans('name') }}"
                 class="w-20 h-20 md:w-24 md:h-24 rounded-2xl object-cover shrink-0">
        @endif
        <div>
            <h1 class="font-black text-xl md:text-2xl mb-1">{{ $category->trans('name') ?? $category->slug }}</h1>
            @if($category->trans('description'))
                <p class="text-xs md:text-sm leading-relaxed" style="color: var(--text-secondary);">
                    {{ $category->trans('description') }}
                </p>
            @endif
            <p class="text-xs mt-2 mono" style="color: var(--text-secondary);">
                {{ number_format($products->total()) }} {{ __('messages.product') }}
            </p>
        </div>
    </div>

    {{-- محصولات دسته --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
        @forelse($products as $product)
            <x-product-card :product="$product" />
        @empty
            <div class="col-span-full text-center py-20 neu-flat">
                <p class="text-sm" style="color: var(--text-secondary);">{{ __('messages.no_products_found') }}</p>
            </div>
        @endforelse
    </div>

    {{-- صفحه‌بندی --}}
    @if($products->hasPages())
        <div class="flex items-center justify-center gap-2 mt-10">
            @if($products->onFirstPage())
                <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">›</span>
            @else
                <a href="{{ $products->previousPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">›</a>
            @endif

            @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                @if($page == $products->currentPage())
                    <span class="neu-page-item active w-10 h-10 text-sm mono flex items-center justify-center font-bold text-[var(--accent)]">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="neu-page-item w-10 h-10 text-sm mono flex items-center justify-center hover:text-[var(--accent)] transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">‹</a>
            @else
                <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">‹</span>
            @endif
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .neu-page-item {
            background: var(--bg-elevated);
            box-shadow: 3px 3px 6px var(--shadow-dark), -3px -3px 6px var(--shadow-light);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .neu-page-item:hover {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
        }
        .neu-page-item.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
        }
    </style>
@endpush
