@extends('layouts.site.app')

@section('title', $product->trans('name'))

{{-- داده‌های نمایشی (mainImage/specGroups/textValues/mainSpecs/variantsData/minVariant) در کنترلر آماده می‌شود --}}
@section('content')

    {{-- Breadcrumb --}}
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ localizedRoute('products.index') }}"
           class="hover:text-[var(--accent)] transition-colors">{{ __('messages.products') }}</a>
        @if($product->category)
            <span class="mx-1">/</span>
            <span style="color: var(--text-primary);">{{ $product->category->trans('name') }}</span>
        @endif
    </p>

    <div class="grid lg:grid-cols-2 gap-10 mb-14">

        {{-- ۱. گالری تصاویر --}}
        <div>
            <div class="neu-flat-lg p-4 mb-4">
                <div class="image-zoom-wrap" id="image-zoom-wrap">
                    <img id="main-image" src="{{ $mainImage }}"
                         alt="{{ $product->trans('name') }}"
                         class="w-full rounded-xl object-cover transition-all duration-300"
                         style="aspect-ratio:1/1;">
                    <div class="zoom-lens" id="zoom-lens"></div>
                </div>
            </div>

            @if($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($product->images as $img)
                        <button onclick="document.getElementById('main-image').src='{{ $img->url }}'"
                                class="neu-flat-sm p-1 w-20 h-20 flex-shrink-0 overflow-hidden hover:neu-pressed transition-all">
                            <img src="{{ $img->url }}" class="w-full h-full object-cover rounded-lg" alt="thumbnail">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- لایت‌باکس تمام‌صفحه --}}
            <div class="lightbox" id="product-lightbox">
                <button type="button" class="lightbox__close" onclick="closeLightbox()" aria-label="بستن">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <button type="button" class="lightbox__arrow lightbox__arrow--prev" onclick="lightboxNav(-1)" aria-label="قبلی">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="lightbox__img-wrap">
                    <img class="lightbox__img" id="lightbox-img" src="" alt="">
                </div>
                <button type="button" class="lightbox__arrow lightbox__arrow--next" onclick="lightboxNav(1)" aria-label="بعدی">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="lightbox__counter" id="lightbox-counter"></div>
            </div>
        </div>

        {{-- ۲. اطلاعات محصول --}}
        <div class="flex flex-col">
            @php
                $allImages = collect([$mainImage]);
                foreach ($product->images as $img) {
                    if ($img->url !== $mainImage) $allImages->push($img->url);
                }
            @endphp
            <h1 class="text-2xl font-extrabold mb-3 leading-tight">{{ $product->trans('name') }}</h1>

            {{-- برند + امتیاز --}}
            <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                @if($product->brand)
                    <a href="{{ localizedRoute('products.index', ['brand' => $product->brand->url]) }}"
                       class="inline-flex items-center gap-2 neu-flat-sm px-4 py-2 rounded-xl text-sm font-bold hover:text-[var(--accent)] transition-colors">
                        @if($product->brand->image)
                            <img
                                src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $product->brand->image) }}"
                                class="w-6 h-6 object-contain" alt="{{ $product->brand->trans('title') }}">
                        @endif
                        {{ $product->brand->trans('title') }}
                    </a>
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-[var(--gold)] tracking-wider">
                        {{ str_repeat('★', (int) round($product->average_rating)) }}{{ str_repeat('☆', 5 - (int) round($product->average_rating)) }}
                    </span>
                    <span class="text-sm" style="color: var(--text-secondary);">
                        ({{ $product->reviews_count }} {{ __('messages.reviews') }})
                    </span>
                </div>
            </div>

            {{-- دکمه‌های اشتراک‌گذاری --}}
            <div class="flex items-center gap-3 mb-6">
                <span class="text-sm font-semibold" style="color: var(--text-secondary);">{{ __('messages.share') }}:</span>
                <x-share-buttons :url="url()->current()" :title="$product->trans('name')" />
            </div>

            {{-- انتخابگر واریانت (رنگ/سایز) --}}
            <div id="variant-selector" class="space-y-5 mb-6">
                @foreach($mainSpecs as $group)
                    <div data-spec-group="{{ $group['id'] }}">
                        <label class="text-sm font-semibold block mb-3">
                            {{ $group['title'] }}
                            <span class="text-xs font-normal" style="color: var(--text-secondary);"
                                  data-selected-label></span>
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($group['children'] as $child)
                                @if($group['is_color'] && $child['color_code'])
                                    <button type="button"
                                            class="variant-option color-option w-10 h-10 rounded-full shadow-sm transition-all duration-200"
                                            style="background-color: {{ $child['color_code'] }};"
                                            data-spec-group="{{ $group['id'] }}"
                                            data-spec-id="{{ $child['id'] }}"
                                            data-variant-ids='{{ json_encode($child['variant_ids']) }}'
                                            title="{{ $child['title'] }}">
                                        <span class="opacity-0 text-white text-sm font-bold">✓</span>
                                    </button>
                                @else
                                    <button type="button"
                                            class="variant-option neu-flat-sm px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                                            data-spec-group="{{ $group['id'] }}"
                                            data-spec-id="{{ $child['id'] }}"
                                            data-variant-ids='{{ json_encode($child['variant_ids']) }}'>
                                        {{ $child['title'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- قیمت — سه حالت (موجود / تماس بگیرید / ناموجود) --}}
            <div class="mb-6" id="price-block">
                @if($product->has_variants)
                    {{-- قیمت از ارزان‌ترین واریانت --}}
                    @if($minVariant)
                        <div class="flex items-center gap-3" data-price-view>
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr"
                                  id="display-final-price">
                                {{ number_format($minVariant->final_price) }}
                            </span>
                            @if($minVariant->discounted_price > 0 && $minVariant->discounted_price < $minVariant->price)
                                <span class="text-lg line-through" style="color: var(--text-secondary);" dir="ltr"
                                      id="display-old-price">
                                    {{ number_format($minVariant->price) }}
                                </span>
                                <span class="text-xs font-bold bg-red-500 text-white px-2 py-1 rounded-full"
                                      id="display-percent">
                                    {{ $minVariant->percent }}٪-
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                            {{ number_format($product->final_price) }}
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-3">
                        @if($product->has_discount)
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                                {{ number_format($product->final_price) }}
                            </span>
                            <span class="text-lg line-through" style="color: var(--text-secondary);" dir="ltr">
                                {{ number_format($product->price) }}
                            </span>
                            <span class="text-xs font-bold bg-red-500 text-white px-2 py-1 rounded-full">
                                {{ $product->discount_percentage }}٪-
                            </span>
                        @else
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                                {{ number_format($product->final_price) }}
                            </span>
                        @endif
                    </div>
                @endif
                <span class="text-sm font-semibold" style="color: var(--text-secondary);">تومان</span>
            </div>

            {{-- توضیحات --}}
            <p class="mb-8 leading-relaxed text-sm" style="color: var(--text-secondary);">
                {{ $product->trans('short_description')  ?: __('messages.no_description') }}
            </p>
            {{--            ?: $product->trans('description')--}}
            {{-- فرم افزودن به سبد خرید --}}
            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="space-y-6 mt-auto">
                @csrf
                <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $minVariant?->id ?? '' }}">

                {{-- تعداد و دکمه خرید --}}
                <div class="flex items-center gap-4 pt-4 border-t border-[var(--shadow-light)]">
                    <div class="neu-flat-sm flex items-center rounded-lg overflow-hidden">
                        <button type="button" onclick="decrementQty()"
                                class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">
                            -
                        </button>
                        <input type="number" id="qty-input" name="qty" value="1" min="1" max="{{ $product->stock }}"
                               class="w-16 h-10 text-center bg-transparent border-none focus:ring-0 text-sm font-bold"
                               readonly>
                        <button type="button" onclick="incrementQty({{ max(1, $product->stock) }})"
                                class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">
                            +
                        </button>
                    </div>

                    <button type="submit" id="add-to-cart-btn"
                            class="neu-btn neu-btn-primary flex-1 py-3 font-bold text-sm flex items-center justify-center gap-2"
                        @disabled($product->stock === 0)>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ __('messages.add_to_cart') }}
                    </button>
                </div>

                {{-- وضعیت موجودی --}}
                <p class="text-sm font-semibold flex items-center gap-2" id="stock-status"
                   style="color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}">
                    <span class="w-2 h-2 rounded-full"
                          style="background-color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}"></span>
                    <span
                        id="stock-text">{{ $product->stock > 0 ? __('messages.in_stock') . " ({$product->stock} عدد)" : __('messages.out_of_stock') }}</span>
                </p>
            </form>
        </div>
    </div>

    {{-- ۳. تب‌ها: توضیحات / مشخصات / نظرات --}}
    <section class="mb-14 reveal-on-scroll">
        <div class="flex flex-wrap gap-2 mb-6" id="detail-tabs">
            <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold active"
                    data-tab="description">{{ __('messages.description') }}</button>
            @if(count($specGroups) > 0 || count($textValues) > 0)
                <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold"
                        data-tab="specs">{{ __('messages.specifications') }}
                </button>
            @endif
            <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold"
                    data-tab="reviews">{{ __('messages.reviews') }}</button>
        </div>

        {{-- توضیحات --}}
        <div class="detail-tab-content" id="tab-description">
            <div class="neu-card p-6 leading-relaxed text-sm" style="color: var(--text-secondary);">
                {!! $product->trans('description') ?: '<p>توضیحاتی برای این محصول ثبت نشده است.</p>' !!}
            </div>
        </div>

        {{-- مشخصات فنی --}}
        @if(count($specGroups) > 0 || count($textValues) > 0)
            <div class="detail-tab-content hidden" id="tab-specs">
                <div class="neu-card p-6">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-[var(--shadow-light)]">
                        @foreach($specGroups as $group)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $group['title'] }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $group['values'] }}</td>
                            </tr>
                        @endforeach
                        @foreach($textValues as $tv)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $tv['title'] }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $tv['value'] }}</td>
                            </tr>
                        @endforeach
                        @foreach($product->properties as $prop)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $prop->trans('title') }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $prop->value }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- نظرات --}}
        <div class="detail-tab-content hidden" id="tab-reviews">

            {{-- ─── فرم ثبت نظر ─── --}}
            @auth
                <div class="review-form-wrap mb-8">
                    <h3 class="text-lg font-extrabold mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        {{ __('messages.review_write') }}
                    </h3>

                    <form action="{{ route('products.reviews.store', $product->slug) }}" method="POST" class="review-form">
                        @csrf

                        {{-- امتیاز ستاره‌ای --}}
                        <div class="review-form__rating">
                            <label class="review-form__label">{{ __('messages.review_your_rating') }}</label>
                            <div class="star-rating" id="star-rating" role="radiogroup" aria-label="امتیاز">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button"
                                            class="star-rating__star"
                                            data-value="{{ $i }}"
                                            onclick="setRating({{ $i }})"
                                            onmouseenter="previewRating({{ $i }})"
                                            onmouseleave="resetRatingPreview()"
                                            aria-label="{{ $i }} از ۵">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" id="rating-input" value="" required>
                            </div>
                            @error('rating')
                                <p class="review-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- متن نظر --}}
                        <div class="review-form__body">
                            <label class="review-form__label" for="review-comment">{{ __('messages.review_your_review') }}</label>
                            <textarea
                                id="review-comment"
                                name="comment"
                                rows="4"
                                maxlength="1000"
                                placeholder="{{ __('messages.review_placeholder') }}"
                                class="review-form__textarea"
                                required
                            >{{ old('comment') }}</textarea>
                            <div class="review-form__char-count">
                                <span id="char-count">0</span> / 1000
                            </div>
                            @error('comment')
                                <p class="review-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- دکمه ارسال --}}
                        <div class="review-form__actions">
                            <button type="submit" class="neu-btn neu-btn-primary review-form__submit" id="review-submit-btn" disabled>
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                {{ __('messages.review_submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="review-login-hint neu-flat p-6 mb-8 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3" style="color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <p class="font-bold mb-1">{{ __('messages.review_login_hint') }}</p>
                    <a href="{{ route('login') }}" class="neu-btn neu-btn-primary inline-flex items-center gap-2 mt-3 px-6 py-2 text-sm font-bold">
                        {{ __('messages.login') }}
                    </a>
                </div>
            @endauth

            {{-- ─── لیست نظرات --}}
            <div class="review-summary mb-6" style="display: {{ $product->reviews->count() > 0 ? 'block' : 'none' }};">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="review-summary__score">
                        <span class="text-4xl font-extrabold" style="color: var(--accent);">{{ number_format($product->reviews->avg('rating'), 1) }}</span>
                        <span class="text-sm" style="color: var(--text-secondary);">از ۵</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        @for($i = 5; $i >= 1; $i--)
                            @php $count = $product->reviews->where('rating', $i)->count(); @endphp
                            <div class="review-bar" style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="text-xs font-bold" style="width: 1.2rem; text-align: center; color: var(--text-secondary);">{{ $i }}</span>
                                <div class="review-bar__track" style="flex: 1; height: 6px; border-radius: 999px; background: var(--shadow-dark); overflow: hidden; min-width: 80px;">
                                    <div class="review-bar__fill" style="height: 100%; border-radius: 999px; background: var(--gold); transition: width 0.6s ease; width: {{ $product->reviews->count() > 0 ? ($count / $product->reviews->count() * 100) : 0 }}%;"></div>
                                </div>
                                <span class="text-xs" style="width: 1.5rem; color: var(--text-secondary);">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                    <span class="text-xs" style="color: var(--text-secondary);">{{ $product->reviews->count() }} {{ __('messages.reviews') }}</span>
                </div>
            </div>

            {{-- لیست نظرات --}}
            <div class="grid md:grid-cols-2 gap-5">
                @forelse($product->reviews as $review)
                    <div class="neu-card p-5 flex flex-col h-full review-card">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-10 h-10 rounded-full neu-flat flex items-center justify-center text-sm font-bold"
                                    style="color: var(--accent);">
                                    {{ mb_substr($review->user->name ?? 'U', 0, 1) }}
                                </span>
                                <div>
                                    <p class="text-sm font-bold">{{ $review->user->name ?? __('messages.user') }}</p>
                                    <p class="text-[10px]"
                                       style="color: var(--text-secondary);">{{ show_date($review->created_at, 'd F Y') }}</p>
                                </div>
                            </div>
                            <span class="text-[var(--gold)] text-sm tracking-wider">
                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                            </span>
                        </div>
                        <p class="text-sm leading-relaxed flex-1" style="color: var(--text-secondary);">
                            "{{ $review->comment }}"
                        </p>
                    </div>
                @empty
                    <div class="col-span-full neu-flat p-10 text-center">
                        <p class="text-lg mb-2" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.9 8.9 0 0 1-3.6-.7L3 21l1.9-5.3a8 8 0 0 1-.9-3.7A8.4 8.4 0 0 1 12.5 3.5 8.4 8.4 0 0 1 21 11.5z"/></svg>
                        </p>
                        <p style="color: var(--text-secondary);">{{ __('messages.no_reviews_yet') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ۴. محصولات مکمل --}}
    @if($complement->isNotEmpty())
        <section class="mb-14 reveal-on-scroll">
            <h2 class="text-xl font-extrabold mb-6">پیشنهاد مکمل</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($complement as $item)
                    <x-product-card :product="$item"/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۵. محصولات مرتبط --}}
    @if($related->isNotEmpty())
        <section class="mb-14 reveal-on-scroll">
            <h2 class="text-xl font-extrabold mb-6">{{ __('messages.related_products') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($related as $item)
                    <x-product-card :product="$item"/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۶. اسکیمای JSON-LD --}}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "{{ $product->trans('name') }}",
        "image": "{{ $mainImage }}",
        @if($product->brand)
            "brand": { "@type": "Brand", "name": "{{ $product->brand->trans('title') }}" },
        @endif
        "offers": {
            "@type": "Offer",
            "url": "{{ url()->current() }}",
            "priceCurrency": "IRR",
            "price": "{{ $product->final_price * 10 }}",
            "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
        }
    }
    </script>
