{{--
    کارت محصول — تم Eco-Collectible (فاز ۳)
    Props (کلاس ProductCard): product | rank | soldPercent
      + collectibleId | materialTag | statusTag (نمایشی، از اکسسورهای مدل)
    منطق Backend دست‌نخورده؛ هوک‌های JS حفظ شده:
      data-card-link / data-wishlist-toggle(+data-*)/ data-quick-view-url / .add-to-cart(+data-*)
--}}

<a href="{{ $url }}" class="eco-pc group" data-card-link style="text-decoration: none; color: inherit;">

    {{-- ═══ قاب تصویر (بژ + سایهٔ محو زیر محصول) ═══ --}}
    <div class="eco-pc-media">
        @if($hasImage)
            <img src="{{ $imageUrl }}"
                 alt="{{ $name }}"
                 width="400" height="400"
                 loading="lazy"
                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"
                 class="eco-pc-img">
            {{-- placeholder مخفی برای زمانی که تصویر اصلی خراب شود --}}
            <div class="eco-pc-img-fallback" style="display: none;">
                <div class="eco-pc-img-fallback-inner">
                    <svg class="eco-pc-img-fallback-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="eco-pc-img-fallback-text">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @else
            <div class="eco-pc-img-fallback">
                <div class="eco-pc-img-fallback-inner">
                    <svg class="eco-pc-img-fallback-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="eco-pc-img-fallback-text">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @endif

        {{-- شناسهٔ کلکسیونی («برچسب انبار») --}}
        <span class="eco-pc-id eco-id" dir="ltr">{{ $collectibleId }}</span>

        {{-- نشان رتبه (فقط پرفروش‌ترین‌ها) --}}
        @if($rank)
            <span class="eco-pc-rank" style="background: {{ $rankStyle['color'] }}; color: {{ $rankStyle['text'] }};">{{ $rank }}</span>
        @endif

        {{-- دکمه‌های عملیات سریع --}}
        <div class="eco-pc-actions @if($rank) eco-pc-actions--below-rank @endif">
            <button type="button"
                    class="eco-pc-action wishlist-heart {{ $inWishlist ? 'is-active' : '' }}"
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
                <svg width="17" height="17" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                </svg>
            </button>
            <button type="button"
                    class="eco-pc-action"
                    data-quick-view-url="{{ localizedRoute('products.quick-view', ['product' => $product->slug]) }}"
                    aria-label="{{ __('messages.quick_view') }}" title="{{ __('messages.quick_view') }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        {{-- لایه ناموجود --}}
        @if($outOfStock)
            <div class="eco-pc-out">
                <span class="eco-pc-out-label">{{ __('messages.out_of_stock') }}</span>
            </div>
        @endif
    </div>

    {{-- ═══ بدنه ═══ --}}
    <div class="eco-pc-body">
        {{-- تگ‌های متریال و وضعیت --}}
        @if($materialTag || $statusTag)
            <div class="eco-pc-tags">
                @if($materialTag)
                    <span class="eco-tag eco-tag--sage">{{ $materialTag }}</span>
                @endif
                @if($statusTag)
                    <span class="eco-tag eco-tag--{{ $statusTag['tone'] }}">{{ $statusTag['label'] }}</span>
                @endif
            </div>
        @endif

        {{-- عنوان --}}
        <h4 class="eco-pc-title">{{ $name }}</h4>

        {{-- امتیاز --}}
        @if($rating > 0)
            <div class="eco-pc-rating">
                <span class="eco-pc-stars" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="{{ $i <= round($rating) ? 'is-on' : '' }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    @endfor
                </span>
                <span class="eco-pc-rating-count">({{ $reviewCount }})</span>
            </div>
        @endif

        {{-- نوار پیشرفت فروش --}}
        @if($soldPercent !== null)
            <div class="eco-pc-sold">
                <div class="eco-pc-sold-track">
                    <div class="eco-pc-sold-fill {{ $soldPercent >= 80 ? 'is-hot' : '' }}" style="width: {{ $soldPercent }}%;"></div>
                </div>
                <p class="eco-pc-sold-text {{ $soldPercent >= 80 ? 'is-hot' : '' }}">
                    {{ $soldPercent }}٪ {{ __('messages.sold_percent') }}
                </p>
            </div>
        @endif

        {{-- ═══ قیمت (تراکوتا، مونو) ═══ --}}
        <div class="eco-pc-price">
            @if($hasDiscount)
                <span class="eco-pc-price-old mono" dir="ltr">{{ format_price($originalPrice) }}</span>
            @endif
            <div class="eco-pc-price-row">
                <span class="eco-pc-price-now mono" dir="ltr">{{ format_price($finalPrice) }}</span>
                <span class="eco-pc-price-unit">{{ __('messages.toman') }}</span>
            </div>
        </div>

        {{-- ═══ افزودن به سبد ═══ --}}
        <button type="button"
                class="eco-btn eco-btn--clay eco-btn--block add-to-cart"
                @disabled($outOfStock)
                data-success="{{ __('messages.added_short') }}"
                data-cart-url="{{ route('cart.add', ['product' => $product->id]) }}"
                data-csrf="{{ csrf_token() }}">
            @if($outOfStock)
                <svg class="eco-pc-cart-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                </svg>
                <span>{{ __('messages.out_of_stock') }}</span>
            @else
                <svg class="eco-pc-cart-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <span>{{ __('messages.add_to_cart') }}</span>
            @endif
        </button>
    </div>
</a>
