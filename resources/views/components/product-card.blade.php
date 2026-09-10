{{--
    کارت محصول — بازطراحی مدرن با Tailwind CSS
    Props: product | rank | soldPercent
    حفظ کامل منطق Backend + بهبود UI/UX
--}}

<a href="{{ $url }}" class="group block bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden" style="text-decoration: none; color: inherit;" data-card-link>

    {{-- ═══ بخش تصویر ═══ --}}
    <div class="relative aspect-square overflow-hidden bg-gray-100">
        {{-- تصویر محصول --}}
        @if($hasImage)
            <img src="{{ $imageUrl }}"
                 alt="{{ $name }}"
                 width="400" height="400"
                 loading="lazy"
                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
            {{-- placeholder مخفی برای زمانی که تصویر اصلی خراب شود --}}
            <div class="absolute inset-0 hidden items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="mt-2 text-[11px] text-gray-400">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="mt-2 text-[11px] text-gray-400">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @endif

        {{-- ═══ بج‌ها ═══ --}}
        <div class="absolute top-3 start-3 flex flex-col gap-2 z-10">
            {{-- بج تخفیف --}}
            @if($hasDiscount)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-red-500 shadow-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                    </svg>
                    {{ $discountPercent }}٪-
                </span>
            @endif

            {{-- بج موجودی کم --}}
            @if($lowStock && !$hasDiscount)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-orange-500 shadow-lg animate-pulse">
                    {{ __('messages.only_left', ['count' => $product->stock]) }}
                </span>
            @endif

            {{-- بج جدید --}}
            @if($isNew && !$hasDiscount && !$lowStock)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-emerald-500 shadow-lg">
                    {{ __('messages.new_badge') }}
                </span>
            @endif
        </div>

        {{-- بج رتبه --}}
        @if($rank)
            <span class="absolute top-3 end-3 w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black text-white shadow-lg z-10"
                  style="background: {{ $rankStyle['color'] }};">
                {{ $rank }}
            </span>
        @endif

        {{-- دکمه‌های عملیات سریع --}}
        <div class="absolute top-3 end-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 @if($rank) mt-14 @endif">
            <button type="button"
                    class="w-9 h-9 rounded-xl bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center hover:bg-white hover:scale-110 transition-all duration-200 wishlist-heart {{ $inWishlist ? 'text-red-500' : 'text-gray-600 hover:text-red-500' }}"
                    data-wishlist-toggle
                    data-wishlist-url="{{ localizedRoute('wishlist.toggle', ['product' => $product->slug]) }}"
                    data-wishlist-active="{{ $inWishlist ? 1 : 0 }}"
                    data-add-label="{{ __('messages.add_to_wishlist') }}"
                    data-remove-label="{{ __('messages.remove_from_wishlist') }}"
                    data-wishlist-title-confirm="{{ __('messages.confirm_remove_wishlist_title') }}"
                    data-wishlist-text-confirm="{{ __('messages.confirm_remove_wishlist') }}"
                    data-wishlist-confirm-label="{{ __('messages.confirm_remove_wishlist_confirm') }}"
                    data-wishlist-cancel-label="{{ __('messages.confirm_remove_wishlist_cancel') }}"
                    data-wishlist-removed-label="{{ __('messages.removed_from_wishlist') }}"
                    aria-label="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}"
                    title="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                </svg>
            </button>
            <button type="button"
                    class="w-9 h-9 rounded-xl bg-white/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-gray-600 hover:bg-white hover:scale-110 hover:text-blue-500 transition-all duration-200"
                    data-quick-view-url="{{ localizedRoute('products.quick-view', ['product' => $product->slug]) }}"
                    aria-label="{{ __('messages.quick_view') }}" title="{{ __('messages.quick_view') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        {{-- لایه ناموجود --}}
        @if($outOfStock)
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center z-20">
                <span class="px-4 py-2 rounded-xl bg-white/90 text-sm font-bold text-gray-700 shadow-lg">
                    {{ __('messages.out_of_stock') }}
                </span>
            </div>
        @endif
    </div>

    {{-- ═══ بخش محتوا ═══ --}}
    <div class="p-4">
        {{-- عنوان محصول --}}
        <h4 class="text-sm font-bold text-gray-900 line-clamp-2 leading-relaxed mb-2 min-h-[2.5rem]">
            {{ $name }}
        </h4>

        {{-- امتیاز --}}
        @if($rating > 0)
            <div class="flex items-center gap-1.5 mb-3">
                <div class="flex items-center gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i <= round($rating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-[11px] text-gray-500">({{ $reviewCount }})</span>
            </div>
        @endif

        {{-- نوار پیشرفت فروش --}}
        @if($soldPercent !== null)
            <div class="mb-3">
                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $soldPercent >= 80 ? 'bg-red-500' : 'bg-emerald-500' }}"
                         style="width: {{ $soldPercent }}%;"></div>
                </div>
                <p class="text-[10px] font-semibold mt-1 {{ $soldPercent >= 80 ? 'text-red-500' : 'text-gray-500' }}">
                    {{ $soldPercent }}٪ {{ __('messages.sold_percent') }}
                </p>
            </div>
        @endif

        {{-- ═══ قیمت ═══ --}}
        <div class="mb-3">
            @if($hasDiscount)
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs text-gray-400 line-through mono" dir="ltr">
                        {{ format_price($originalPrice) }}
                    </span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-100 text-red-600">
                        {{ $discountPercent }}٪-
                    </span>
                </div>
            @endif
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl font-black text-indigo-600 mono" dir="ltr">
                    {{ format_price($finalPrice) }}
                </span>
                <span class="text-[11px] text-gray-500 font-medium">{{ __('messages.toman') }}</span>
            </div>
        </div>

        {{-- ═══ دکمه افزودن به سبد ═══ --}}
        <button type="button"
                class="add-to-cart w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold transition-all duration-200
                       {{ $outOfStock
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 active:scale-[0.98]' }}"
                @disabled($outOfStock)
                data-success="{{ __('messages.added_short') }}"
                data-cart-url="{{ route('cart.add', ['product' => $product->id]) }}"
                data-csrf="{{ csrf_token() }}">

            @if($outOfStock)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                </svg>
                <span>{{ __('messages.out_of_stock') }}</span>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <span>{{ __('messages.add_to_cart') }}</span>
            @endif
        </button>
    </div>
</a>
