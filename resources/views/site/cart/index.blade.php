@extends('layouts.site.app')

@section('title', __('messages.cart'))

@section('content')
    <div class="reveal-on-scroll">
        {{-- هدر --}}
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
                <p class="text-sm mb-6" style="color: var(--text-secondary);">{{ __('messages.cart_empty_desc') }}</p>
                <a href="{{ localizedRoute('products.index') }}" class="neu-btn px-6 py-3 text-sm font-bold rounded-full"
                   style="background: var(--accent); color: var(--accent-contrast);">
                    {{ __('messages.continue_shopping') }}
                </a>
            </div>
        @else
            @if(!($isGuest ?? false))
            {{-- ═══════════════════ نشانگر مراحل ═══════════════════ --}}
            <div class="neu-card px-6 py-5 mb-8" id="step-indicator">
                <div class="flex items-center justify-center gap-0">
                    <div class="step-dot active" data-step="1">
                        <div class="step-circle"><span class="step-num">۱</span><svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
                        <span class="step-label">{{ __('messages.cart') }}</span>
                    </div>
                    <div class="step-line" id="line-1-2"></div>
                    <div class="step-dot" data-step="2">
                        <div class="step-circle"><span class="step-num">۲</span><svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
                        <span class="step-label">{{ __('messages.shipping_address') }}</span>
                    </div>
                    <div class="step-line" id="line-2-3"></div>
                    <div class="step-dot" data-step="3">
                        <div class="step-circle"><span class="step-num">۳</span><svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
                        <span class="step-label">{{ __('messages.coupon_code') }}</span>
                    </div>
                    <div class="step-line" id="line-3-4"></div>
                    <div class="step-dot" data-step="4">
                        <div class="step-circle"><span class="step-num">۴</span><svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
                        <span class="step-label">{{ __('messages.payment_method') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- ═══════════════════ مرحله ۱: اقلام سبد خرید ═══════════════════ --}}
            <div id="checkout-step-1" class="checkout-step">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2 space-y-3">
                        @foreach($items as $item)
                            <div class="neu-card p-5 flex items-center gap-5 transition-all duration-300 hover:translate-y-[-2px] group {{ empty($item['is_in_stock']) ? 'opacity-50 grayscale pointer-events-none' : '' }}">
                                <a href="{{ !empty($item['slug']) ? localizedRoute('products.show', ['product' => $item['slug']]) : localizedRoute('products.index') }}"
                                   class="shrink-0 block w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden neu-flat-sm relative">
                                     <img src="{{ $item['image'] ?? asset('assets/admin/images/image-not-found.png') }}" alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/admin/images/image-not-found.png') }}'"
                                         width="96" height="96">
                                </a>

                                <div class="flex-1 min-w-0">
                                    <a href="{{ !empty($item['slug']) ? localizedRoute('products.show', ['product' => $item['slug']]) : localizedRoute('products.index') }}"
                                       class="font-bold text-sm md:text-base block truncate hover:opacity-70 transition-opacity"
                                       style="color: var(--text-primary);">
                                        {{ $item['name'] }}
                                    </a>
                                    @if(!empty($item['variant_label']))
                                        <p class="text-xs mt-1 px-2 py-0.5 rounded-full inline-block" style="background: color-mix(in srgb, var(--accent) 10%, transparent); color: var(--accent);">{{ $item['variant_label'] }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-1.5">
                                        @if(!empty($item['original_price']) && $item['original_price'] > $item['price'])
                                            <span class="mono text-xs line-through" style="color: var(--text-secondary); opacity:.6;" dir="ltr">{{ number_format($item['original_price']) }}</span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                                                {{ round((1 - $item['price'] / $item['original_price']) * 100) }}٪-
                                            </span>
                                        @endif
                                        <span class="mono text-xs font-bold" style="color: var(--accent);" dir="ltr">{{ number_format($item['price']) }}</span>
                                        <span class="text-[10px]" style="color: var(--text-secondary);">{{ __('messages.toman') }}</span>
                                    </div>
                                    @if($item['stock'] <= 0)
                                        <p class="text-xs mt-1 font-bold" style="color: var(--danger);">{{ __('messages.out_of_stock') }}</p>
                                    @elseif($item['stock'] <= 3)
                                        <p class="text-xs mt-1 font-bold" style="color: var(--danger);">
                                            {{ __('messages.low_stock') }} ({{ $item['stock'] }} {{ __('messages.left') }})
                                        </p>
                                    @endif

                                    <div class="flex items-center gap-3 mt-3 flex-wrap">
                                        <form action="{{ localizedRoute('cart.update', ['rowId' => $item['rowId']]) }}"
                                              method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center gap-0.5 neu-flat-sm rounded-full p-1">
                                                <button type="button" data-cart-qty="dec"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold transition-all duration-200 hover:scale-110"
                                                        style="background: var(--accent); color: var(--accent-contrast);">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                </button>
                                                <input type="number" name="qty" value="{{ $item['qty'] }}" min="1"
                                                       max="{{ $item['stock'] }}"
                                                       class="w-12 bg-transparent text-center text-sm font-bold py-1.5 outline-none mono"
                                                       aria-label="{{ __('messages.quantity') }}" readonly>
                                                <button type="button" data-cart-qty="inc"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold transition-all duration-200 hover:scale-110"
                                                        style="background: var(--accent); color: var(--accent-contrast);">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                </button>
                                            </div>
                                        </form>

                                        <form action="{{ localizedRoute('cart.remove', ['rowId' => $item['rowId']]) }}"
                                              method="POST" class="inline" data-cart-remove-form>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" data-cart-remove
                                                    class="text-xs font-bold inline-flex items-center gap-1 px-3 py-1.5 rounded-full transition-all duration-200 hover:bg-red-50 dark:hover:bg-red-900/20"
                                                    style="color: var(--danger);">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    {{-- خلاصه سفارش مرحله ۱ --}}
                    <div class="lg:sticky lg:top-24 space-y-4">
                        <div class="neu-card p-6">
                            <h2 class="font-black text-base mb-5" style="color: var(--text-primary);">{{ __('messages.subtotal') }}</h2>

                            <div class="space-y-3 mb-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: var(--text-secondary);">{{ __('messages.subtotal') }}</span>
                                    <span id="cart-subtotal" class="mono font-bold" style="color: var(--text-primary);" dir="ltr">
                                        {{ number_format($cart->subtotal()) }}
                                    </span>
                                </div>

                                <div id="cart-coupon-row" class="flex items-center justify-between text-sm {{ session('coupon') ? '' : 'hidden' }}">
                                    <span class="flex items-center gap-1.5" style="color: var(--success);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        {{ session('coupon.code') ?? '' }}
                                    </span>
                                    <span id="cart-coupon-amount" class="mono font-bold" style="color: var(--success);" dir="ltr">-{{ number_format($cart->discount()) }}</span>
                                </div>
                                @php
                                    $totalSavings = $items->sum(function ($item) {
                                        $original = $item['original_price'] ?? $item['price'];
                                        return max(0, ($original - $item['price']) * $item['qty']);
                                    });
                                @endphp
                                @if($totalSavings > 0)
                                    <div id="cart-savings-row" class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-1.5" style="color: var(--success);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            {{ __('messages.your_savings_from_purchase') }}
                                        </span>
                                        <span id="cart-savings" class="mono font-bold" style="color: var(--success);" dir="ltr">{{ number_format($totalSavings) }} {{ __('messages.toman') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="border-t pt-4 flex items-center justify-between mb-6"
                                 style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                                <span class="font-bold text-base">{{ __('messages.total') }}</span>
                                <span id="cart-total" class="mono font-black text-xl" style="color: var(--accent);" dir="ltr">
                                    {{ number_format($cart->total()) }}
                                </span>
                            </div>

                            @if($isGuest ?? false)
                                <a href="{{ localizedRoute('login') }}"
                                   class="neu-btn neu-btn-primary w-full py-3.5 text-sm font-bold rounded-full text-center block transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                                    {{ __('messages.login_register') }}
                                </a>
                                <p class="text-xs text-center mt-2" style="color: var(--text-secondary);">برای تکمیل خرید وارد شوید</p>
                            @else
                                <button type="button" onclick="goToStep(2)"
                                        class="neu-btn neu-btn-primary w-full py-3.5 text-sm font-bold rounded-full text-center block transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                                    {{ __('messages.proceed_to_checkout') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(!($isGuest ?? false))
            {{-- ═══════════════════ مرحله ۲: انتخاب آدرس و روش ارسال ═══════════════════ --}}
            <div id="checkout-step-2" class="checkout-step" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2 space-y-4">
                        {{-- لیست آدرس‌ها --}}
                        <div class="neu-card p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="font-black text-base flex items-center gap-2.5" style="color: var(--text-primary);">
                                    <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                        <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    </div>
                                    {{ __('messages.shipping_address') }}
                                </h2>
                                <button type="button"
                                        onclick="document.getElementById('addAddressPanel').classList.toggle('hidden')"
                                        class="neu-btn px-4 py-2 text-xs font-bold rounded-full transition-all duration-200 hover:scale-[1.03]"
                                        style="color: var(--accent);">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        آدرس جدید
                                    </span>
                                </button>
                            </div>

                            @if($addresses->isEmpty())
                                <div class="text-center py-12">
                                    <div class="neu-icon-circle w-16 h-16 mx-auto grid place-items-center mb-4" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">
                                        <svg class="w-7 h-7" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold mb-1" style="color: var(--text-primary);">هنوز آدرسی ذخیره نکرده‌اید</p>
                                    <p class="text-xs" style="color: var(--text-secondary);">روی «آدرس جدید» بزنید تا آدرس خود را اضافه کنید</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="addressList">
                                    @foreach($addresses as $address)
                                        <div class="address-card neu-flat-sm p-4 rounded-2xl cursor-pointer transition-all duration-300"
                                             data-address-id="{{ $address->id }}"
                                             onclick="selectAddress({{ $address->id }})">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="address-radio w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all duration-300"
                                                         style="border-color: color-mix(in srgb, var(--text-primary) 25%, transparent);">
                                                        <div class="address-radio-inner w-2.5 h-2.5 rounded-full transition-all duration-300 scale-0"
                                                             style="background: var(--accent);"></div>
                                                    </div>
                                                    <p class="font-bold text-sm" style="color: var(--text-primary);">{{ $address->receiptor_full_name }}</p>
                                                </div>
                                            </div>
                                            <p class="text-xs mb-3 leading-relaxed line-clamp-2" style="color: var(--text-secondary);">{{ $address->address }}</p>
                                            <div class="flex items-center gap-2 flex-wrap text-[11px]" style="color: var(--text-secondary);">
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                                    {{ $address->state?->name }} - {{ $address->city?->name }}
                                                </span>
                                                <span aria-hidden="true" style="opacity:.3;">•</span>
                                                <span dir="ltr" class="inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                                    {{ $address->receiptor_mobile }}
                                                </span>
                                            </div>
                                            @if($address->postal_code)
                                                <p class="text-[11px] mt-1.5 inline-flex items-center gap-1" style="color: var(--text-secondary);">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                                    کدپستی: <span dir="ltr">{{ $address->postal_code }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- فرم افزودن آدرس جدید --}}
                            <div id="addAddressPanel" class="hidden mt-5 pt-5 border-t" style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="neu-icon-circle w-8 h-8 grid place-items-center shrink-0">
                                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </div>
                                    <h3 class="font-bold text-sm" style="color: var(--text-primary);">افزودن آدرس جدید</h3>
                                </div>
                                <form action="{{ localizedRoute('dashboard.addresses.create') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ localizedRoute('cart.index') }}?step=2">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">نام تحویل‌گیرنده</label>
                                            <input type="text" name="receiptor_full_name" class="neu-input w-full text-sm py-3" required>
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">موبایل</label>
                                            <input type="text" name="receiptor_mobile" class="neu-input w-full text-sm py-3" required dir="ltr">
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">استان</label>
                                            <select name="state_id" class="neu-input w-full text-sm py-3" id="newAddrState" onchange="loadCities(this.value, 'newAddrCity')" required>
                                                <option value="">انتخاب استان</option>
                                                @foreach($states as $state)
                                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">شهر</label>
                                            <select name="city_id" class="neu-input w-full text-sm py-3" id="newAddrCity" required>
                                                <option value="">انتخاب شهر</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">آدرس کامل</label>
                                        <textarea name="address" class="neu-input w-full text-sm py-3" rows="2" required></textarea>
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">کدپستی</label>
                                        <input type="text" name="postal_code" class="neu-input w-full text-sm py-3" required dir="ltr">
                                    </div>
                                    <div class="flex gap-2 pt-1">
                                        <button type="submit"
                                                class="neu-btn px-6 py-2.5 text-xs font-bold rounded-full transition-all duration-200 hover:scale-[1.03]"
                                                style="background: var(--accent); color: var(--accent-contrast);">
                                            ذخیره آدرس
                                        </button>
                                        <button type="button"
                                                onclick="document.getElementById('addAddressPanel').classList.add('hidden')"
                                                class="neu-btn px-6 py-2.5 text-xs font-bold rounded-full">
                                            انصراف
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- روش‌های ارسال --}}
                        <div class="neu-card p-6 hidden" id="shippingMethodsCard">
                            <h2 class="font-black text-base flex items-center gap-2.5 mb-5" style="color: var(--text-primary);">
                                <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25m-2.25 0h-2.25m0 0v-.958c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.958m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </div>
                                روش ارسال
                            </h2>
                            <div id="step2-free-ship-banner" class="hidden" style="display: none !important;"></div>
                            <div id="shippingMethodsList"></div>
                        </div>
                    </div>

                    {{-- خلاصه سفارش مرحله ۲ --}}
                    <div class="lg:sticky lg:top-24">
                        <div class="neu-card p-6">
                            <h3 class="font-black text-base mb-5" style="color: var(--text-primary);">خلاصه سفارش</h3>
                            <div class="space-y-3 mb-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: var(--text-secondary);">{{ __('messages.subtotal') }}</span>
                                    <span id="step2-subtotal" class="mono font-bold" dir="ltr">{{ number_format($cart->subtotal()) }}</span>
                                </div>
                                <div id="step2-coupon-row" class="flex items-center justify-between text-sm {{ session('coupon') ? '' : 'hidden' }}">
                                    <span style="color: var(--success);">تخفیف ({{ session('coupon.code') ?? '' }})</span>
                                    <span id="step2-coupon-amount" class="mono font-bold" style="color: var(--success);" dir="ltr">-{{ number_format($cart->discount()) }}</span>
                                </div>
                                @if(($totalSavings ?? 0) > 0)
                                    <div id="step2-savings-row" class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-1.5" style="color: var(--success);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            {{ __('messages.your_savings_from_purchase') }}
                                        </span>
                                        <span class="mono font-bold" style="color: var(--success);" dir="ltr">{{ number_format($totalSavings) }} {{ __('messages.toman') }}</span>
                                    </div>
                                @endif
                                <div id="step2-shipping-row" class="flex items-center justify-between text-sm hidden">
                                    <span style="color: var(--text-secondary);">هزینه ارسال</span>
                                    <span id="step2-shipping-price" class="mono font-bold" dir="ltr">۰</span>
                                </div>
                                <div id="step2-free-ship-info" class="hidden rounded-xl p-3 text-xs font-bold" style="display: none !important;"></div>
                            </div>
                            <div class="border-t pt-4 flex items-center justify-between mb-6"
                                 style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                                <span class="font-bold text-base">{{ __('messages.total') }}</span>
                                <span id="step2-total" class="mono font-black text-xl" style="color: var(--accent);" dir="ltr">{{ number_format($cart->total()) }}</span>
                            </div>
                            <div class="flex gap-2.5">
                                <button type="button" onclick="goToStep(1)"
                                        class="neu-btn px-4 py-3 text-xs font-bold rounded-full flex-1 text-center transition-all duration-200">
                                    بازگشت
                                </button>
                                <button type="button" onclick="goToStep(3)" id="step2-next-btn"
                                        class="neu-btn neu-btn-primary px-4 py-3 text-xs font-bold rounded-full flex-1 text-center transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                                        disabled>
                                    ادامه
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endif
            @if(!($isGuest ?? false))
            {{-- ═══════════════════ مرحله ۳: کد تخفیف ═══════════════════ --}}
            <div id="checkout-step-3" class="checkout-step" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2">
                        <div class="neu-card p-6">
                            <h2 class="font-black text-base flex items-center gap-2.5 mb-5" style="color: var(--text-primary);">
                                <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h.008v.008H6V6Z"/></svg>
                                </div>
                                {{ __('messages.coupon_code') }}
                            </h2>
                            <p class="text-sm mb-5" style="color: var(--text-secondary);">اگر کد تخفیف دارید، در کادر زیر وارد کنید و دکمه اعمال را بزنید.</p>
                            <div class="neu-flat-sm p-5 rounded-2xl">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex-1">
                                        <input type="text" id="discountCodeInput" class="neu-input w-full text-sm py-3.5 ps-10"
                                               placeholder="کد تخفیف خود را وارد کنید" autocomplete="off">
                                        <svg class="absolute start-3 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/></svg>
                                    </div>
                                    <button type="button" onclick="applyDiscountCode()"
                                            class="neu-btn px-6 py-3.5 text-sm font-bold rounded-full shrink-0 transition-all duration-200 hover:scale-[1.03] active:scale-[0.98]"
                                            style="background: var(--accent); color: var(--accent-contrast);">
                                        {{ __('messages.apply') }}
                                    </button>
                                </div>
                                <div id="discountMessage" class="text-sm mt-3 font-bold" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    {{-- خلاصه مرحله ۳ --}}
                    <div class="lg:sticky lg:top-24">
                        <div class="neu-card p-6">
                            <h3 class="font-black text-base mb-5" style="color: var(--text-primary);">خلاصه سفارش</h3>
                            <div class="space-y-3 mb-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: var(--text-secondary);">{{ __('messages.subtotal') }}</span>
                                    <span id="step3-subtotal" class="mono font-bold" dir="ltr">{{ number_format($cart->subtotal()) }}</span>
                                </div>
                                <div id="step3-coupon-row" class="flex items-center justify-between text-sm {{ session('coupon') ? '' : 'hidden' }}">
                                    <span style="color: var(--success);">{{ __('messages.discount') }}</span>
                                    <span id="step3-coupon-amount" class="mono font-bold" style="color: var(--success);" dir="ltr">-{{ number_format($cart->discount()) }}</span>
                                </div>
                                @if(($totalSavings ?? 0) > 0)
                                    <div id="step3-savings-row" class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-1.5" style="color: var(--success);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            {{ __('messages.your_savings_from_purchase') }}
                                        </span>
                                        <span class="mono font-bold" style="color: var(--success);" dir="ltr">{{ number_format($totalSavings) }} {{ __('messages.toman') }}</span>
                                    </div>
                                @endif
                                <div id="step3-shipping-row" class="flex items-center justify-between text-sm hidden">
                                    <span style="color: var(--text-secondary);">هزینه ارسال</span>
                                    <span id="step3-shipping-price" class="mono font-bold" dir="ltr">۰</span>
                                </div>
                                <div id="step3-free-ship-info" class="rounded-xl p-3 text-xs font-bold" style="display: none;"></div>
                            </div>
                            <div class="border-t pt-4 flex items-center justify-between mb-6"
                                 style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                                <span class="font-bold text-base">{{ __('messages.total') }}</span>
                                <span id="step3-total" class="mono font-black text-xl" style="color: var(--accent);" dir="ltr">{{ number_format($cart->total()) }}</span>
                            </div>
                            <div class="flex gap-2.5">
                                <button type="button" onclick="goToStep(2)"
                                        class="neu-btn px-4 py-3 text-xs font-bold rounded-full flex-1 text-center transition-all duration-200">
                                    بازگشت
                                </button>
                                <button type="button" onclick="goToStep(4)"
                                        class="neu-btn neu-btn-primary px-4 py-3 text-xs font-bold rounded-full flex-1 text-center transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                                    ادامه و پرداخت
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endif
            @if(!($isGuest ?? false))
            {{-- ═══════════════════ مرحله ۴: پرداخت و ثبت نهایی ═══════════════════ --}}
            <div id="checkout-step-4" class="checkout-step" style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2 space-y-4">
                        {{-- درگاه پرداخت --}}
                        <div class="neu-card p-6">
                            <h2 class="font-black text-base flex items-center gap-2.5 mb-5" style="color: var(--text-primary);">
                                <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                </div>
                                {{ __('messages.payment_method') }}
                            </h2>
                            <form action="{{ localizedRoute('checkout.create') }}" method="POST" id="paymentForm" enctype="multipart/form-data">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @forelse($banks as $bank)
                                        <label class="checkout-bank-option neu-flat-sm p-4 rounded-2xl flex items-center gap-3 cursor-pointer transition-all duration-300"
                                               data-bank-id="{{ $bank->id }}"
                                               data-bank-type="{{ $bank->bank_type }}"
                                               data-card-number="{{ $bank->card_number ?? '' }}"
                                               data-card-holder="{{ $bank->card_holder_name ?? '' }}"
                                               data-card-desc="{{ $bank->card_description ?? '' }}"
                                               onclick="selectBank({{ $bank->id }})">
                                            <div class="bank-radio w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all duration-300"
                                                 style="border-color: color-mix(in srgb, var(--text-primary) 25%, transparent);">
                                                <div class="bank-radio-inner w-2.5 h-2.5 rounded-full transition-all duration-300 scale-0"
                                                     style="background: var(--accent);"></div>
                                            </div>
                                            @if($bank->icon)
                                                <img src="{{ $bank->item_image }}" alt="{{ $bank->title }}" class="w-10 h-7 object-contain shrink-0">
                                            @endif
                                            <span class="text-sm font-bold" style="color: var(--text-primary);">{{ $bank->title }}</span>
                                            <input type="radio" name="bank_id" value="{{ $bank->id }}" class="hidden">
                                        </label>
                                    @empty
                                        <div class="text-center py-6 sm:col-span-2">
                                            <div class="neu-icon-circle w-12 h-12 mx-auto grid place-items-center mb-3" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">
                                                <svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                            </div>
                                            <p class="text-sm" style="color: var(--text-secondary);">هیچ درگاه پرداختی فعال نیست.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="mt-5">
                                    <label class="text-[11px] font-bold mb-1.5 block uppercase tracking-wide" style="color: var(--text-secondary);">توضیحات سفارش (اختیاری)</label>
                                    <textarea name="user_description" class="neu-input w-full text-sm py-3" rows="2"
                                              placeholder="مثلاً: ساعت ارسال، توضیحات خاص و ..."></textarea>
                                </div>
                                <div class="mt-6 p-5 text-center rounded-2xl" style="background: color-mix(in srgb, var(--accent) 5%, transparent); border: 1px solid color-mix(in srgb, var(--accent) 12%, transparent);">
                                    <p class="text-xs font-bold mb-2" style="color: var(--text-secondary);">مبلغ قابل پرداخت</p>
                                    <p class="mono font-black text-2xl mb-4" style="color: var(--accent);" dir="ltr" id="step4-total-inline">{{ number_format($cart->total()) }}</p>
                                    <button type="submit" id="payBtn"
                                            class="neu-btn neu-btn-primary w-full py-4 text-sm font-black rounded-full text-center block transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                                            disabled>
                                        <span class="flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                            {{ __('messages.place_order') }}
                                        </span>
                                    </button>
                                </div>

                                {{-- نمایش اطلاعات فیش --}}
                                <div id="receiptInfoBadge" class="hidden mt-4 p-3 rounded-2xl flex items-center gap-3" style="background: color-mix(in srgb, var(--accent) 5%, transparent); border: 1px solid color-mix(in srgb, var(--accent) 15%, transparent);">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 12%, transparent);">
                                        <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold" style="color: var(--accent);">اطلاعات واریزی ثبت شد ✓</p>
                                        <p id="receiptInfoTracking" class="text-[10px] mt-0.5 truncate" style="color: var(--text-secondary);"></p>
                                        <p id="receiptInfoCard" class="text-[10px] mt-0.5 truncate" style="color: var(--text-secondary);"></p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('receiptInfoBadge').classList.add('hidden'); clearReceiptInfo();" class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-colors" style="background: color-mix(in srgb, #ef4444 10%, transparent);">
                                        <svg class="w-3.5 h-3.5" style="color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                {{-- hidden inputs for receipt info inside form --}}
                                <input type="hidden" name="receipt_tracking_code" id="receiptTrackingCodeForm" value="">
                                <input type="hidden" name="receipt_payer_card" id="receiptPayerCardForm" value="">

                            </form>
                        </div>

                        {{-- خلاصه اقلام --}}
                        <div class="neu-card p-6">
                            <h2 class="font-black text-base flex items-center gap-2.5 mb-5" style="color: var(--text-primary);">
                                <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                </div>
                                اقلام سفارش
                            </h2>
                            <div class="space-y-0">
                                @foreach($items as $item)
                                    <div class="flex items-center gap-4 py-3.5 @if(!$loop->last) border-b @endif"
                                         style="border-color: color-mix(in srgb, var(--text-primary) 6%, transparent);">
                                        <img src="{{ $item['image'] ?? asset('assets/admin/images/image-not-found.png') }}" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0 neu-flat-sm"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/admin/images/image-not-found.png') }}'">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold truncate" style="color: var(--text-primary);">{{ $item['name'] }}</p>
                                            @if(!empty($item['variant_label']))
                                                <p class="text-[11px] mt-0.5" style="color: var(--text-secondary);">{{ $item['variant_label'] }}</p>
                                            @endif
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if(!empty($item['original_price']) && $item['original_price'] > $item['price'])
                                                    <span class="mono text-[10px] line-through" style="color: var(--text-secondary); opacity:.6;" dir="ltr">{{ number_format($item['original_price']) }}</span>
                                                @endif
                                                <span class="text-[11px]" style="color: var(--text-secondary);">{{ $item['qty'] }} × {{ number_format($item['price']) }} {{ __('messages.toman') }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm mono font-black shrink-0" style="color: var(--accent);" dir="ltr">{{ number_format($item['price'] * $item['qty']) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- اطلاعات آدرس و ارسال --}}
                        <div class="neu-card p-6">
                            <h2 class="font-black text-base flex items-center gap-2.5 mb-5" style="color: var(--text-primary);">
                                <div class="neu-icon-circle w-9 h-9 grid place-items-center shrink-0">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                </div>
                                اطلاعات ارسال
                            </h2>
                            <div class="space-y-4">
                                {{-- آدرس --}}
                                @if($selectedAddress)
                                    <div class="neu-flat-sm p-4 rounded-2xl">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                            <span class="text-xs font-bold" style="color: var(--text-secondary);">آدرس تحویل</span>
                                        </div>
                                        <p class="text-sm font-bold mb-1" style="color: var(--text-primary);">{{ $selectedAddress->receiptor_full_name }}</p>
                                        <p class="text-xs leading-relaxed mb-2" style="color: var(--text-secondary);">{{ $selectedAddress->address }}</p>
                                        <div class="flex items-center gap-3 text-[11px] flex-wrap" style="color: var(--text-secondary);">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                                {{ $selectedAddress->state?->name }} - {{ $selectedAddress->city?->name }}
                                            </span>
                                            <span aria-hidden="true" style="opacity:.3;">•</span>
                                            <span dir="ltr" class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                                {{ $selectedAddress->receiptor_mobile }}
                                            </span>
                                            @if($selectedAddress->postal_code)
                                                <span aria-hidden="true" style="opacity:.3;">•</span>
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                                    {{ $selectedAddress->postal_code }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                {{-- روش ارسال --}}
                                @if($selectedShipping)
                                    <div class="neu-flat-sm p-4 rounded-2xl">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25m-2.25 0h-2.25m0 0v-.958c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.958m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            <span class="text-xs font-bold" style="color: var(--text-secondary);">روش ارسال</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $selectedShipping->title }}</p>
                                            <span class="text-sm mono font-bold" style="color: var(--accent);" dir="ltr">
                                                @if($shippingPrice > 0)
                                                    {{ number_format($shippingPrice) }} {{ __('messages.toman') }}
                                                @else
                                                    {{ __('messages.trust_free_shipping') }}
                                                @endif
                                            </span>
                                        </div>
                                        @if(!empty($selectedShipping->show_estimated_days) && ($selectedShipping->estimated_days_min || $selectedShipping->estimated_days_max))
                                            <div class="flex items-center gap-1.5 mt-2 text-[11px]" style="color: var(--text-secondary);">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                <span>زمان تخمینی تحویل: <strong style="color: var(--accent);">{{ $selectedShipping->estimated_days_text }}</strong></span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- خلاصه پرداخت مرحله ۴ --}}
                    <div class="lg:sticky lg:top-24">
                        <div class="neu-card p-6">
                            <h3 class="font-black text-base mb-5" style="color: var(--text-primary);">خلاصه پرداخت</h3>
                            <div class="space-y-3 mb-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: var(--text-secondary);">{{ __('messages.subtotal') }}</span>
                                    <span id="step4-subtotal" class="mono font-bold" dir="ltr">{{ number_format($cart->subtotal()) }}</span>
                                </div>
                                <div id="step4-coupon-row" class="flex items-center justify-between text-sm {{ session('coupon') ? '' : 'hidden' }}">
                                    <span style="color: var(--success);">{{ __('messages.discount') }}</span>
                                    <span id="step4-coupon-amount" class="mono font-bold" style="color: var(--success);" dir="ltr">-{{ number_format($cart->discount()) }}</span>
                                </div>
                                @if(($totalSavings ?? 0) > 0)
                                    <div id="step4-savings-row" class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-1.5" style="color: var(--success);">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            {{ __('messages.your_savings_from_purchase') }}
                                        </span>
                                        <span class="mono font-bold" style="color: var(--success);" dir="ltr">{{ number_format($totalSavings) }} {{ __('messages.toman') }}</span>
                                    </div>
                                @endif
                                <div id="step4-shipping-row" class="flex items-center justify-between text-sm hidden">
                                    <span style="color: var(--text-secondary);">هزینه ارسال</span>
                                    <span id="step4-shipping-price" class="mono font-bold" dir="ltr">۰</span>
                                </div>
                                <div id="step4-free-ship-info" class="rounded-xl p-3 text-xs font-bold" style="display: none;"></div>
                            </div>
                            <div class="border-t pt-4 flex items-center justify-between mb-6"
                                 style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                                <span class="font-bold text-base">مبلغ قابل پرداخت</span>
                                <span id="step4-total" class="mono font-black text-xl" style="color: var(--accent);" dir="ltr">{{ number_format($cart->total()) }}</span>
                            </div>
                            <button type="button" onclick="goToStep(3)"
                                    class="neu-btn px-4 py-3 text-xs font-bold rounded-full w-full text-center transition-all duration-200">
                                <span class="flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    بازگشت به کد تخفیف
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif {{-- isGuest step4 --}}
        @endif
    </div>

    {{-- ═══════════════════ مودال کارت به کارت (خارج از reveal-on-scroll) ═══════════════════ --}}
    <div id="cardToCardModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" onclick="closeCardToCardModal()"></div>
        <div class="relative z-10 w-full max-w-md">
            <div class="rounded-3xl shadow-2xl overflow-hidden" style="background: var(--bg-card, #fff);">
                <div class="relative px-6 pt-6 pb-4 text-center" style="background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 10%, transparent), color-mix(in srgb, var(--accent) 5%, transparent));">
                    <button onclick="closeCardToCardModal()" class="absolute top-4 left-4 w-8 h-8 rounded-full flex items-center justify-center transition-colors" style="background: color-mix(in srgb, var(--text-primary) 5%, transparent);">
                        <svg class="w-4 h-4" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-3" style="background: color-mix(in srgb, var(--accent) 15%, transparent);">
                        <svg class="w-7 h-7" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    </div>
                    <h3 class="text-lg font-black" style="color: var(--text-primary);">اطلاعات کارت به کارت</h3>
                    <p class="text-xs mt-1" style="color: var(--text-secondary);">لطفاً اطلاعات زیر را بررسی کرده و فیش واریزی را آپلود کنید</p>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="p-4 rounded-2xl" style="background: color-mix(in srgb, var(--accent) 5%, transparent); border: 1px solid color-mix(in srgb, var(--accent) 10%, transparent);">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold" style="color: var(--text-secondary);">شماره کارت مقصد</span>
                            <button type="button" onclick="copyCardNumber()" id="copyCardBtn" class="text-xs px-3 py-1.5 rounded-xl font-bold transition-all" style="background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent);">کپی شماره کارت</button>
                        </div>
                        <p id="modalCardNumber" class="mono text-xl font-black tracking-wider text-center py-1" style="color: var(--accent);" dir="ltr"></p>
                    </div>
                    <div id="modalHolderRow" class="flex items-center justify-between px-1" style="display:none;">
                        <span class="text-xs" style="color: var(--text-secondary);">صاحب کارت</span>
                        <span id="modalCardHolder" class="text-sm font-bold" style="color: var(--text-primary);"></span>
                    </div>
                    <div id="modalDescRow" class="px-1" style="display:none;">
                        <p id="modalCardDesc" class="text-xs leading-relaxed" style="color: var(--text-secondary);"></p>
                    </div>
                    <div>
                        <label class="text-xs font-bold mb-2 block" style="color: var(--text-secondary);">کد پیگیری واریزی <span class="text-rose-500">*</span></label>
                        <input type="text" id="receiptTrackingCode" inputmode="numeric" pattern="\d{6,16}" maxlength="16" placeholder="مثلاً: ۱۲۳۴۵۶۷۸۹۰" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               class="w-full px-4 py-3 rounded-xl text-sm font-bold transition-all outline-none"
                               style="background: color-mix(in srgb, var(--text-primary) 4%, transparent); color: var(--text-primary); border: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent);"
                               dir="ltr">
                    </div>
                    <div>
                        <label class="text-xs font-bold mb-2 block" style="color: var(--text-secondary);">شماره کارت واریزکننده <span class="text-[10px] font-normal">(اختیاری)</span></label>
                        <input type="text" id="receiptPayerCard" inputmode="numeric" pattern="(\d{4}-?){3}\d{4}" maxlength="16" placeholder="مثلاً: 6037xxxxxxxxxxxx" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               class="w-full px-4 py-3 rounded-xl text-sm font-bold transition-all outline-none"
                               style="background: color-mix(in srgb, var(--text-primary) 4%, transparent); color: var(--text-primary); border: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent);"
                               dir="ltr">
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <button type="button" onclick="confirmCardToCard()" id="confirmCardToCardBtn" class="w-full py-3.5 rounded-2xl text-sm font-black text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]" style="background: var(--accent);">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            تایید و ادامه
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ استایل‌ها ═══════════════════ --}}
    <style>
        /* ─── نشانگر مراحل ─── */
        .step-dot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 2;
        }
        .step-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            position: relative;
            background: color-mix(in srgb, var(--text-primary) 8%, transparent);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .step-num {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-secondary);
            transition: all 0.3s;
        }
        .step-check {
            width: 20px;
            height: 20px;
            position: absolute;
            color: #fff;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .step-dot.active .step-circle {
            background: var(--accent);
            box-shadow: 0 4px 20px color-mix(in srgb, var(--accent) 45%, transparent);
            transform: scale(1.08);
        }
        .step-dot.active .step-num { color: var(--accent-contrast); }
        .step-dot.done .step-circle {
            background: var(--success);
            box-shadow: 0 4px 16px color-mix(in srgb, var(--success) 35%, transparent);
        }
        .step-dot.done .step-num { opacity: 0; transform: scale(0); }
        .step-dot.done .step-check { opacity: 1; transform: scale(1); }

        .step-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            white-space: nowrap;
            transition: color 0.3s;
        }
        .step-dot.active .step-label { color: var(--accent); font-weight: 800; }
        .step-dot.done .step-label { color: var(--success); }

        .step-line {
            flex: 1;
            height: 3px;
            background: color-mix(in srgb, var(--text-primary) 8%, transparent);
            margin: 0 -2px;
            margin-bottom: 24px;
            border-radius: 3px;
            transition: background 0.5s ease;
            min-width: 30px;
            max-width: 100px;
            overflow: hidden;
        }
        .step-line::after {
            content: '';
            display: block;
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.5s ease, background 0.5s ease;
        }
        .step-line.active::after {
            width: 100%;
            background: var(--accent);
        }
        .step-line.done::after {
            width: 100%;
            background: var(--success);
        }

        /* ─── کارت آدرس ─── */
        .address-card {
            border: 2px solid transparent;
        }
        .address-card:hover {
            border-color: color-mix(in srgb, var(--accent) 25%, transparent);
            transform: translateY(-1px);
        }
        .address-card.selected {
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 4%, transparent);
            box-shadow: 0 4px 16px color-mix(in srgb, var(--accent) 12%, transparent);
        }
        .address-card.selected .address-radio {
            border-color: var(--accent) !important;
        }
        .address-card.selected .address-radio-inner {
            opacity: 1 !important;
            transform: scale(1) !important;
        }

        /* ─── کارت روش ارسال ─── */
        .shipping-option {
            border: 2px solid transparent;
        }
        .shipping-option:hover {
            border-color: color-mix(in srgb, var(--accent) 25%, transparent);
            transform: translateY(-1px);
        }
        .shipping-option.selected {
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 4%, transparent);
            box-shadow: 0 4px 16px color-mix(in srgb, var(--accent) 12%, transparent);
        }
        .shipping-option.selected .shipping-radio {
            border-color: var(--accent) !important;
        }
        .shipping-option.selected .shipping-radio-inner {
            opacity: 1 !important;
            transform: scale(1) !important;
        }

        /* ─── کارت بانک ─── */
        .checkout-bank-option {
            border: 2px solid transparent;
        }
        .checkout-bank-option:hover {
            border-color: color-mix(in srgb, var(--accent) 25%, transparent);
            transform: translateY(-1px);
        }
        .checkout-bank-option.selected {
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 4%, transparent);
            box-shadow: 0 4px 16px color-mix(in srgb, var(--accent) 12%, transparent);
        }
        .checkout-bank-option.selected .bank-radio {
            border-color: var(--accent) !important;
        }
        .checkout-bank-option.selected .bank-radio-inner {
            opacity: 1 !important;
            transform: scale(1) !important;
        }

        /* ─── انیمیشن ورود مراحل ─── */
        .checkout-step {
            animation: stepFadeIn 0.4s ease-out;
        }
        @keyframes stepFadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }
        /* When modal is open: blur everything inside .reveal-on-scroll */
        .page-blur-active .reveal-on-scroll {
            filter: blur(8px) !important;
            pointer-events: none !important;
            user-select: none !important;
            transition: filter 0.3s ease;
        }

        /* ─── ریسپانسیو ─── */
        @media (max-width: 768px) {
            .step-circle { width: 38px; height: 38px; }
            .step-num { font-size: 13px; }
            .step-check { width: 17px; height: 17px; }
            .step-label { font-size: 10px; }
            .step-line { min-width: 20px; margin-bottom: 20px; }
        }
    </style>
