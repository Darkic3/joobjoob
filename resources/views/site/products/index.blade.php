@extends('layouts.site.app')

@section('title', __('messages.products'))

@section('content')
    {{-- ═══ Breadcrumb ═══ --}}
    <x-breadcrumbs />

    {{-- ═══ هدر صفحه ═══ --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-eco-ink mb-1">{{ __('messages.products') }}</h1>
            <p class="text-sm text-eco-ink-2">
                <span class="font-bold text-eco-clay">{{ number_format($products->total()) }}</span>
                {{ __('messages.products') }}
                @if (!empty($filters['q']))
                    — «{{ $filters['q'] }}»
                @endif
            </p>
        </div>
        <button type="button" onclick="document.getElementById('filters-panel').classList.toggle('hidden-mobile')"
            class="lg:hidden flex items-center gap-2 px-4 py-2.5 bg-eco-surface rounded-xl shadow-sm border border-eco-line text-sm font-medium text-eco-ink-2 hover:bg-eco-canvas-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
            {{ __('messages.filter') }}
        </button>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        {{-- ═══ سایدبار فیلتر ═══ --}}
        <aside id="filters-panel" class="hidden-mobile lg:block h-fit lg:sticky lg:top-24">
            <div class="bg-eco-surface rounded-2xl shadow-sm border border-eco-line p-5">

                {{-- هدر فیلتر --}}
                <div class="flex items-center justify-between mb-5 pb-4 border-b border-eco-line">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-eco-clay/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-eco-clay" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm text-eco-ink">{{ __('messages.filter') }}</h3>
                    </div>
                    <a href="{{ localizedRoute('products.index') }}"
                        class="text-xs font-medium text-eco-danger hover:text-eco-danger px-3 py-1.5 rounded-lg hover:bg-eco-danger/10 transition-colors">
                        {{ __('messages.reset') }}
                    </a>
                </div>

                <form method="GET" action="{{ localizedRoute('products.index') }}" id="filter-form">
                    @if (!empty($filters['q']))
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif

                    {{-- ─── جستجو ─── --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-eco-ink-2 mb-2">{{ __('messages.search') }}</label>
                        <div class="relative">
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                                placeholder="{{ __('messages.search_placeholder') }}"
                                class="w-full ps-10 pe-4 py-2.5 text-sm bg-eco-canvas-2 border border-eco-line rounded-xl focus:ring-2 focus:ring-eco-clay focus:border-eco-clay outline-none transition-all">
                            <svg class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-eco-ink-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path stroke-linecap="round" d="m21 21-4.35-4.35" />
                            </svg>
                        </div>
                    </div>

                    {{-- ─── دسته‌بندی ─── --}}
                    <div class="mb-5">
                        <label
                            class="block text-xs font-semibold text-eco-ink-2 mb-2">{{ __('messages.categories') }}</label>
                        <div class="space-y-1 max-h-44 overflow-y-auto custom-scrollbar pe-1">
                            @foreach ($categories as $cat)
                                <label
                                    class="flex items-center gap-2.5 cursor-pointer group px-2 py-2 rounded-lg hover:bg-eco-canvas-2 transition-colors">
                                    <input type="checkbox" name="category[]" value="{{ $cat->slug }}"
                                        {{ in_array($cat->slug, $selectedCategories, true) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-eco-line text-eco-clay focus:ring-eco-clay">
                                    <span
                                        class="text-sm text-eco-ink-2 group-hover:text-eco-clay transition-colors">{{ $cat->trans('name') }}</span>
                                    <span class="text-[11px] text-eco-ink-3 ms-auto bg-eco-canvas-2 px-2 py-0.5 rounded-full">
                                        {{ $cat->products_count }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ─── قیمت ─── --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-eco-ink-2 mb-2">{{ __('messages.price') }}
                            (تومان)</label>
                        <div class="space-y-2 p-3 rounded-2xl bg-eco-canvas-2/70 border border-eco-line">
                            <div class="flex items-center gap-2">
                                <label for="filter-min-price-display"
                                    class="shrink-0 text-xs font-semibold text-eco-ink-2">حداقل</label>
                                <div class="relative flex-1 min-w-0">
                                    {{-- عدد خام بدون فرمت برای بک‌اند --}}
                                    <input type="hidden" name="min_price" id="filter-min-price"
                                        value="{{ $filters['min_price'] ?? '' }}">
                                    {{-- نمایش فرمت‌شده (۳ رقم ۳ رقم با ,) فقط برای کاربر --}}
                                    <input type="text" inputmode="numeric" id="filter-min-price-display" placeholder="0"
                                        autocomplete="off"
                                        class="w-full min-w-0 pe-11 ps-3 py-2 text-sm bg-eco-surface border border-eco-line rounded-xl focus:ring-2 focus:ring-eco-clay focus:border-eco-clay outline-none transition-all text-start mono"
                                        dir="ltr">
                                    <span
                                        class="absolute end-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-eco-ink-3 pointer-events-none">تومان</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="filter-max-price-display"
                                    class="shrink-0 text-xs font-semibold text-eco-ink-2">حداکثر</label>
                                <div class="relative flex-1 min-w-0">
                                    {{-- عدد خام بدون فرمت برای بک‌اند --}}
                                    <input type="hidden" name="max_price" id="filter-max-price"
                                        value="{{ $filters['max_price'] ?? '' }}">
                                    {{-- نمایش فرمت‌شده (۳ رقم ۳ رقم با ,) فقط برای کاربر --}}
                                    <input type="text" inputmode="numeric" id="filter-max-price-display" placeholder="0"
                                        autocomplete="off"
                                        class="w-full min-w-0 pe-11 ps-3 py-2 text-sm bg-eco-surface border border-eco-line rounded-xl focus:ring-2 focus:ring-eco-clay focus:border-eco-clay outline-none transition-all text-start mono"
                                        dir="ltr">
                                    <span
                                        class="absolute end-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-eco-ink-3 pointer-events-none">تومان</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── برند ─── --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-eco-ink-2 mb-2">{{ __('messages.brand') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="brand" value="" class="hidden"
                                    {{ !$selectedBrand ? 'checked' : '' }}>
                                <span
                                    class="inline-block px-3 py-1.5 text-[11px] font-medium rounded-full border transition-all {{ !$selectedBrand ? 'bg-eco-clay text-white border-eco-clay' : 'bg-eco-canvas-2 text-eco-ink-2 border-eco-line hover:border-eco-clay' }}">
                                    {{ __('messages.all') }}
                                </span>
                            </label>
                            @foreach ($brands as $brand)
                                <label class="cursor-pointer">
                                    <input type="radio" name="brand" value="{{ $brand->url }}" class="hidden"
                                        {{ $selectedBrand === $brand->url ? 'checked' : '' }}>
                                    <span
                                        class="inline-block px-3 py-1.5 text-[11px] font-medium rounded-full border transition-all {{ $selectedBrand === $brand->url ? 'bg-eco-clay text-white border-eco-clay' : 'bg-eco-canvas-2 text-eco-ink-2 border-eco-line hover:border-eco-clay' }}">
                                        {{ $brand->trans('title') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- دکمه اعمال --}}
                    <button type="submit"
                        class="eco-btn eco-btn--clay eco-btn--block w-full py-3 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        {{ __('messages.apply_filter') }}
                    </button>
                </form>
            </div>
        </aside>

        {{-- ═══ لیست محصولات ═══ --}}
        <div class="lg:col-span-3">

            {{-- نوار مرتب‌سازی --}}
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <form method="GET" action="{{ localizedRoute('products.index') }}" id="sort-form" class="contents">
                    @foreach ($filters as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <input type="hidden" name="sort" id="sort-input" value="{{ $selectedSort }}">

                    <div class="flex items-center gap-1 p-1 bg-eco-canvas-2 rounded-xl">
                        <button type="button" data-sort="default"
                            class="sort-btn px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all {{ $selectedSort === 'default' ? 'bg-eco-surface text-eco-clay shadow-sm' : 'text-eco-ink-2 hover:text-eco-ink-2' }}">
                            {{ __('messages.relevance') }}
                        </button>
                        <button type="button" data-sort="price_asc"
                            class="sort-btn px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all {{ $selectedSort === 'price_asc' ? 'bg-eco-surface text-eco-clay shadow-sm' : 'text-eco-ink-2 hover:text-eco-ink-2' }}">
                            {{ __('messages.price') }} ↑
                        </button>
                        <button type="button" data-sort="price_desc"
                            class="sort-btn px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all {{ $selectedSort === 'price_desc' ? 'bg-eco-surface text-eco-clay shadow-sm' : 'text-eco-ink-2 hover:text-eco-ink-2' }}">
                            {{ __('messages.price') }} ↓
                        </button>
                        <button type="button" data-sort="newest"
                            class="sort-btn px-3 py-1.5 text-[11px] font-semibold rounded-lg transition-all {{ $selectedSort === 'newest' ? 'bg-eco-surface text-eco-clay shadow-sm' : 'text-eco-ink-2 hover:text-eco-ink-2' }}">
                            {{ __('messages.newest') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- گرید محصولات --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full text-center py-20">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-eco-canvas-2 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-eco-ink-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <p class="font-bold text-sm text-eco-ink mb-1">{{ __('messages.no_products_found') }}</p>
                        <p class="text-sm text-eco-ink-2">{{ __('messages.cart_empty_desc') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- ═══ صفحه‌بندی ═══ --}}
            @if ($products->hasPages())
                <div class="mt-10">
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                        {{-- قبلی --}}
                        @if ($products->onFirstPage())
                            <span class="w-10 h-10 flex items-center justify-center text-eco-ink-3 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}"
                                class="w-10 h-10 flex items-center justify-center text-eco-ink-2 hover:text-eco-clay hover:bg-eco-clay/10 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </a>
                        @endif

                        {{-- شماره صفحات --}}
                        @php
                            $currentPage = $products->currentPage();
                            $lastPage = $products->lastPage();
                            $pages = [];
                            if ($lastPage <= 7) {
                                $pages = range(1, $lastPage);
                            } else {
                                $pages[] = 1;
                                if ($currentPage > 3) {
                                    $pages[] = '...';
                                }
                                for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                                    $pages[] = $i;
                                }
                                if ($currentPage < $lastPage - 2) {
                                    $pages[] = '...';
                                }
                                $pages[] = $lastPage;
                            }
                        @endphp

                        @foreach ($pages as $page)
                            @if ($page === '...')
                                <span class="w-10 h-10 flex items-center justify-center text-eco-ink-3">…</span>
                            @elseif($page == $currentPage)
                                <span
                                    class="w-10 h-10 flex items-center justify-center bg-eco-clay text-white text-sm font-bold rounded-xl shadow-eco-xs">{{ $page }}</span>
                            @else
                                <a href="{{ $products->url($page) }}"
                                    class="w-10 h-10 flex items-center justify-center text-eco-ink-2 hover:text-eco-clay hover:bg-eco-clay/10 text-sm font-medium rounded-xl transition-all">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- بعدی --}}
                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}"
                                class="w-10 h-10 flex items-center justify-center text-eco-ink-2 hover:text-eco-clay hover:bg-eco-clay/10 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        @else
                            <span class="w-10 h-10 flex items-center justify-center text-eco-ink-3 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Mobile filter panel */
        @media (max-width: 1023px) {
            #filters-panel.hidden-mobile {
                display: none;
            }
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.sort-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('sort-input').value = btn.getAttribute('data-sort');
                document.getElementById('sort-form').submit();
            });
        });

        // ─── فرمت نمایشی قیمت (۳ رقم ۳ رقم با ,) — فقط ظاهر؛ عدد خام در اینپوت مخفی می‌ماند ───
        (function() {
            function toLatinDigits(s) {
                return String(s || '')
                    .replace(/[۰-۹]/g, function(d) {
                        return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
                    })
                    .replace(/[٠-٩]/g, function(d) {
                        return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
                    });
            }

            function groupThousands(digits) {
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            function bindPriceInput(displayId, hiddenId) {
                var display = document.getElementById(displayId);
                var hidden = document.getElementById(hiddenId);
                if (!display || !hidden) return;

                // مقدار اولیه از سرور (عدد خام) → نمایش فرمت‌شده
                var initial = toLatinDigits(hidden.value).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                hidden.value = initial;
                display.value = initial ? groupThousands(initial) : '';

                display.addEventListener('input', function() {
                    var digits = toLatinDigits(display.value).replace(/\D/g, '').replace(/^0+(?=\d)/, '').slice(
                        0, 15);
                    hidden.value = digits;
                    display.value = digits ? groupThousands(digits) : '';
                });
                // اطمینان لحظه سابمیت: hidden همیشه عدد خام و تمیز است
                var form = display.closest('form');
                if (form && !form.dataset.priceBound) {
                    form.dataset.priceBound = '1';
                    form.addEventListener('submit', function() {
                        ['filter-min-price', 'filter-max-price'].forEach(function(id) {
                            var h = document.getElementById(id);
                            if (h) h.value = toLatinDigits(h.value).replace(/\D/g, '').replace(
                                /^0+(?=\d)/, '');
                        });
                    });
                }
            }
            bindPriceInput('filter-min-price-display', 'filter-min-price');
            bindPriceInput('filter-max-price-display', 'filter-max-price');
        })();
    </script>
@endpush
