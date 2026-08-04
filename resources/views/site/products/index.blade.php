@extends('layouts.site.app')

@section('title', __('messages.products'))

@section('content')
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ route('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <span style="color: var(--text-primary);">{{ __('messages.products') }}</span>
    </p>

    <div class="flex items-center justify-between mb-5 lg:hidden">
        <h1 class="font-extrabold text-lg">{{ __('messages.products') }}</h1>
        <button type="button" onclick="document.getElementById('filters-panel').classList.toggle('hidden-mobile')"
                class="neu-btn px-4 py-2 text-xs font-bold">{{ __('messages.filter') }} ⚙</button>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- سایدبار فیلتر --}}
        <aside id="filters-panel" class="hidden-mobile lg:block neu-flat p-5 h-fit lg:sticky lg:top-24">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-extrabold text-sm">{{ __('messages.filter') }}</h3>
                <a href="{{ route('products.index') }}" class="text-xs font-semibold hover:text-[var(--accent)] transition-colors">{{ __('messages.reset') }}</a>
            </div>

            <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                {{-- حفظ پارامتر جستجو در فرم --}}
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                {{-- فیلتر دسته‌بندی --}}
                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.categories') }}</p>
                    <div class="space-y-2 text-sm max-h-48 overflow-y-auto custom-scrollbar">
                        @foreach($categories as $cat)
                            @php $isChecked = in_array($cat->slug, (array) request('category', [])); @endphp
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="category[]" value="{{ $cat->slug }}" {{ $isChecked ? 'checked' : '' }}
                                class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                                <span class="group-hover:text-[var(--accent)] transition-colors">{{ $cat->trans('name') }}</span>
                                <span class="mono text-[11px] ms-auto" style="color: var(--text-secondary);">({{ $cat->products_count }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- فیلتر قیمت --}}
                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.price') }} (تومان)</p>
                    <div class="flex gap-2 mb-2">
                        <input type="number" name="min_price" placeholder="از" value="{{ request('min_price') }}"
                               class="neu-input w-1/2 text-xs py-2 px-2 text-center">
                        <input type="number" name="max_price" placeholder="تا" value="{{ request('max_price') }}"
                               class="neu-input w-1/2 text-xs py-2 px-2 text-center">
                    </div>
                </div>

                {{-- فیلتر برند --}}
                <div class="mb-6">
                    <p class="text-xs font-bold mb-3" style="color: var(--text-secondary);">{{ __('messages.brand') }}</p>
                    <div class="flex flex-wrap gap-2">
                        {{-- گزینه "همه" برای حذف فیلتر برند --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="brand" value="" class="hidden" {{ !request('brand') ? 'checked' : '' }}>
                            <span class="chip px-3 py-1.5 text-[11px] block transition-all {{ !request('brand') ? 'active' : '' }}">
                                {{ __('messages.all') }}
                            </span>
                        </label>

                        @foreach($brands as $brand)
                            @php $isActive = request('brand') === $brand->url; @endphp
                            <label class="cursor-pointer">
                                <input type="radio" name="brand" value="{{ $brand->url }}" class="hidden" {{ $isActive ? 'checked' : '' }}>
                                <span class="chip px-3 py-1.5 text-[11px] block transition-all {{ $isActive ? 'active' : '' }}">
                                    {{ $brand->trans('title') }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="neu-btn neu-btn-primary w-full text-sm py-2.5 font-bold">
                    {{ __('messages.apply_filter') }}
                </button>
            </form>
        </aside>

        {{-- لیست محصولات --}}
        <div class="lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <p class="text-xs" style="color: var(--text-secondary);">
                    <span class="mono font-bold" style="color: var(--text-primary);">{{ number_format($products->total()) }}</span>
                    {{ __('messages.products') }}
                </p>

                <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
                    {{-- حفظ فیلترهای فعلی در فرم مرتب‌سازی --}}
                    @foreach(request()->except('sort', 'page') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <select name="sort" onchange="this.form.submit()" class="neu-input text-xs py-2 px-3 cursor-pointer">
                        <option value="default" {{ request('sort') == 'default' || !request('sort') ? 'selected' : '' }}>{{ __('messages.relevance') }}</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('messages.price') }} ↑</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('messages.price') }} ↓</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>{{ __('messages.newest') }}</option>
                    </select>
                </form>
            </div>

            {{-- گرید محصولات --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                @forelse($products as $product)
                    {{-- فرض بر این است که کامپوننت x-product-card مدل Product را می‌پذیرد --}}
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full text-center py-20 neu-flat">
                        <p class="text-gray-500">{{ __('messages.no_products_found') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination نئومورفیک سفارشی --}}
            @if($products->hasPages())
                <div class="flex items-center justify-center gap-2 mt-10">
                    {{-- دکمه قبلی --}}
                    @if($products->onFirstPage())
                        <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">›</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">›</a>
                    @endif

                    {{-- شماره صفحات --}}
                    @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if($page == $products->currentPage())
                            <span class="neu-page-item active w-10 h-10 text-sm mono flex items-center justify-center font-bold text-[var(--accent)]">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="neu-page-item w-10 h-10 text-sm mono flex items-center justify-center hover:text-[var(--accent)] transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- دکمه بعدی --}}
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">‹</a>
                    @else
                        <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">‹</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .range-track { -webkit-appearance: none; appearance: none; width: 100%; height: 6px; border-radius: 999px; background: var(--bg-elevated); box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light); }
        .range-track::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: var(--accent); box-shadow: 2px 2px 5px var(--shadow-dark); cursor: pointer; }

        /* استایل چیپ‌های برند که در واقع radio button هستند */
        .chip {
            background: var(--bg-elevated);
            box-shadow: 3px 3px 6px var(--shadow-dark), -3px -3px 6px var(--shadow-light);
            border-radius: 999px;
            text-align: center;
        }
        .chip.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
            font-weight: 700;
        }

        /* استایل آیتم‌های صفحه‌بندی */
        .neu-page-item {
            background: var(--bg-elevated);
            box-shadow: 3px 3px 6px var(--shadow-dark), -3px -3px 6px var(--shadow-light);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .neu-page-item:hover {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
        }
        .neu-page-item.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
        }

        @media (max-width: 1023px) {
            #filters-panel.hidden-mobile { display: none; }
        }

        /* اسکرول‌بار زیبا برای لیست دسته‌بندی‌ها */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--shadow-dark); border-radius: 4px; }
    </style>
@endpush
