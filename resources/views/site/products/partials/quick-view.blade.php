{{-- مودال پیش‌نمایش سریع محصول — محتوای این پارشال با fetch داخل #quick-view-content تزریق می‌شود --}}
<div class="quick-view-grid">
    <div class="quick-view-media relative">
        @if($product->main_image_url)
            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->trans('name') }}"
                 class="w-full h-full object-cover rounded-2xl"
                 loading="lazy"
                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"
                 width="600" height="600">
            <div class="absolute inset-0 hidden items-center justify-center bg-gray-50 rounded-2xl">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="mt-2 text-xs text-gray-400">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-50 rounded-2xl">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm15.75-11.25H21v.75a.75.75 0 0 1-.75.75h-1.5" />
                    </svg>
                    <p class="mt-2 text-xs text-gray-400">{{ __('messages.no_image') }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="quick-view-body">
        @if($product->category)
            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full mb-3"
                  style="background: color-mix(in srgb, var(--accent) 12%, transparent); color: var(--accent);">
                {{ $product->category->trans('name') }}
            </span>
        @endif

        <h3 class="text-lg font-black leading-snug mb-2" style="color: var(--text-primary);">
            {{ $product->trans('name') }}
        </h3>

        @if($product->reviews->isNotEmpty())
            <div class="flex items-center gap-2 text-xs mb-3">
                <span class="tracking-widest" style="color: var(--gold);">
                    {{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}
                </span>
                <span style="color: var(--text-secondary);">({{ $product->reviews->count() }})</span>
            </div>
        @endif

        @if($product->trans('short_description'))
            <p class="text-sm leading-relaxed mb-4" style="color: var(--text-secondary);">
                {{ $product->trans('short_description') }}
            </p>
        @endif

        <div class="flex items-end gap-2 mb-5">
            @if($product->has_discount)
                <span class="mono text-sm line-through mb-1" style="color: var(--text-secondary); opacity: .8;" dir="ltr">
                    {{ format_price($product->price) }}
                </span>
            @endif
            <span class="mono font-black text-2xl leading-none" style="color: var(--accent);" dir="ltr">
                {{ format_price($product->final_price) }}
            </span>
            <span class="text-xs font-bold mb-0.5" style="color: var(--text-secondary);">{{ __('messages.toman') }}</span>
        </div>

        <form action="{{ localizedRoute('cart.add', ['product' => $product->id]) }}" method="POST"
              class="flex items-center gap-3" data-quick-view-add>
            @csrf
            <input type="number" name="qty" value="1" min="1"
                   class="neu-input w-20 text-center text-sm"
                   aria-label="{{ __('messages.quantity') }}">
            <button type="submit" class="neu-btn neu-btn-primary px-6 py-3 text-sm font-bold flex-1"
                    style="background: var(--accent); color: var(--accent-contrast);"
                    @disabled($product->stock === 0)>
                @if($product->stock === 0)
                    {{ __('messages.out_of_stock') }}
                @else
                    {{ __('messages.add_to_cart') }}
                @endif
            </button>
        </form>

        <a href="{{ localizedRoute('products.show', ['product' => $product->slug]) }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold mt-4 hover:opacity-70 transition-opacity"
           style="color: var(--accent);">
            {{ __('messages.view_details') }}
            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>
</div>