@endsection

@push('scripts')
    @include('site.cart.ajax-cart')
    <script>
    (function() {
        // ========== مدیریت مراحل ==========
        var currentStep = {{ $currentStep ?? session('step') ?? 1 }};
        var selectedAddressId = null;
        var selectedShippingMethodId = null;
        var selectedBankId = null;
        var selectedShippingFreightBalance = 0;
        var shippingPrice = 0;
        var selectedPriceCeiling = 0;
        var selectedOriginalPrice = 0;
        var subtotal = {{ $cart->subtotal() }};
        var discount = {{ $cart->discount() }};
        var totalWithoutShipping = {{ $cart->total() }};

        var urls = {
            cities: '{{ localizedRoute("checkout.cities") }}',
            shipments: '{{ localizedRoute("checkout.shipments") }}',
            setShipment: '{{ localizedRoute("checkout.set-shipment") }}',
            addressPrice: '{{ localizedRoute("checkout.address-price") }}',
            applyDiscount: '{{ localizedRoute("checkout.apply-discount") }}',
            removeDiscount: '{{ localizedRoute("checkout.remove-discount") }}',
            orderPrice: '{{ localizedRoute("checkout.order-price") }}',
            validateStock: '{{ localizedRoute("checkout.validate-stock") }}',
        };

        var csrfToken = function() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

        function formatNum(n) {
            return new Intl.NumberFormat(document.documentElement.lang === 'fa' || document.documentElement.lang === 'ar' ? 'fa-IR' : 'en-US').format(n);
        }

        function freeShipProgressBar(orderTotal, priceCeiling) {
            var pct = Math.min(100, Math.round((orderTotal / priceCeiling) * 100));
            return '<div style="margin-top:10px;">' +
                '<div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:10px;font-weight:700;">' +
                    '<span style="color:var(--text-secondary);">' + pct + '%</span>' +
                    '<span style="color:var(--text-secondary);">' + formatNum(priceCeiling) + ' {{ __("messages.toman") }}</span>' +
                '</div>' +
                '<div style="width:100%;height:8px;border-radius:99px;background:color-mix(in srgb, var(--text-primary) 8%, transparent);overflow:hidden;">' +
                    '<div class="free-ship-bar-fill" style="height:100%;width:0%;border-radius:99px;background:linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--success) 80%, var(--accent)));transition:width 0.8s cubic-bezier(0.34,1.56,0.64,1);" data-target-width="' + pct + '%"></div>' +
                '</div>' +
            '</div>';
        }

        function animateProgressBars() {
            requestAnimationFrame(function() {
                document.querySelectorAll('.free-ship-bar-fill').forEach(function(bar) {
                    bar.style.width = bar.getAttribute('data-target-width');
                });
            });
        }

        window.goToStep = function(step) {
            currentStep = step;
            document.querySelectorAll('.checkout-step').forEach(function(el) { el.style.display = 'none'; });
            var target = document.getElementById('checkout-step-' + step);
            target.style.display = '';
            // ری‌تریگ انیمیشن
            target.style.animation = 'none';
            target.offsetHeight; // reflow
            target.style.animation = '';

            // به‌روزرسانی نشانگر
            document.querySelectorAll('.step-dot').forEach(function(dot) {
                var s = parseInt(dot.getAttribute('data-step'));
                dot.classList.remove('active', 'done');
                if (s === step) dot.classList.add('active');
                else if (s < step) dot.classList.add('done');
            });
            document.getElementById('line-1-2').className = 'step-line' + (step > 1 ? ' done' : step === 2 ? ' active' : '');
            document.getElementById('line-2-3').className = 'step-line' + (step > 2 ? ' done' : step === 3 ? ' active' : '');
            document.getElementById('line-3-4').className = 'step-line' + (step > 3 ? ' done' : step === 4 ? ' active' : '');

            // ذخیره مرحله در URL (بدون رفرش صفحه)
            var url = new URL(window.location.href);
            url.searchParams.set('step', step);
            history.replaceState(null, '', url.toString());

            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ========== انتخاب آدرس ==========
        window.selectAddress = function(addressId) {
            selectedAddressId = addressId;
            selectedShippingMethodId = null;
            shippingPrice = 0;

            // مخفی کردن بنر و اطلاعات سقف رایگان (تا روش ارسال انتخاب نشده نمایش داده نشه)
            var b = document.getElementById('step2-free-ship-banner');
            if (b) b.style.display = 'none';
            var s = document.getElementById('step2-free-ship-info');
            if (s) s.style.display = 'none';

            document.querySelectorAll('.address-card').forEach(function(c) { c.classList.remove('selected'); });
            var card = document.querySelector('[data-address-id="' + addressId + '"]');
            if (card) card.classList.add('selected');

            fetch(urls.shipments, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ address_id: addressId })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';
                if (data.shipping_methods && data.shipping_methods.length > 0) {
                    data.shipping_methods.forEach(function(method) {
                        var priceText = method.price === 'dynamic' ? 'قیمت لحظه‌ای' : (method.price > 0 ? formatNum(method.price) + ' {{ __("messages.toman") }}' : (method.freight_balance == 1 ? '{{ __("messages.trust_cod_shipping") }}' : '{{ __("messages.trust_free_shipping") }}'));
                        var priceVal = method.price === 'dynamic' ? 0 : parseInt(method.price);
                        var priceCeiling = parseInt(method.price_ceiling) || 0;
                        var originalPrice = parseInt(method.original_price) || 0;
                        var icon = method.type === 'with_weight'
                            ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>'
                            : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25m-2.25 0h-2.25m0 0v-.958c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.958m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';
                        // نمایش برچسب سقف رایگان در کنار روش ارسال
                        var freeShipBadge = '';
                        var freeShipAboveTpl = '{{ __("messages.free_shipping_above", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                        if (priceCeiling > 0 && method.freight_balance != 1) {
                            freeShipBadge = '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full mt-1 inline-block" style="background: color-mix(in srgb, var(--success) 12%, transparent); color: var(--success);">' +
                                freeShipAboveTpl.replace('__AMOUNT__', formatNum(priceCeiling)) + '</span>';
                        }

                        html += '<div class="shipping-option neu-flat-sm p-4 rounded-2xl cursor-pointer transition-all duration-300 mb-2.5" ' +
                            'data-method-id="' + method.id + '" data-price="' + priceVal + '" ' +
                            'data-price-ceiling="' + priceCeiling + '" data-original-price="' + originalPrice + '" ' +
                            'data-freight-balance="' + (method.freight_balance || 0) + '" ' +
                            'onclick="selectShipping(' + method.id + ', ' + priceVal + ', ' + (method.freight_balance || 0) + ', ' + priceCeiling + ', ' + originalPrice + ')">' +
                            '<div class="flex items-center justify-between">' +
                            '<div class="flex items-center gap-3">' +
                            '<div class="shipping-radio w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all duration-300" ' +
                            'style="border-color: color-mix(in srgb, var(--text-primary) 25%, transparent);">' +
                            '<div class="shipping-radio-inner w-2.5 h-2.5 rounded-full transition-all duration-300 scale-0" ' +
                            'style="background: var(--accent);"></div></div>' +
                            '<div class="flex items-center gap-2">' + icon +
                            '<div><p class="text-sm font-bold" style="color: var(--text-primary);">' + method.title + '</p>' +
                            '<p class="text-[11px]" style="color: var(--text-secondary);">' +
                            (method.type === 'with_weight' ? 'بر اساس وزن' : method.type === 'fixed_price' ? 'قیمت ثابت' : '') +
                            (method.show_estimated_days && method.estimated_days_min ? ' · ' + method.estimated_days_min + ' تا ' + method.estimated_days_max + ' روز کاری' : '') +
                            '</p>' + freeShipBadge + '</div>' +
                            '</div>' +
                            '</div>' +
                            '<span class="text-sm mono font-bold" style="color: var(--accent);" dir="ltr">' + priceText + '</span>' +
                            '</div></div>';
                    });
                } else {
                    html = '<div class="text-center py-8">' +
                        '<div class="neu-icon-circle w-12 h-12 mx-auto grid place-items-center mb-3" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">' +
                        '<svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>' +
                        '</div><p class="text-sm" style="color: var(--text-secondary);">روش ارسالی یافت نشد.</p></div>';
                }
                document.getElementById('shippingMethodsList').innerHTML = html;
                document.getElementById('shippingMethodsCard').classList.remove('hidden');
                // ری‌تریگ انیمیشن
                var shipCard = document.getElementById('shippingMethodsCard');
                shipCard.style.animation = 'none';
                shipCard.offsetHeight;
                shipCard.style.animation = '';
            });
        };

        // ========== انتخاب روش ارسال ==========
        window.selectShipping = function(methodId, price, freightBalance, priceCeiling, originalPrice) {
            selectedShippingMethodId = methodId;
            selectedShippingFreightBalance = freightBalance || 0;
            shippingPrice = parseInt(price) || 0;
            selectedPriceCeiling = parseInt(priceCeiling) || 0;
            selectedOriginalPrice = parseInt(originalPrice) || 0;
            var pc = parseInt(priceCeiling) || 0;
            var op = parseInt(originalPrice) || 0;

            document.querySelectorAll('.shipping-option').forEach(function(c) { c.classList.remove('selected'); });
            var card = document.querySelector('[data-method-id="' + methodId + '"]');
            if (card) card.classList.add('selected');

            fetch(urls.setShipment, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ shipping_method_id: methodId, shipping_price: price })
            });

            // نمایش پیام سقف رایگان + به‌روزرسانی باکس خلاصه
            var banner = document.getElementById('step2-free-ship-banner');
            var sidebarInfo = document.getElementById('step2-free-ship-info');
            var orderTotal = totalWithoutShipping;

            if (pc > 0 && freightBalance != 1) {
                if (orderTotal >= pc) {
                    // ارسال رایگان شده — سود کردیم
                    var savedTpl = '{{ __("messages.free_shipping_saved", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                    var savedAmount = '<span class="mono" dir="ltr">' + formatNum(op) + '</span>';

                    if (banner) {
                        banner.className = 'flex items-center gap-2.5 p-3 rounded-xl text-xs font-bold mb-2.5';
                        banner.style.background = 'color-mix(in srgb, var(--success) 10%, transparent)';
                        banner.style.color = 'var(--success)';
                        banner.innerHTML = '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                            '<div>' + savedTpl.replace('__AMOUNT__', savedAmount) + '</div>';
                        banner.style.display = 'flex';
                    }
                    // باکس خلاصه سفارش
                    var savedSidebarTpl = '{{ __("messages.free_shipping_sidebar_saved", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                    if (sidebarInfo) {
                        sidebarInfo.className = 'rounded-xl p-3 text-xs font-bold';
                        sidebarInfo.style.background = 'color-mix(in srgb, var(--success) 10%, transparent)';
                        sidebarInfo.style.color = 'var(--success)';
                        sidebarInfo.innerHTML = savedSidebarTpl.replace('__AMOUNT__', '<span class="mono" dir="ltr">' + formatNum(op) + '</span>');
                        sidebarInfo.style.display = 'flex';
                        animateProgressBars();
                    }
                } else {
                    // هنوز به سقف نرسیده
                    var remaining = pc - orderTotal;
                    var remainingTpl = '{{ __("messages.free_shipping_remaining", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                    var remainingAmount = '<span class="mono" dir="ltr">' + formatNum(remaining) + '</span>';

                    if (banner) {
                        banner.className = 'flex items-center gap-2.5 p-3 rounded-xl text-xs font-bold mb-2.5';
                        banner.style.background = 'color-mix(in srgb, var(--accent) 8%, transparent)';
                        banner.style.color = 'var(--accent)';
                        banner.innerHTML = '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                            '<div>' + remainingTpl.replace('__AMOUNT__', remainingAmount) + freeShipProgressBar(orderTotal, pc) + '</div>';
                        banner.style.display = 'flex';
                        animateProgressBars();
         
                    }
                    // باکس خلاصه سفارش
                    var remainingSidebarTpl = '{{ __("messages.free_shipping_sidebar_remaining", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                    if (sidebarInfo) {
                        sidebarInfo.className = 'rounded-xl p-3 text-xs font-bold';
                        sidebarInfo.style.background = 'color-mix(in srgb, var(--accent) 8%, transparent)';
                        sidebarInfo.style.color = 'var(--accent)';
                        sidebarInfo.innerHTML = remainingSidebarTpl.replace('__AMOUNT__', '<span class="mono" dir="ltr">' + formatNum(remaining) + '</span>') + freeShipProgressBar(orderTotal, pc);
                        sidebarInfo.style.display = 'flex';
                    }
                }
            } else {
                if (banner) banner.style.display = 'none';
                if (sidebarInfo) sidebarInfo.style.display = 'none';
            }

            updateStep2Price();
        };

        function updateStep2Price() {
            var total = subtotal + shippingPrice - discount;
            var shippingRow = document.getElementById('step2-shipping-row');
            if (shippingRow) {
                shippingRow.classList.remove('hidden');
                if (selectedShippingFreightBalance == 1) {
                    document.getElementById('step2-shipping-price').textContent = '{{ __("messages.trust_cod_shipping") }}';
                } else if (shippingPrice > 0) {
                    document.getElementById('step2-shipping-price').textContent = formatNum(shippingPrice) + ' {{ __("messages.toman") }}';
                } else {
                    document.getElementById('step2-shipping-price').textContent = '{{ __("messages.trust_free_shipping") }}';
                }
            }
            var totalEl = document.getElementById('step2-total');
            if (totalEl) totalEl.textContent = formatNum(total);

            var nextBtn = document.getElementById('step2-next-btn');
            if (nextBtn) nextBtn.disabled = !(selectedAddressId && selectedShippingMethodId);
        }

        // ========== دریافت شهرها ==========
        window.loadCities = function(stateId, targetId) {
            fetch(urls.cities + '?state_id=' + stateId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var select = document.getElementById(targetId);
                    select.innerHTML = '<option value="">انتخاب شهر</option>';
                    data.cities.forEach(function(city) {
                        select.innerHTML += '<option value="' + city.id + '">' + city.name + '</option>';
                    });
                });
        };

        // ========== انتخاب بانک ==========
        window.selectBank = function(bankId) {
            selectedBankId = bankId;
            document.querySelectorAll('.checkout-bank-option').forEach(function(c) { c.classList.remove('selected'); });
            var card = document.querySelector('[data-bank-id="' + bankId + '"]');
            if (card) {
                card.classList.add('selected');
                card.querySelector('input[type="radio"]').checked = true;

                var bankType = card.getAttribute('data-bank-type');
                if (bankType === 'card_to_card') {
                    var cardNum = card.getAttribute('data-card-number') || '';
                    var cardHolder = card.getAttribute('data-card-holder') || '';
                    var cardDesc = card.getAttribute('data-card-desc') || '';
                    var displayNum = window.localizeDigits ? window.localizeDigits.convert(cardNum) : cardNum;

                    document.getElementById('modalCardNumber').textContent = displayNum;
                    var holderRow = document.getElementById('modalHolderRow');
                    var descRow = document.getElementById('modalDescRow');
                    if (cardHolder) { holderRow.style.display = ''; document.getElementById('modalCardHolder').textContent = cardHolder; } else { holderRow.style.display = 'none'; }
                    if (cardDesc) { descRow.style.display = ''; document.getElementById('modalCardDesc').textContent = cardDesc; } else { descRow.style.display = 'none'; }

                    clearReceiptInfo();
                    document.getElementById('cardToCardModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    document.body.classList.add('page-blur-active');
                } else {
                    var formTrackingInput = document.getElementById('receiptTrackingCodeForm');
                    var formCardInput = document.getElementById('receiptPayerCardForm');
                    if (formTrackingInput) formTrackingInput.value = '';
                    if (formCardInput) formCardInput.value = '';
                }
            }
            var payBtn = document.getElementById('payBtn');
            if (payBtn) payBtn.disabled = false;
        };

        window.closeCardToCardModal = function() {
            document.getElementById('cardToCardModal').classList.add('hidden');
            document.body.style.overflow = '';
            document.body.classList.remove('page-blur-active');
        };

        window.confirmCardToCard = function() {
            var trackingInput = document.getElementById('receiptTrackingCode');
            var trackingVal = trackingInput ? trackingInput.value.trim() : '';

            if (!trackingVal) {
                trackingInput.style.borderColor = '#ef4444';
                trackingInput.style.animation = 'none'; trackingInput.offsetHeight;
                trackingInput.style.animation = 'shake 0.4s ease';
                setTimeout(function() { trackingInput.style.borderColor = ''; }, 1500);
                return;
            }

            // ── اعتبارسنجی کد پیگیری: فقط عدد، حداقل ۶ رقم ──
            var cleanTracking = trackingVal.replace(/[\s\-]/g, '');
            if (!/^\d{6,16}$/.test(cleanTracking)) {
                trackingInput.style.borderColor = '#ef4444';
                trackingInput.style.animation = 'none'; trackingInput.offsetHeight;
                trackingInput.style.animation = 'shake 0.4s ease';
                trackingInput.setAttribute('title', 'کد پیگیری باید فقط عدد باشد (۶ تا ۱۶ رقم)');
                setTimeout(function() { trackingInput.style.borderColor = ''; trackingInput.removeAttribute('title'); }, 2000);
                return;
            }
            // مقدار تمیزشده را ذخیره کن (بدون فاصله و خط تیره)
            trackingVal = cleanTracking;

            var cardInput = document.getElementById('receiptPayerCard');
            var cardVal = cardInput ? cardInput.value.trim() : '';

            // ── اعتبارسنجی شماره کارت (اختیاری): ۱۶ رقم با خط تیره اختیاری ──
            if (cardVal) {
                var cleanCard = cardVal.replace(/[\s\-]/g, '');
                if (!/^\d{16}$/.test(cleanCard)) {
                    cardInput.style.borderColor = '#ef4444';
                    cardInput.style.animation = 'none'; cardInput.offsetHeight;
                    cardInput.style.animation = 'shake 0.4s ease';
                    cardInput.setAttribute('title', 'شماره کارت باید ۱۶ رقم باشد');
                    setTimeout(function() { cardInput.style.borderColor = ''; cardInput.removeAttribute('title'); }, 2000);
                    return;
                }
                cardVal = cleanCard;
            }

            // Set hidden form values
            document.getElementById('receiptTrackingCodeForm').value = trackingVal;
            document.getElementById('receiptPayerCardForm').value = cardVal;

            // Show info badge on the page
            var badge = document.getElementById('receiptInfoBadge');
            var trackingEl = document.getElementById('receiptInfoTracking');
            var cardEl = document.getElementById('receiptInfoCard');
            if (trackingEl) trackingEl.textContent = 'کد پیگیری: ' + (window.localizeDigits ? window.localizeDigits.convert(trackingVal) : trackingVal);
            if (cardEl) cardEl.textContent = cardVal ? 'کارت واریزکننده: ' + (window.localizeDigits ? window.localizeDigits.convert(cardVal) : cardVal) : '';
            if (cardEl && !cardVal) cardEl.style.display = 'none'; else if (cardEl) cardEl.style.display = '';
            if (badge) badge.classList.remove('hidden');

            closeCardToCardModal();
        };

        window.clearReceiptInfo = function() {
            var trackingInput = document.getElementById('receiptTrackingCode');
            var cardInput = document.getElementById('receiptPayerCard');
            var formTracking = document.getElementById('receiptTrackingCodeForm');
            var formCard = document.getElementById('receiptPayerCardForm');
            if (trackingInput) trackingInput.value = '';
            if (cardInput) cardInput.value = '';
            if (formTracking) formTracking.value = '';
            if (formCard) formCard.value = '';
        };

        window.copyCardNumber = function() {
            var displayText = document.getElementById('modalCardNumber').textContent;
            var num = displayText.replace(/[۰-۹]/g, function(d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }).replace(/-/g, '');
            var btn = document.getElementById('copyCardBtn');
            var orig = btn.textContent;

            function onSuccess() {
                btn.textContent = 'کپی شد! ✓';
                btn.style.background = 'color-mix(in srgb, #22c55e 15%, transparent)';
                btn.style.color = '#22c55e';
                setTimeout(function() {
                    btn.textContent = orig;
                    btn.style.background = '';
                    btn.style.color = '';
                }, 1500);
            }

            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(num).then(onSuccess).catch(function() {
                    fallbackCopy(num, onSuccess);
                });
            } else {
                fallbackCopy(num, onSuccess);
            }

            function fallbackCopy(text, callback) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    callback();
                } catch(e) {
                    // Last resort: show the number for manual copy
                    btn.textContent = 'خطا در کپی';
                    setTimeout(function() { btn.textContent = orig; }, 2000);
                }
                document.body.removeChild(ta);
            }
        };

        // ========== اعمال کد تخفیف ==========
        window.applyDiscountCode = function() {
            var code = document.getElementById('discountCodeInput').value;
            if (!code) return;

            fetch(urls.applyDiscount, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ discount_code: code })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var msgEl = document.getElementById('discountMessage');
                msgEl.style.display = '';
                msgEl.textContent = data.message;
                msgEl.style.color = data.success ? 'var(--success)' : 'var(--danger)';
                if (data.success) {
                    fetch(urls.orderPrice, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(priceData) {
                        discount = priceData.price_discount;
                        totalWithoutShipping = priceData.price_cart - priceData.price_shipping;
                        var total = priceData.price_cart;

                        // مرحله ۳
                        document.getElementById('step3-subtotal').textContent = formatNum(priceData.final_price_sum);
                        var couponRow3 = document.getElementById('step3-coupon-row');
                        if (priceData.price_discount > 0) {
                            couponRow3.classList.remove('hidden');
                            document.getElementById('step3-coupon-amount').textContent = '-' + formatNum(priceData.price_discount);
                        } else {
                            couponRow3.classList.add('hidden');
                        }
                        document.getElementById('step3-total').textContent = formatNum(total);
                        // مرحله ۴
                        document.getElementById('step4-subtotal').textContent = formatNum(priceData.final_price_sum);
                        var couponRow4 = document.getElementById('step4-coupon-row');
                        if (priceData.price_discount > 0) {
                            couponRow4.classList.remove('hidden');
                            document.getElementById('step4-coupon-amount').textContent = '-' + formatNum(priceData.price_discount);
                        } else {
                            couponRow4.classList.add('hidden');
                        }
                        document.getElementById('step4-total').textContent = formatNum(total);
                        document.getElementById('step4-total-inline').textContent = formatNum(total);

                        // مرحله ۲
                        document.getElementById('step2-subtotal').textContent = formatNum(priceData.final_price_sum);
                        var couponRow2 = document.getElementById('step2-coupon-row');
                        if (priceData.price_discount > 0) {
                            couponRow2.classList.remove('hidden');
                            document.getElementById('step2-coupon-amount').textContent = '-' + formatNum(priceData.price_discount);
                        } else {
                            couponRow2.classList.add('hidden');
                        }
                        updateStep2Price();

                        // مرحله ۱
                        var subEl = document.getElementById('cart-subtotal');
                        var totalEl = document.getElementById('cart-total');
                        if (subEl) subEl.textContent = formatNum(priceData.final_price_sum);
                        if (totalEl) totalEl.textContent = formatNum(priceData.final_price_sum - priceData.price_discount);
                        var couponRow1 = document.getElementById('cart-coupon-row');
                        if (priceData.price_discount > 0) {
                            couponRow1.classList.remove('hidden');
                            document.getElementById('cart-coupon-amount').textContent = '-' + formatNum(priceData.price_discount);
                        } else {
                            couponRow1.classList.add('hidden');
                        }
                    });
                }
            });
        };

        // ========== به‌روزرسانی قیمت مرحله ۳ و ۴ ==========
        function updateStep34Price() {
            var total = subtotal + shippingPrice - discount;
            var shippingText = selectedShippingFreightBalance == 1 ? '{{ __("messages.trust_cod_shipping") }}' : (shippingPrice > 0 ? formatNum(shippingPrice) + ' {{ __("messages.toman") }}' : '{{ __("messages.trust_free_shipping") }}');
            // مرحله ۳
            document.getElementById('step3-total').textContent = formatNum(total);
            document.getElementById('step3-shipping-row').classList.remove('hidden');
            document.getElementById('step3-shipping-price').textContent = shippingText;
            // مرحله ۴
            document.getElementById('step4-total').textContent = formatNum(total);
            document.getElementById('step4-total-inline').textContent = formatNum(total);
            document.getElementById('step4-shipping-row').classList.remove('hidden');
            document.getElementById('step4-shipping-price').textContent = shippingText;

            // اطلاعات سقف رایگان برای مراحل ۳ و ۴
            ['step3', 'step4'].forEach(function(step) {
                var info = document.getElementById(step + '-free-ship-info');
                if (!info) return;
                if (selectedPriceCeiling > 0 && selectedShippingFreightBalance != 1) {
                    if (totalWithoutShipping >= selectedPriceCeiling) {
                        var tpl = '{{ __("messages.free_shipping_sidebar_saved", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                        info.className = 'rounded-xl p-3 text-xs font-bold';
                        info.style.background = 'color-mix(in srgb, var(--success) 10%, transparent)';
                        info.style.color = 'var(--success)';
                        info.innerHTML = tpl.replace('__AMOUNT__', '<span class="mono" dir="ltr">' + formatNum(selectedOriginalPrice) + '</span>');
                        info.style.display = 'flex';
                    } else {
                        var remaining = selectedPriceCeiling - totalWithoutShipping;
                        var tpl2 = '{{ __("messages.free_shipping_sidebar_remaining", ["amount" => "__AMOUNT__", "currency" => __("messages.toman")]) }}';
                        info.className = 'rounded-xl p-3 text-xs font-bold';
                        info.style.background = 'color-mix(in srgb, var(--accent) 8%, transparent)';
                        info.style.color = 'var(--accent)';
                        info.innerHTML = tpl2.replace('__AMOUNT__', '<span class="mono" dir="ltr">' + formatNum(remaining) + '</span>') + freeShipProgressBar(totalWithoutShipping, selectedPriceCeiling);
                        info.style.display = 'flex';
                        animateProgressBars();
                    }
                } else {
                    info.style.display = 'none';
                }
            });
        }