@endsection

@push('scripts')
    <script>
        // ===== داده‌های واریانت از بک‌اند =====
        const PRODUCT_VARIANTS = @json($variantsData);
        const PRODUCT_MAIN_SPECS = @json($mainSpecs);

        // قالب‌بندی قیمت بر اساس زبان سایت (fa/ar → ارقام فارسی، سایر → انگلیسی)
        function formatPrice(n) {
            const useFa = window.localizeDigits && window.localizeDigits.isPersian();
            return new Intl.NumberFormat(useFa ? 'fa-IR' : 'en-US').format(n);
        }

        // ===== انتخابگر واریانت =====
        const selections = {}; // groupId -> specId
        let selectedVariant = null;

        function findVariant() {
            const ids = Object.values(selections);
            if (ids.length !== PRODUCT_MAIN_SPECS.length) return null;

            const sorted = [...ids].sort((a, b) => a - b);
            return PRODUCT_VARIANTS.find(v =>
                v.spec_ids.length === sorted.length &&
                sorted.every(id => v.spec_ids.includes(id))
            ) || null;
        }

        function updateVariantUI() {
            // به‌روزرسانی استایل انتخاب‌ها
            document.querySelectorAll('.variant-option').forEach(btn => {
                const g = btn.dataset.specGroup;
                const selected = selections[g] === parseInt(btn.dataset.specId);

                if (btn.classList.contains('color-option')) {
                    btn.classList.toggle('ring-2', selected);
                    btn.classList.toggle('ring-offset-2', selected);
                    btn.classList.toggle('scale-110', selected);
                    const check = btn.querySelector('span');
                    if (check) check.style.opacity = selected ? '1' : '0';
                } else {
                    btn.classList.toggle('active-variant', selected);
                }
            });

            // برچسب انتخاب‌شده
            document.querySelectorAll('[data-spec-group]').forEach(groupEl => {
                const g = groupEl.dataset.specGroup;
                const label = groupEl.querySelector('[data-selected-label]');
                if (label) {
                    const specId = selections[g];
                    const btn = groupEl.querySelector(`[data-spec-id="${specId}"]`);
                    label.textContent = btn ? `: ${btn.title || btn.textContent.trim()}` : '';
                }
            });

            selectedVariant = findVariant();

            // فعال/غیرفعال کردن گزینه‌های ناسازگار
            document.querySelectorAll('.variant-option').forEach(btn => {
                const g = btn.dataset.specGroup;
                const specId = parseInt(btn.dataset.specId);
                const variantIds = JSON.parse(btn.dataset.variantIds || '[]');

                // اگر برای گروه‌های دیگر انتخابی داریم، سازگاری را چک کن
                let compatible = true;
                for (const [otherGroup, otherSpec] of Object.entries(selections)) {
                    if (parseInt(otherGroup) === parseInt(g)) continue;
                    // واریانت‌هایی که هم این مقدار و هم انتخاب فعلی را دارند
                    const has = PRODUCT_VARIANTS.some(v =>
                        v.spec_ids.includes(specId) && v.spec_ids.includes(otherSpec)
                    );
                    if (!has) {
                        compatible = false;
                        break;
                    }
                }
                btn.classList.toggle('variant-disabled', !compatible);
            });

            // قیمت و موجودی
            if (selectedVariant) {
                document.getElementById('selected-variant-id').value = selectedVariant.id;
                const priceEl = document.getElementById('display-final-price');
                const oldEl = document.getElementById('display-old-price');
                const percentEl = document.getElementById('display-percent');
                if (priceEl) priceEl.textContent = formatPrice(selectedVariant.final_price);
                if (oldEl) {
                    if (selectedVariant.discount_price > 0) {
                        oldEl.textContent = formatPrice(selectedVariant.price);
                        oldEl.classList.remove('hidden');
                    } else {
                        oldEl.classList.add('hidden');
                    }
                }
                if (percentEl) {
                    if (selectedVariant.percent) {
                        percentEl.textContent = selectedVariant.percent + '٪-';
                        percentEl.classList.remove('hidden');
                    } else {
                        percentEl.classList.add('hidden');
                    }
                }

                const btn = document.getElementById('add-to-cart-btn');
                const status = document.getElementById('stock-status');
                const stockText = document.getElementById('stock-text');
                const qtyInput = document.getElementById('qty-input');

                if (selectedVariant.stock > 0) {
                    btn.disabled = false;
                    status.style.color = 'var(--success)';
                    stockText.textContent = '{{ __("messages.in_stock") }} (' + selectedVariant.stock + ' عدد)';
                    qtyInput.max = selectedVariant.stock;
                    if (parseInt(qtyInput.value) > selectedVariant.stock) qtyInput.value = selectedVariant.stock;
                } else {
                    btn.disabled = true;
                    status.style.color = 'var(--danger)';
                    stockText.textContent = '{{ __("messages.out_of_stock") }}';
                }
            }
        }

        document.querySelectorAll('.variant-option').forEach(btn => {
            btn.addEventListener('click', () => {
                const g = btn.dataset.specGroup;
                const specId = parseInt(btn.dataset.specId);
                if (selections[g] === specId) {
                    delete selections[g]; // دوباره کلیک = لغو انتخاب
                } else {
                    selections[g] = specId;
                }
                updateVariantUI();
            });
        });

        // انتخاب خودکار اگر یک گروه فقط یک مقدار دارد
        document.addEventListener('DOMContentLoaded', () => {
            PRODUCT_MAIN_SPECS.forEach(group => {
                if (group.children.length === 1) {
                    selections[group.id] = group.children[0].id;
                }
            });
            if (Object.keys(selections).length > 0) updateVariantUI();
        });

        // ===== تعداد =====
        function incrementQty(max) {
            const input = document.getElementById('qty-input');
            const val = parseInt(input.value);
            const m = selectedVariant ? selectedVariant.stock : max;
            if (val < m) input.value = val + 1;
        }

        function decrementQty() {
            const input = document.getElementById('qty-input');
            const val = parseInt(input.value);
            if (val > 1) input.value = val - 1;
        }

        // ===== تب‌ها =====
        document.querySelectorAll('.detail-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.detail-tab-content').forEach(c => c.classList.add('hidden'));
                tab.classList.add('active');
                document.getElementById('tab-' + tab.dataset.tab).classList.remove('hidden');
            });
        });

        // ===== ذره‌بین تصویر اصلی =====
        (function() {
            var wrap = document.getElementById('image-zoom-wrap');
            var lens = document.getElementById('zoom-lens');
            if (!wrap || !lens) return;

            var img = document.getElementById('main-image');
            var zoom = parseFloat(getComputedStyle(lens).getPropertyValue('--zoom')) || 2.5;

            function updateLens(e) {
                var rect = wrap.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;

                // Clamp
                x = Math.max(0, Math.min(x, rect.width));
                y = Math.max(0, Math.min(y, rect.height));

                // Position lens
                lens.style.left = x + 'px';
                lens.style.top  = y + 'px';

                // Background: the actual image at zoom level
                var bgW = rect.width * zoom;
                var bgH = rect.height * zoom;
                var bgX = -(x * zoom - lens.offsetWidth / 2);
                var bgY = -(y * zoom - lens.offsetHeight / 2);

                lens.style.backgroundImage  = 'url("' + img.src + '")';
                lens.style.backgroundSize    = bgW + 'px ' + bgH + 'px';
                lens.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
            }

            wrap.addEventListener('mousemove', updateLens);
            wrap.addEventListener('touchmove', function(e) {
                e.preventDefault();
                updateLens(e.touches[0]);
            }, { passive: false });

            // Reset on thumbnail change
            var observer = new MutationObserver(function() {
                lens.style.backgroundImage = '';
            });
            observer.observe(img, { attributes: true, attributeFilter: ['src'] });
        })();

        // ===== لایت‌باکس تمام‌صفحه =====
        (function() {
            var LIGHTBOX_IMAGES = @json($allImages);
            var currentIndex = 0;
            var lb = document.getElementById('product-lightbox');
            var lbImg = document.getElementById('lightbox-img');
            var lbCounter = document.getElementById('lightbox-counter');
            var wrap = document.getElementById('image-zoom-wrap');

            function showImage(i) {
                currentIndex = (i + LIGHTBOX_IMAGES.length) % LIGHTBOX_IMAGES.length;
                lbImg.style.opacity = '0';
                lbImg.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    lbImg.src = LIGHTBOX_IMAGES[currentIndex];
                    lbImg.alt = 'تصویر ' + (currentIndex + 1);
                    lbCounter.textContent = (currentIndex + 1) + ' / ' + LIGHTBOX_IMAGES.length;
                    lbImg.style.opacity = '1';
                    lbImg.style.transform = 'scale(1)';
                }, 150);
            }

            window.openLightbox = function(startIndex) {
                currentIndex = startIndex || 0;
                lb.classList.add('is-active');
                document.body.style.overflow = 'hidden';
                showImage(currentIndex);
            };

            window.closeLightbox = function() {
                lb.classList.remove('is-active');
                document.body.style.overflow = '';
            };

            window.lightboxNav = function(dir) {
                showImage(currentIndex + dir);
            };

            // کلیک روی تصویر اصلی → باز شدن لایت‌باکس
            if (wrap) {
                wrap.addEventListener('click', function(e) {
                    // اگه ذره‌بین فعال بود، کلیک نادیده گرفته نشه
                    if (e.target.closest('.zoom-lens')) return;
                    var curSrc = document.getElementById('main-image').src;
                    var idx = LIGHTBOX_IMAGES.findIndex(function(u) { return curSrc.includes(u) || u.includes(curSrc); });
                    openLightbox(idx >= 0 ? idx : 0);
                });
            }

            // کلیک روی thumbnail → باز شدن لایت‌باکس از همون عکس
            document.querySelectorAll('.flex.gap-3 button[onclick]').forEach(function(btn, i) {
                btn.addEventListener('click', function(e) {
                    // اجازه تغییر عکس اصلی بده، بعد لایت‌باکس رو باز کن
                    setTimeout(function() { openLightbox(i); }, 50);
                });
            });

            // کلیک روی پس‌زمینه تاریک → بسته شدن
            lb.addEventListener('click', function(e) {
                if (e.target === lb || e.target.classList.contains('lightbox__img-wrap')) {
                    closeLightbox();
                }
            });

            // کلیدهای جهت‌دار + Escape
            document.addEventListener('keydown', function(e) {
                if (!lb.classList.contains('is-active')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') lightboxNav(-1); // RTL: راست = قبلی
                if (e.key === 'ArrowLeft') lightboxNav(1);   // RTL: چپ = بعدی
            });

            // Swipe لمسی
            var touchStartX = 0;
            var touchStartY = 0;
            lb.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });
            lb.addEventListener('touchend', function(e) {
                var dx = e.changedTouches[0].clientX - touchStartX;
                var dy = e.changedTouches[0].clientY - touchStartY;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                    lightboxNav(dx > 0 ? -1 : 1); // RTL
                }
            }, { passive: true });
        })();

        // ===== فرم ثبت نظر: ستاره امتیاز + شمارنده حروف =====
        (function() {
            var input = document.getElementById('rating-input');
            var textarea = document.getElementById('review-comment');
            var charCount = document.getElementById('char-count');
            var submitBtn = document.getElementById('review-submit-btn');
            var stars = document.querySelectorAll('.star-rating__star');
            if (!input || !stars.length) return;

            var currentRating = 0;

            window.setRating = function(val) {
                currentRating = val;
                input.value = val;
                syncStars();
                checkValid();
            };

            window.previewRating = function(val) {
                stars.forEach(function(s) {
                    var v = parseInt(s.dataset.value);
                    s.classList.toggle('is-preview', v <= val && v > currentRating);
                });
            };

            window.resetRatingPreview = function() {
                stars.forEach(function(s) { s.classList.remove('is-preview'); });
            };

            function syncStars() {
                stars.forEach(function(s) {
                    s.classList.toggle('is-active', parseInt(s.dataset.value) <= currentRating);
                });
            }

            function checkValid() {
                if (!submitBtn) return;
                var ok = currentRating > 0 && textarea && textarea.value.trim().length >= 3;
                submitBtn.disabled = !ok;
            }

            if (textarea) {
                textarea.addEventListener('input', function() {
                    if (charCount) charCount.textContent = textarea.value.length;
                    checkValid();
                });
            }

            // Enter روی textarea → ارسال فرم نشه
            if (textarea) {
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) e.stopPropagation();
                });
            }
        })();
    </script>

    <style>
        .variant-option.active-variant {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
            font-weight: 700;
        }

        .variant-option.variant-disabled {
            opacity: 0.35;
            pointer-events: none;
        }

        .detail-tab.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
        }
    </style>
