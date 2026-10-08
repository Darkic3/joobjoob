{{--
    کارت محصول — تم Eco-Collectible («برگهٔ کلکسیونی»)
    Props (کلاس ProductCard): product | rank | soldPercent
      + collectibleId | materialTag | cardStatusTag | rankLabel | rankTone

    ساختار:
      article.eco-pc (container + سایهٔ drop-shadow)
        └ .eco-pc-ticket (سطح کارت؛ دو بریدگی بلیتی روی خط پرفراژ با mask)
            ├ .eco-pc-media   → تصویر، برچسب انبار، اکشن‌های سریع، مُهر ناموجود
            └ .eco-pc-body    → تگ‌ها، عنوان (لینک کشیده روی کل کارت)، امتیاز، قیمت، CTA

    دسترس‌پذیری: به‌جای <a> دور کل کارت (که دکمه‌ها را داخل لینک می‌گذاشت)،
    لینک روی عنوان است و با ::after کل کارت را کلیک‌پذیر می‌کند.

    هوک‌های JS حفظ شده:
      data-card-link / data-wishlist-toggle(+data-*) / data-quick-view-url / .add-to-cart(+data-*)
--}}

<article class="eco-pc {{ $outOfStock ? 'is-out' : '' }}" data-card-link>
    <div class="eco-pc-ticket">

        {{-- ═══ قاب تصویر ═══ --}}
        <div class="eco-pc-media">
            @if($hasImage)
                <img src="{{ $imageUrl }}"
                     alt="{{ $name }}"
                     width="400" height="400"
                     loading="lazy" decoding="async"
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='grid'"
                     class="eco-pc-img">
            @endif
            <div class="eco-pc-fallback" @if($hasImage) style="display: none;" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5"/>
                </svg>
                <span>{{ __('messages.no_image') }}</span>
            </div>

            {{-- برچسب انبار (امضای تم): سوراخ آویز + رتبه (اختیاری) + شناسه --}}
            <span class="eco-pc-label" dir="ltr">
                <span class="eco-pc-label-hole" aria-hidden="true"></span>
                @if($rankLabel)
                    <span class="eco-pc-label-rank eco-pc-label-rank--{{ $rankTone }}">{{ $rankLabel }}</span>
                @endif
                <span class="eco-pc-label-id">{{ $collectibleId }}</span>
            </span>

            {{-- اکشن‌های سریع --}}
            <div class="eco-pc-actions">
                <button type="button"
                        class="eco-pc-action eco-pc-action--wish wishlist-heart"
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
                        aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                        aria-label="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}"
                        title="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                    </svg>
                </button>
                <button type="button"
                        class="eco-pc-action eco-pc-action--peek"
                        data-quick-view-url="{{ localizedRoute('products.quick-view', ['product' => $product->slug]) }}"
                        aria-label="{{ __('messages.quick_view') }}" title="{{ __('messages.quick_view') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.42 8.1 7.36 5 12 5s8.58 3.1 9.94 6.65a1 1 0 0 1 0 .7C20.58 15.9 16.64 19 12 19s-8.58-3.1-9.94-6.65z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            {{-- مُهر ناموجود --}}
            @if($outOfStock)
                <span class="eco-pc-stamp">{{ __('messages.out_of_stock') }}</span>
            @endif
        </div>

        {{-- ═══ بدنه ═══ --}}
        <div class="eco-pc-body">
            @if($materialTag || $cardStatusTag)
                <div class="eco-pc-tags">
                    @if($materialTag)
                        <span class="eco-pc-tag eco-pc-tag--sage">{{ $materialTag }}</span>
                    @endif
                    @if($cardStatusTag)
                        <span class="eco-pc-tag eco-pc-tag--{{ $cardStatusTag['tone'] }}">
                            <span class="eco-pc-tag-dot" aria-hidden="true"></span>{{ $cardStatusTag['label'] }}
                        </span>
                    @endif
                </div>
            @endif

            <h3 class="eco-pc-title">
                <a href="{{ $url }}" class="eco-pc-link">{{ $name }}</a>
            </h3>

            @if($rating > 0)
                <div class="eco-pc-rating" title="{{ __('messages.review_average') }}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span class="eco-pc-rating-value mono">{{ number_format($rating, 1) }}</span>
                    <span class="eco-pc-rating-count" dir="ltr">({{ $reviewCount }})</span>
                </div>
            @endif

            @if($soldPercent !== null)
                <div class="eco-pc-sold {{ $soldPercent >= 80 ? 'is-hot' : '' }}">
                    <div class="eco-pc-sold-track" role="progressbar" aria-valuenow="{{ $soldPercent }}" aria-valuemin="0" aria-valuemax="100"
                         aria-label="{{ $soldPercent }}٪ {{ __('messages.sold_percent') }}">
                        <span class="eco-pc-sold-fill" style="--sold: {{ $soldPercent / 100 }};"></span>
                    </div>
                    <span class="eco-pc-sold-text">{{ $soldPercent }}٪ {{ __('messages.sold_percent') }}</span>
                </div>
            @endif

            {{-- ═══ قیمت + CTA ═══ --}}
            <div class="eco-pc-foot">
                <div class="eco-pc-price">
                    @if($hasDiscount)
                        <div class="eco-pc-price-was">
                            <del class="mono" dir="ltr"><span class="sr-only">{{ __('messages.price') }}: </span>{{ format_price($originalPrice) }}</del>
                            <span class="eco-pc-discount" aria-label="{{ __('messages.discount') }} {{ $discountPercent }}٪">{{ $discountPercent }}٪</span>
                        </div>
                    @endif
                    <div class="eco-pc-price-now">
                        <span class="eco-pc-price-value mono" dir="ltr">{{ format_price($finalPrice) }}</span>
                        <span class="eco-pc-price-unit">{{ __('messages.toman') }}</span>
                    </div>
                </div>

                <button type="button"
                        class="eco-pc-cta add-to-cart"
                        @disabled($outOfStock)
                        data-success="{{ __('messages.added_short') }}"
                        data-cart-url="{{ route('cart.add', ['product' => $product->id]) }}"
                        data-csrf="{{ csrf_token() }}">
                    @if($outOfStock)
                        <span>{{ __('messages.out_of_stock') }}</span>
                    @else
                        <svg class="eco-pc-cta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                        </svg>
                        <span>{{ __('messages.add_to_cart') }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>
</article>
