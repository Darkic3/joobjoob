@extends('layouts.site.app')

@section('title', __('messages.cart'))

@section('content')
    <div class="reveal-on-scroll">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-8">
            <h1 class="text-2xl font-black" style="color: var(--text-primary);">{{ __('messages.cart') }}</h1>
            @if($items->isNotEmpty())
                <span class="neu-flat-sm px-4 py-2 text-sm font-bold rounded-full" style="color: var(--accent);">
                    {{ number_format($items->sum('qty')) }} {{ __('messages.quantity') }}
                </span>
            @endif
        </div>

        @if($items->isEmpty())
            <div class="neu-card text-center py-20 px-6">
                <div class="neu-icon-circle w-20 h-20 mx-auto grid place-items-center mb-5">
                    <svg class="w-9 h-9" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold mb-2">{{ __('messages.cart_empty') }}</h2>
                <p class="text-sm mb-6" style="color: var(--text-secondary);">
                    {{ __('messages.cart_empty_desc') }}
                </p>
                <a href="{{ localizedRoute('products.index') }}" class="neu-btn px-6 py-3 text-sm font-bold rounded-full"
                   style="background: var(--accent); color: var(--accent-contrast);">
                    {{ __('messages.continue_shopping') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- اقلام سبد --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach($items as $item)
                        <div class="neu-card p-4 flex items-center gap-4">
                            @if($item['image'])
                                <a href="{{ !empty($item['slug']) ? localizedRoute('products.show', ['product' => $item['slug']]) : localizedRoute('products.index') }}"
                                   class="shrink-0 block w-24 h-24 rounded-2xl overflow-hidden neu-flat-sm">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover" loading="lazy"
                                         width="96" height="96">
                                </a>
                            @endif

                            <div class="flex-1 min-w-0">
                                <a href="{{ !empty($item['slug']) ? localizedRoute('products.show', ['product' => $item['slug']]) : localizedRoute('products.index') }}"
                                   class="font-bold text-sm md:text-base block truncate hover:opacity-70 transition-opacity"
                                   style="color: var(--text-primary);">
                                    {{ $item['name'] }}
                                </a>
                                @if(!empty($item['variant_label']))
                                    <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ $item['variant_label'] }}</p>
                                @endif
                                <p class="text-xs mt-1" style="color: var(--text-secondary);">
                                    {{ __('messages.price') }}:
                                    <span class="mono font-bold" dir="ltr">{{ number_format($item['price']) }}</span>
                                    <span class="text-[10px]"> {{ __('messages.toman') }}</span>
                                </p>
                                @if($item['stock'] <= 0)
                                    <p class="text-xs mt-1 font-bold" style="color: var(--danger);">
                                        {{ __('messages.out_of_stock') }}
                                    </p>
                                @elseif($item['stock'] <= 3)
                                    <p class="text-xs mt-1 font-bold" style="color: var(--danger);">
                                        {{ __('messages.low_stock') }} ({{ $item['stock'] }} {{ __('messages.left') }})
                                    </p>
                                @endif

                                <div class="flex items-center gap-3 mt-3 flex-wrap">
                                    {{-- تغییر تعداد --}}
                                    <form action="{{ localizedRoute('cart.update', ['rowId' => $item['rowId']]) }}"
                                          method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex items-center gap-0.5 neu-flat-sm rounded-full p-1">
                                            <button type="button"
                                                    data-cart-qty="dec"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold transition-all duration-200"
                                                    style="background: var(--accent); color: var(--accent-contrast);"
                                                    aria-label="{{ __('messages.quantity') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                            <input type="number" name="qty" value="{{ $item['qty'] }}" min="1"
                                                   max="{{ $item['stock'] }}"
                                                   class="w-12 bg-transparent text-center text-sm font-bold py-1.5 outline-none mono"
                                                   aria-label="{{ __('messages.quantity') }}" readonly>
                                            <button type="button"
                                                    data-cart-qty="inc"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold transition-all duration-200"
                                                    style="background: var(--accent); color: var(--accent-contrast);"
                                                    aria-label="{{ __('messages.quantity') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    </form>

                                    {{-- حذف --}}
                                    <form action="{{ localizedRoute('cart.remove', ['rowId' => $item['rowId']]) }}"
                                          method="POST" class="inline" data-cart-remove-form>
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                data-cart-remove
                                                class="text-xs font-bold inline-flex items-center gap-1 px-3 py-1.5 rounded-full hover:opacity-70 transition-opacity"
                                                style="color: var(--danger);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                            {{ __('messages.remove') }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-end shrink-0">
                                <p data-cart-item-total class="mono font-black text-sm md:text-base" style="color: var(--accent);" dir="ltr">
                                    {{ number_format($item['price'] * $item['qty']) }}
                                </p>
                                <p class="text-[10px]" style="color: var(--text-secondary);">{{ __('messages.toman') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- خلاصه سفارش --}}
                <div class="lg:sticky lg:top-24 space-y-4">
                    <div class="neu-card p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-bold">{{ __('messages.subtotal') }}</h2>
                            <span id="cart-subtotal" class="mono font-bold" style="color: var(--text-primary);" dir="ltr">
                                {{ number_format($cart->subtotal()) }}
                            </span>
                        </div>

                        <div id="cart-coupon-row" class="flex items-center justify-between text-sm mb-2 {{ session('coupon') ? '' : 'hidden' }}">
                                <span style="color: var(--text-secondary);">{{ __('messages.coupon_code') }} ({{ session('coupon.code') }})</span>
                                <span id="cart-coupon-amount" class="mono" style="color: var(--success);" dir="ltr">-{{ number_format($cart->discount()) }}</span>
                            </div>

                        <form action="{{ localizedRoute('cart.coupon') }}" method="POST" class="flex items-center gap-2 mb-5">
                            @csrf
                            <input type="text" name="code" value="{{ session('coupon.code') ?? '' }}"
                                   placeholder="{{ __('messages.coupon_code') }}"
                                   class="neu-input flex-1 text-sm" autocomplete="off">
                            <button type="submit" class="neu-btn px-4 py-2 text-xs font-bold rounded-full">
                                {{ __('messages.apply') }}
                            </button>
                        </form>

                        <div class="border-t pt-4 flex items-center justify-between mb-5"
                             style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                            <span class="font-bold">{{ __('messages.total') }}</span>
                            <span id="cart-total" class="mono font-black text-lg" style="color: var(--accent);" dir="ltr">
                                {{ number_format($cart->total()) }}
                            </span>
                        </div>

                        <a href="{{ localizedRoute('checkout.index') }}"
                           class="neu-btn neu-btn-primary w-full py-3 text-sm font-bold rounded-full text-center block">
                            {{ __('messages.proceed_to_checkout') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @include('site.cart.ajax-cart')
@endpush
