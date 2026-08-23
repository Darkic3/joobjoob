@extends('layouts.site.app')

@section('title', __('messages.products'))

@section('content')
    @include('site.partials.breadcrumb', ['items' => [
        ['label' => __('messages.products')],
    ]])

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-extrabold mb-1">{{ __('messages.products') }}</h1>
            <p class="text-xs" style="color: var(--text-secondary);">
                <span class="mono font-bold" style="color: var(--accent);">{{ number_format($products->total()) }}</span>
                {{ __('messages.products') }}
                @if(!empty($filters['q']))
                    — «{{ $filters['q'] }}»
                @endif
            </p>
        </div>
        <button type="button" onclick="document.getElementById('filters-panel').classList.toggle('hidden-mobile')"
                class="neu-btn px-4 py-2.5 text-xs font-bold flex items-center gap-2 lg:hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
            {{ __('messages.filter') }}
        </button>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- ═══ سایدبار فیلتر ═══ --}}
        <aside id="filters-panel" class="hidden-mobile lg:block h-fit lg:sticky lg:top-24">
            <div class="neu-card p-5">
                {{-- هدر فیلتر --}}
                <div class="flex items-center justify-between mb-5 pb-4" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 8%, transparent);">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                            <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        </span>
                        <h3 class="font-bold text-sm">{{ __('messages.filter') }}</h3>
                    </div>
                    <a href="{{ localizedRoute('products.index') }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all hover:opacity-70"
                       style="color: var(--danger); background: color-mix(in srgb, var(--danger) 8%, transparent);">
                        {{ __('messages.reset') }}
                    </a>
                </div>

                <form method="GET" action="{{ localizedRoute('products.index') }}" id="filter-form">
                    @if(!empty($filters['q'])) <input type="hidden" name="q" value="{{ $filters['q'] }}"> @endif

                    {{-- ─── جستجوی پیشرفته ─── --}}
                    <div class="mb-5">
                        <div class="neu-flat-sm rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                                <span class="text-xs font-bold" style="color: var(--text-secondary);">{{ __('messages.search') }}</span>
                            </div>
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                                   placeholder="{{ __('messages.search_placeholder') }}"
                                   class="neu-input w-full text-xs py-2 px-3">
                        </div>
                    </div>

                    {{-- ─── دسته‌بندی ─── --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold mb-3 flex items-center gap-1.5" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                            {{ __('messages.categories') }}
                        </p>
                        <div class="space-y-1.5 text-sm max-h-44 overflow-y-auto custom-scrollbar pe-1">
                            @foreach($categories as $cat)
                                <label class="flex items-center gap-2.5 cursor-pointer group px-2 py-1.5 rounded-lg transition-all hover:bg-[color-mix(in_srgb,var(--accent)_5%,transparent)]">
                                    <input type="checkbox" name="category[]" value="{{ $cat->slug }}"
                                           {{ in_array($cat->slug, $selectedCategories, true) ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)] shrink-0">
                                    <span class="group-hover:text-[var(--accent)] transition-colors text-xs">{{ $cat->trans('name') }}</span>
                                    <span class="mono text-[10px] ms-auto shrink-0 px-1.5 py-0.5 rounded-md"
                                          style="background: color-mix(in srgb, var(--text-primary) 6%, transparent); color: var(--text-secondary);">
                                        {{ $cat->products_count }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- ─── قیمت ─── --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold mb-3 flex items-center gap-1.5" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659 1.171-1.171a2.25 2.25 0 0 1 1.591-.666h3.375a2.25 2.25 0 0 1 2.25 2.25v.37a2.25 2.25 0 0 1-2.25 2.25H18.75"/></svg>
                            {{ __('messages.price') }} <span class="text-[10px] font-normal">(تومان)</span>
                        </p>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="number" name="min_price" placeholder="حداقل"
                                       value="{{ $filters['min_price'] ?? '' }}"
                                       class="neu-input w-full text-xs py-2 px-3 text-center" dir="ltr">
                            </div>
                            <span class="flex items-center text-xs" style="color: var(--text-secondary);">—</span>
                            <div class="flex-1 relative">
                                <input type="number" name="max_price" placeholder="حداکثر"
                                       value="{{ $filters['max_price'] ?? '' }}"
                                       class="neu-input w-full text-xs py-2 px-3 text-center" dir="ltr">
                            </div>
                        </div>
                    </div>

                    {{-- ─── برند ─── --}}
                    <div class="mb-5">
                        <p class="text-xs font-bold mb-3 flex items-center gap-1.5" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
                            {{ __('messages.brand') }}
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="brand" value="" class="hidden" {{ !$selectedBrand ? 'checked' : '' }}>
                                <span class="chip px-3 py-1.5 text-[11px] block transition-all {{ !$selectedBrand ? 'active' : '' }}">
                                    {{ __('messages.all') }}
                                </span>
                            </label>
                            @foreach($brands as $brand)
                                <label class="cursor-pointer">
                                    <input type="radio" name="brand" value="{{ $brand->url }}" class="hidden" {{ $selectedBrand === $brand->url ? 'checked' : '' }}>
                                    <span class="chip px-3 py-1.5 text-[11px] block transition-all {{ $selectedBrand === $brand->url ? 'active' : '' }}">
                                        {{ $brand->trans('title') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- دکمه اعمال --}}
                    <button type="submit" class="neu-btn neu-btn-primary w-full text-sm py-3 font-bold flex items-center justify-center gap-2 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        {{ __('messages.apply_filter') }}
                    </button>
                </form>
            </div>
        </aside>

        {{-- ═══ لیست محصولات ═══ --}}
        <div class="lg:col-span-3">
            {{-- نوار مرتب‌سازی --}}
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <form method="GET" action="{{ localizedRoute('products.index') }}" id="sort-form" class="contents">
                    @foreach($filters as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <input type="hidden" name="sort" id="sort-input" value="{{ $selectedSort }}">

                    <div class="flex items-center gap-1.5 p-1 neu-flat-sm rounded-2xl">
                        <button type="button" data-sort="default"
                                class="sort-btn px-3 py-1.5 text-[11px] font-bold rounded-xl transition-all {{ $selectedSort === 'default' ? 'active' : '' }}">
                            <svg class="w-3 h-3 inline-block ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Z"/></svg>
                            {{ __('messages.relevance') }}
                        </button>
                        <button type="button" data-sort="price_asc"
                                class="sort-btn px-3 py-1.5 text-[11px] font-bold rounded-xl transition-all {{ $selectedSort === 'price_asc' ? 'active' : '' }}">
                            <svg class="w-3 h-3 inline-block ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                            {{ __('messages.price') }} ↑
                        </button>
                        <button type="button" data-sort="price_desc"
                                class="sort-btn px-3 py-1.5 text-[11px] font-bold rounded-xl transition-all {{ $selectedSort === 'price_desc' ? 'active' : '' }}">
                            <svg class="w-3 h-3 inline-block ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/></svg>
                            {{ __('messages.price') }} ↓
                        </button>
                        <button type="button" data-sort="newest"
                                class="sort-btn px-3 py-1.5 text-[11px] font-bold rounded-xl transition-all {{ $selectedSort === 'newest' ? 'active' : '' }}">
                            <svg class="w-3 h-3 inline-block ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('messages.newest') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- گرید محصولات --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                @forelse($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full text-center py-20 neu-card">
                        <div class="w-16 h-16 mx-auto rounded-2xl grid place-items-center mb-4"
                             style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                            <svg class="w-7 h-7" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <p class="font-bold text-sm mb-1">{{ __('messages.no_products_found') }}</p>
                        <p class="text-xs" style="color: var(--text-secondary);">{{ __('messages.cart_empty_desc') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- ═══ صفحه‌بندی ═══ --}}
            @if($products->hasPages())
                <div class="mt-10">
                    <div class="neu-card p-3 flex items-center justify-center gap-1.5 flex-wrap">
                        {{-- قبلی --}}
                        @if($products->onFirstPage())
                            <span class="neu-page-item w-9 h-9 text-sm flex items-center justify-center opacity-40 cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}" class="neu-page-item w-9 h-9 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
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
                                if ($currentPage > 3) $pages[] = '...';
                                for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                                    $pages[] = $i;
                                }
                                if ($currentPage < $lastPage - 2) $pages[] = '...';
                                $pages[] = $lastPage;
                            }
                        @endphp

                        @foreach($pages as $page)
                            @if($page === '...')
                                <span class="w-6 h-9 flex items-center justify-center text-xs" style="color: var(--text-secondary);">…</span>
                            @elseif($page == $currentPage)
                                <span class="neu-page-item active w-9 h-9 text-sm mono flex items-center justify-center font-bold">{{ $page }}</span>
                            @else
                                <a href="{{ $products->url($page) }}" class="neu-page-item w-9 h-9 text-sm mono flex items-center justify-center hover:text-[var(--accent)] transition-all">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- بعدی --}}
                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="neu-page-item w-9 h-9 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        @else
                            <span class="neu-page-item w-9 h-9 text-sm flex items-center justify-center opacity-40 cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
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
        /* ═══════════════════════════════════════
           Sort Buttons — Neumorphic Pills
           ═══════════════════════════════════════ */
        .sort-btn {
            color: var(--text-secondary);
            background: transparent;
        }
        .sort-btn:hover {
            color: var(--text-primary);
            background: color-mix(in srgb, var(--accent) 5%, transparent);
        }
        .sort-btn.active {
            color: var(--accent-contrast, #fff);
            background: var(--accent);
            box-shadow: 0 2px 8px color-mix(in srgb, var(--accent) 35%, transparent);
        }

        /* ═══════════════════════════════════════
           Brand Chips
           ═══════════════════════════════════════ */
        .chip {
            background: var(--bg-elevated);
            box-shadow: 2px 2px 5px var(--shadow-dark), -2px -2px 5px var(--shadow-light);
            border-radius: 999px;
            text-align: center;
            transition: all 0.2s ease;
        }
        .chip:hover {
            box-shadow: 1px 1px 3px var(--shadow-dark), -1px -1px 3px var(--shadow-light);
        }
        .chip.active {
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            color: var(--accent);
            font-weight: 700;
        }

        /* ═══════════════════════════════════════
           Pagination
           ═══════════════════════════════════════ */
        .neu-page-item {
            background: var(--bg-elevated);
            box-shadow: 2px 2px 5px var(--shadow-dark), -2px -2px 5px var(--shadow-light);
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .neu-page-item:hover:not(.active):not([class*="opacity"]) {
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
        }
        .neu-page-item.active {
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 6%, var(--bg-elevated));
        }

        /* ═══════════════════════════════════════
           Mobile Filter Panel
           ═══════════════════════════════════════ */
        @media (max-width: 1023px) {
            #filters-panel.hidden-mobile {
                display: none;
            }
        }

        /* ═══════════════════════════════════════
           Custom Scrollbar
           ═══════════════════════════════════════ */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--shadow-dark); border-radius: 4px; }

        /* ═══════════════════════════════════════
           Sort Form — buttons trigger hidden input
           ═══════════════════════════════════════ */
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
    </script>
@endpush