// ========== به‌روزرسانی قیمت از سرور ==========
        function fetchFreshPrices(callback) {
            fetch(urls.orderPrice, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(priceData) {
                subtotal = priceData.final_price_sum;
                discount = priceData.price_discount;
                totalWithoutShipping = priceData.price_cart - priceData.price_shipping;
                shippingPrice = parseInt(priceData.price_shipping) || 0;

                // مرحله ۱
                var subEl1 = document.getElementById('cart-subtotal');
                var totalEl1 = document.getElementById('cart-total');
                if (subEl1) subEl1.textContent = formatNum(subtotal);
                if (totalEl1) totalEl1.textContent = formatNum(totalWithoutShipping);
                var couponRow1 = document.getElementById('cart-coupon-row');
                if (couponRow1) {
                    if (discount > 0) { couponRow1.classList.remove('hidden'); document.getElementById('cart-coupon-amount').textContent = '-' + formatNum(discount); }
                    else { couponRow1.classList.add('hidden'); }
                }

                // مرحله ۲
                var subEl2 = document.getElementById('step2-subtotal');
                if (subEl2) subEl2.textContent = formatNum(subtotal);
                var couponRow2 = document.getElementById('step2-coupon-row');
                if (couponRow2) {
                    if (discount > 0) { couponRow2.classList.remove('hidden'); document.getElementById('step2-coupon-amount').textContent = '-' + formatNum(discount); }
                    else { couponRow2.classList.add('hidden'); }
                }
                updateStep2Price();

                // مرحله ۳
                var subEl3 = document.getElementById('step3-subtotal');
                if (subEl3) subEl3.textContent = formatNum(subtotal);
                var couponRow3 = document.getElementById('step3-coupon-row');
                if (couponRow3) {
                    if (discount > 0) { couponRow3.classList.remove('hidden'); document.getElementById('step3-coupon-amount').textContent = '-' + formatNum(discount); }
                    else { couponRow3.classList.add('hidden'); }
                }

                // مرحله ۴
                var subEl4 = document.getElementById('step4-subtotal');
                if (subEl4) subEl4.textContent = formatNum(subtotal);
                var couponRow4 = document.getElementById('step4-coupon-row');
                if (couponRow4) {
                    if (discount > 0) { couponRow4.classList.remove('hidden'); document.getElementById('step4-coupon-amount').textContent = '-' + formatNum(discount); }
                    else { couponRow4.classList.add('hidden'); }
                }

                if (callback) callback();
            });
        }

        
        // ========== بررسی آیتم‌های ناموجود در سبد ==========
        (function() {
            var outOfStockCards = document.querySelectorAll(".opacity-50.grayscale");
            if (outOfStockCards.length > 0) {
                var names = [];
                outOfStockCards.forEach(function(card) {
                    var nameEl = card.querySelector("a.font-bold");
                    if (nameEl) names.push(nameEl.textContent.trim());
                });
                if (names.length > 0) {
                    setTimeout(function() {
                        if (typeof Swal !== "undefined") {
                            Swal.fire({
                                title: "\u0645\u0648\u062c\u0648\u062f\u06cc \u062a\u0645\u0627\u0645 \u0634\u062f\u0647",
                                html: "<div style=\"text-align:right;font-size:14px;line-height:1.8;\">\u0645\u0648\u062c\u0648\u062f\u06cc \u0645\u062d\u0635\u0648\u0644\u0627\u062a \u0632\u06cc\u0631 \u062a\u0645\u0627\u0645 \u0634\u062f\u0647 \u0627\u0633\u062a:</div><ul style=\"text-align:right;list-style:none;padding:8px 0;margin:0;\">" + names.map(function(n) { return "<li style=\"padding:3px 0;color:#ef4444;font-weight:700;\">\u2022 " + n + "</li>"; }).join("") + "</ul><div style=\"font-size:12px;color:var(--text-secondary);margin-top:8px;\">\u0627\u06cc\u0646 \u0645\u062d\u0635\u0648\u0644\u0627\u062a \u0628\u0647 \u0635\u0648\u0631\u062a \u062e\u0648\u062f\u06a9\u0627\u0631 \u0627\u0632 \u0633\u0628\u062f \u062d\u0630\u0641 \u0645\u06cc\u200c\u0634\u0648\u0646\u062f.</div>",
                                icon: "warning",
                                confirmButtonText: "\u0628\u0627\u0634\u0647\u002c \u062d\u0630\u0641 \u0634\u0648\u062f",
                                confirmButtonColor: "#ef4444",
                                allowOutsideClick: false,
                                customClass: { popup: "swal2-rtl" }
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    outOfStockCards.forEach(function(card) {
                                        var removeBtn = card.querySelector("[data-cart-remove]");
                                        if (removeBtn) removeBtn.click();
                                    });
                                    setTimeout(function() { window.location.reload(); }, 1500);
                                }
                            });
                        }
                    }, 500);
                }
            }
        })();

        // ========== بررسی موجودی قبل از رفتن به مرحله ۴ ==========
        (function() {
            var _origGoToStep = window.goToStep;
            window.goToStep = function(step) {
                if (step === 4) {
                    fetch(urls.validateStock, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken(),
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            _origGoToStep(step);
                        } else {
                            if (typeof Swal !== "undefined") {
                                Swal.fire({
                                    title: "\u0645\u0648\u062c\u0648\u062f\u06cc \u062a\u063a\u06cc\u06cc\u0631 \u06a9\u0631\u062f\u0647",
                                    text: "\u0622\u06cc\u062a\u0645\u200c\u0647\u0627\u06cc \u0646\u0627\u0645\u0648\u062c\u0648\u062f \u0627\u0632 \u0633\u0628\u062f \u062d\u0630\u0641 \u0634\u062f\u0646\u062f.",
                                    icon: "warning",
                                    confirmButtonText: "\u0628\u0627\u0634\u0647",
                                    allowOutsideClick: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                alert("\u0645\u0648\u062c\u0648\u062f\u06cc \u0628\u0631\u062e\u06cc \u0645\u062d\u0635\u0648\u0644\u0627\u062a \u062a\u063a\u06cc\u06cc\u0631 \u06a9\u0631\u062f\u0647 \u0627\u0633\u062a.");
                                window.location.reload();
                            }
                        }
                    })
                    .catch(function() {
                        _origGoToStep(step);
                    });
                } else {
                    _origGoToStep(step);
                }
            };
        })();

        var origGoToStep = window.goToStep;
        window.goToStep = function(step) {
            if (step >= 2) {
                fetchFreshPrices(function() {
                    if (step === 3 || step === 4) updateStep34Price();
                    if (step === 2 && selectedAddressId) {
                        selectAddress(selectedAddressId);
                    }
                    origGoToStep(step);
                });
            } else {
                origGoToStep(step);
            }
        };

        // بارگذاری اولیه
        if (currentStep > 1) {
            goToStep(currentStep);
        }
    })();
    </script>
@endpush
