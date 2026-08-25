{{--
    کارت محصول — سازگار با مدل Product و پشتیبانی چندزبانه.
    Props (از کلاس ProductCard): product | rank | soldPercent
    همهٔ محاسبات نمایشی در کلاس کامپوننت انجام می‌شود؛ این ویو فقط رندر است.
--}}

<a href="{{ $url }}" class="neu-card product-card neu-interactive group relative flex flex-col h-full p-4 block" style="text-decoration: none; color: inherit;" data-card-link>

    {{-- نشان رتبه (فقط برای بخش پرفروش‌ترین‌ها) — بزرگ و برجسته، همیشه بالای فید لبه‌ها --}}
    @if($rank)
        <span class="neu-rank-badge"
              style="--rank-color: {{ $rankStyle['color'] }}; --rank-text: {{ $rankStyle['text'] }};">
            {{ $rank }}
        </span>
    @endif

    {{-- دکمه‌های عملیات سریع — آیکون‌های خطی ظریف (z-20 بالاتر از لایهٔ محو لبه‌ها) --}}
    <div class="product-actions absolute top-3 end-3 z-20 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <button type="button"
                class="neu-btn neu-btn-icon w-8 h-8 flex items-center justify-center text-sm bg-[var(--bg-elevated)] wishlist-heart {{ $inWishlist ? 'active' : '' }}"
                data-wishlist-toggle
                data-wishlist-url="{{ localizedRoute('wishlist.toggle', ['product' => $product->slug]) }}"
                data-wishlist-active="{{ $inWishlist ? 1 : 0 }}"
                data-add-label="{{ __('messages.add_to_wishlist') }}"
                data-remove-label="{{ __('messages.remove_from_wishlist') }}"
                aria-label="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}"
                title="{{ $inWishlist ? __('messages.remove_from_wishlist') : __('messages.add_to_wishlist') }}"
                onclick="event.stopPropagation()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
            </svg>
        </button>
        <button type="button"
                class="neu-btn neu-btn-icon w-8 h-8 flex items-center justify-center text-sm bg-[var(--bg-elevated)]"
                data-quick-view-url="{{ localizedRoute('products.quick-view', ['product' => $product->slug]) }}"
                aria-label="{{ __('messages.quick_view') }}" title="{{ __('messages.quick_view') }}"
                onclick="event.stopPropagation()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </button>
    </div>

    <div class="flex flex-col flex-1">
        {{-- تصویر محصول --}}
        <div class="product-image-wrap relative mb-3 overflow-hidden rounded-xl bg-[var(--bg-elevated)]/60" style="aspect-ratio: 1/1;">
            <img src="{{ $imageUrl }}"
                 alt="{{ $name }}"
                 width="400" height="400"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('assets/admin/images/image-not-found.png') }}'"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[700ms] ease-[cubic-bezier(0.16,1,0.3,1)] will-change-transform">

            {{-- نشان‌ها (فقط اگر رتبه‌بندی نباشد) --}}
            @if(!$rank)
                @if($lowStock)
                    <span class="badge-urgency absolute top-2 start-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full animate-pulse">
                        {{ __('messages.only_left', ['count' => $product->stock]) }}
                    </span>
                @elseif($hasDiscount)
                    <span class="badge-sale absolute top-2 start-2 z-10">
                        {{ $discountPercent }}٪-
                    </span>
                @elseif($isNew)
                    <span class="badge-new absolute top-2 start-2 z-10 bg-[var(--success)] text-white text-[10px] font-bold px-2 py-1 rounded-full">
                        {{ __('messages.new_badge') }}
                    </span>
                @endif
            @endif
        </div>

        {{-- نام محصول --}}
        <h4 class="text-[13px] font-bold line-clamp-2 leading-relaxed" style="color: var(--text-primary);">
            {{ $name }}
        </h4>

        {{-- امتیاز و تعداد نظرات --}}
        @if($rating > 0)
            <div class="flex items-center gap-1.5 mt-1.5">
                <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md"
                     style="background: color-mix(in srgb, var(--gold) 10%, transparent);">
                    <svg class="w-3 h-3" style="color: var(--gold);" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="text-[11px] font-bold" style="color: var(--gold);">{{ number_format($rating, 1) }}</span>
                </div>
                <span class="text-[10px]" style="color: var(--text-secondary);">({{ $reviewCount }})</span>
            </div>
        @endif

        {{-- بخش قیمت و دکمه خرید --}}
        <div class="mt-auto pt-2 border-t border-[var(--shadow-light)]/50">
            <div class="mb-3">
                {{-- قیمت قبلی + برچسب تخفیف — خط کوچک بالای قیمت نهایی --}}
                @if($hasDiscount)
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="mono text-[11px] font-semibold line-through" style="color: var(--text-secondary); opacity: .8;" dir="ltr">
                            {{ number_format($originalPrice) }}
                        </span>
                        <span class="price-off">{{ $discountPercent }}٪-</span>
                    </div>
                @endif

                {{-- قیمت نهایی — بزرگ، بولد و با هاله برای جلب توجه --}}
                <div class="flex items-baseline gap-1.5">
                    <span class="mono font-black text-xl leading-none price-glow" style="color: var(--accent);" dir="ltr">
                        {{ number_format($finalPrice) }}
                    </span>
                    <span class="text-[11px] font-bold" style="color: var(--text-secondary);">{{ __('messages.toman') }}</span>
                </div>
            </div>

            {{-- نوار پیشرفت فروش (فقط در صورت ارسال soldPercent) — پر شدن با scaleX، هماهنگ با ورود پله‌ای گرید --}}
            @if($soldPercent !== null)
                <div class="stock-bar w-full h-1.5 rounded-full bg-[var(--shadow-dark)] mb-1 overflow-hidden">
                    <div class="stock-bar-fill h-full rounded-full bg-[var(--urgency)]"
                         style="--sold: {{ $soldPercent / 100 }};"></div>
                </div>
                <p class="text-[10px] font-semibold mb-3" style="color: {{ $soldPercent >= 80 ? 'var(--urgency)' : 'var(--text-secondary)' }};">
                    {{ $soldPercent }}٪ {{ __('messages.sold_percent') }}
                </p>
            @endif

            {{-- دکمه افزودن به سبد خرید — Button-in-Button: آیکون داخل چیپ دایره‌ای، با تنش جنبشی روی هاور --}}
            <button type="button"
                    class="add-to-cart"
                    @disabled($outOfStock)
                    data-success="{{ __('messages.added_short') }}"
                    data-cart-url="{{ route('cart.add', ['product' => $product->id]) }}"
                    data-csrf="{{ csrf_token() }}"
                    onclick="event.stopPropagation()">

                @if($outOfStock)
                    <span class="add-to-cart-chip" aria-hidden="true">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </span>
                    <span>{{ __('messages.out_of_stock') }}</span>
                @else
                    <span class="add-to-cart-chip" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <path d="M3 6h18"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                    </span>
                    <span>{{ __('messages.add_to_cart') }}</span>
                @endif
            </button>
        </div>
    </div>
</a>