@endpush

@push('styles')
    <style>
        /* ═══════════════════════════════════════
           Review Form
           ═══════════════════════════════════════ */
        .review-form-wrap {
            background: var(--bg-elevated);
            border-radius: var(--radius-lg, 1rem);
            padding: 1.5rem;
            box-shadow: 6px 6px 14px var(--shadow-dark), -6px -6px 14px var(--shadow-light);
        }
        .review-form { display: flex; flex-direction: column; gap: 1.25rem; }
        .review-form__label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: var(--text-primary);
        }
        .review-form__error {
            color: var(--danger, #e53e3e);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Star rating */
        .star-rating {
            display: flex;
            gap: 0.35rem;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating__star {
            width: 2.2rem; height: 2.2rem;
            background: none; border: none; cursor: pointer;
            color: var(--shadow-dark);
            transition: color 0.15s, transform 0.15s;
            padding: 0;
        }
        .star-rating__star svg { width: 100%; height: 100%; }
        .star-rating__star:hover { transform: scale(1.15); }
        .star-rating__star.is-active {
            color: var(--gold, #f59e0b);
            transform: scale(1.1);
        }
        .star-rating__star.is-preview {
            color: var(--gold, #f59e0b);
            opacity: 0.55;
        }
        .review-form__textarea {
            width: 100%;
            resize: vertical;
            min-height: 100px;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 2px solid transparent;
            background: var(--bg);
            color: var(--text-primary);
            font-size: 0.9rem;
            line-height: 1.7;
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .review-form__textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light), 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
        }
        .review-form__textarea::placeholder { color: var(--text-secondary); opacity: 0.6; }
        .review-form__char-count {
            text-align: left;
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .review-form__actions { display: flex; justify-content: flex-start; }
        .review-form__submit {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            font-size: 0.85rem;
            transition: opacity 0.2s, transform 0.15s;
        }
        .review-form__submit:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Login hint */
        .review-login-hint { border: 2px dashed var(--shadow-dark); }

        /* Review card entrance */
        @keyframes reviewSlide {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .review-card { animation: reviewSlide 0.35s ease both; }
        .review-card:nth-child(2) { animation-delay: 0.05s; }
        .review-card:nth-child(3) { animation-delay: 0.10s; }
        .review-card:nth-child(4) { animation-delay: 0.15s; }

        /* ═══════════════════════════════════════
           Image Zoom Lens
           ═══════════════════════════════════════ */
        .image-zoom-wrap {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-lg, 1rem);
            cursor: crosshair;
        }
        .zoom-lens {
            display: none;
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.85);
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1), 0 8px 32px rgba(0,0,0,0.18);
            pointer-events: none;
            z-index: 20;
            background-repeat: no-repeat;
            transform: translate(-50%, -50%);
            transition: opacity 0.15s ease;
            opacity: 0;
            /* 2.5x zoom */
            --zoom: 2.5;
        }
        .image-zoom-wrap:hover .zoom-lens {
            display: block;
            opacity: 1;
        }
        @media (max-width: 640px) {
            .zoom-lens { width: 120px; height: 120px; --zoom: 2; }
        }

        /* ═══════════════════════════════════════
           Lightbox
           ═══════════════════════════════════════ */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.92);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .lightbox.is-active {
            display: flex;
            opacity: 1;
        }
        .lightbox__close {
            position: absolute;
            top: 1rem; right: 1rem;
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.1);
            border: none; border-radius: 50%;
            color: #fff; cursor: pointer;
            transition: background 0.2s;
            z-index: 10;
        }
        .lightbox__close:hover { background: rgba(255,255,255,0.25); }
        .lightbox__close svg { width: 22px; height: 22px; }
        .lightbox__img-wrap {
            display: flex; align-items: center; justify-content: center;
            width: 100%; height: 100%;
            padding: 60px 60px;
            user-select: none;
        }
        .lightbox__img {
            max-width: 100%; max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1), opacity 0.25s ease;
            transform: scale(0.95);
            opacity: 0;
        }
        .lightbox.is-active .lightbox__img {
            transform: scale(1);
            opacity: 1;
        }
        .lightbox__arrow {
            position: absolute;
            top: 50%; transform: translateY(-50%);
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.08);
            border: none; border-radius: 50%;
            color: #fff; cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            z-index: 10;
        }
        .lightbox__arrow:hover { background: rgba(255,255,255,0.2); }
        .lightbox__arrow svg { width: 26px; height: 26px; }
        .lightbox__arrow--prev { left: 1rem; }
        .lightbox__arrow--next { right: 1rem; }
        .lightbox__counter {
            position: absolute;
            bottom: 1.25rem; left: 50%; transform: translateX(-50%);
            font-size: 0.85rem; font-weight: 600;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.05em;
        }
        @media (max-width: 640px) {
            .lightbox__img-wrap { padding: 50px 12px 60px; }
            .lightbox__arrow { width: 38px; height: 38px; }
            .lightbox__arrow--prev { left: 0.5rem; }
            .lightbox__arrow--next { right: 0.5rem; }
            .lightbox__arrow svg { width: 20px; height: 20px; }
        }

        /* ═══════════════════════════════════════
           Share Section — Pill + Overflow
           ═══════════════════════════════════════ */
        .share-section { margin-bottom: 0; }

        .share-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        /* ── Pill base ── */
        .share-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: var(--bg-elevated);
            color: var(--text-secondary);
            box-shadow: 3px 3px 7px var(--shadow-dark), -3px -3px 7px var(--shadow-light);
            transition: all 0.25s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        .share-pill::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.25s ease;
            z-index: 0;
        }
        .share-pill:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 4px 5px 14px var(--shadow-dark), -4px -5px 14px var(--shadow-light);
        }
        .share-pill:active {
            transform: translateY(0) scale(0.96);
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            transition-duration: 0.1s;
        }
        .share-pill__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .share-pill__icon svg { width: 100%; height: 100%; }
        .share-pill__label {
            white-space: nowrap;
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        /* ── Platform colors ── */
        .share-pill[data-platform="telegram"] { color: #0088cc; }
        .share-pill[data-platform="telegram"]::before { background: linear-gradient(135deg, #0088cc 0%, #229ed9 100%); }
        .share-pill[data-platform="telegram"]:hover { color: #fff; }
        .share-pill[data-platform="telegram"]:hover::before { opacity: 1; }

        .share-pill[data-platform="whatsapp"] { color: #25D366; }
        .share-pill[data-platform="whatsapp"]::before { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
        .share-pill[data-platform="whatsapp"]:hover { color: #fff; }
        .share-pill[data-platform="whatsapp"]:hover::before { opacity: 1; }

        /* Native share (همه اپ‌ها) */
        .share-pill--native { color: var(--accent); }
        .share-pill--native::before { background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 70%, #000) 100%); }
        .share-pill--native:hover { color: #fff; }
        .share-pill--native:hover::before { opacity: 1; }
        .share-pill--native.share-pill--copied { color: var(--success) !important; }
        .share-pill--native.share-pill--copied::before { background: linear-gradient(135deg, var(--success) 0%, color-mix(in srgb, var(--success) 70%, #000) 100%) !important; opacity: 1 !important; }

        /* Toggle (بیشتر/کمتر) */
        .share-pill--toggle { color: var(--text-secondary); border: 1.5px dashed var(--text-secondary); background: transparent; box-shadow: none; }
        .share-pill--toggle::before { background: linear-gradient(135deg, var(--text-secondary) 0%, color-mix(in srgb, var(--text-secondary) 70%, #000) 100%); }
        .share-pill--toggle:hover { color: #fff; border-color: transparent; }
        .share-pill--toggle:hover::before { opacity: 1; }
        .share-pill--toggle .share-pill__icon svg { transition: transform 0.25s ease; }

        /* Copy link */
        .share-pill--copy { color: var(--text-secondary); }
        .share-pill--copy::before { background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 70%, #000) 100%); }
        .share-pill--copy:hover { color: #fff !important; }
        .share-pill--copy:hover::before { opacity: 1; }
        .share-pill--copied { color: var(--success) !important; }
        .share-pill--copied::before { background: linear-gradient(135deg, var(--success) 0%, color-mix(in srgb, var(--success) 70%, #000) 100%) !important; opacity: 1 !important; }
        .share-pill--copied .share-pill__icon,
        .share-pill--copied .share-pill__label { position: relative; color: #fff !important; }

        /* Overflow platform colors */
        [data-platform="bale"] { color: #0d7c9d; }
        [data-platform="bale"]::before { background: linear-gradient(135deg, #0d7c9d 0%, #00a693 100%); }
        [data-platform="bale"]:hover { color: #fff; }
        [data-platform="bale"]:hover::before { opacity: 1; }
        [data-platform="eitaa"] { color: #e84393; }
        [data-platform="eitaa"]::before { background: linear-gradient(135deg, #e84393 0%, #a855f7 100%); }
        [data-platform="eitaa"]:hover { color: #fff; }
        [data-platform="eitaa"]:hover::before { opacity: 1; }
        [data-platform="instagram-story"] { color: #f77737; }
        [data-platform="instagram-story"]::before { background: linear-gradient(135deg, #f77737 0%, #c13584 50%, #833ab4 100%); }
        [data-platform="instagram-story"]:hover { color: #fff; }
        [data-platform="instagram-story"]:hover::before { opacity: 1; }
        [data-platform="instagram-dm"] { color: #c13584; }
        [data-platform="instagram-dm"]::before { background: linear-gradient(135deg, #c13584 0%, #833ab4 100%); }
        [data-platform="instagram-dm"]:hover { color: #fff; }
        [data-platform="instagram-dm"]:hover::before { opacity: 1; }

        /* ── Overflow row ── */
        .share-overflow {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.4rem;
        }
        .share-overflow.hidden { display: none; }
        .share-overflow .share-pill {
            opacity: 0;
            transform: translateY(6px) scale(0.85);
        }
        .share-overflow:not(.hidden) .share-pill {
            animation: sharePop 0.3s ease both;
        }
        .share-overflow:not(.hidden) .share-pill:nth-child(1) { animation-delay: 0.00s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(2) { animation-delay: 0.05s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(3) { animation-delay: 0.10s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(4) { animation-delay: 0.15s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(5) { animation-delay: 0.20s; }

        /* ── Stagger entrance ── */
        @keyframes sharePop {
            from { opacity: 0; transform: translateY(6px) scale(0.85); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .share-pills .share-pill:nth-child(1) { animation: sharePop 0.3s 0.00s both; }
        .share-pills .share-pill:nth-child(2) { animation: sharePop 0.3s 0.05s both; }
        .share-pills .share-pill:nth-child(3) { animation: sharePop 0.3s 0.10s both; }
        .share-pills .share-pill:nth-child(4) { animation: sharePop 0.3s 0.15s both; }
        .share-pills .share-pill:nth-child(5) { animation: sharePop 0.3s 0.20s both; }
        .share-pills .share-pill:nth-child(6) { animation: sharePop 0.3s 0.25s both; }
        .share-pills .share-pill:nth-child(7) { animation: sharePop 0.3s 0.30s both; }

        /* ── Ripple pulse ── */
        .share-pill::after {
            content: '';
            position: absolute;
            width: 0; height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease, opacity 0.4s ease;
            opacity: 0;
            z-index: 0;
        }
        .share-pill:active::after {
            width: 200%; height: 200%;
            opacity: 1;
            transition: 0s;
        }
    </style>
@endpush
