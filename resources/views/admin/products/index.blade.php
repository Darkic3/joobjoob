@extends('layouts.admin.app')


@section('content')
    @php
        // ساخت چیپ‌های فیلترهای فعال برای نمایش زیر کارت جستجو
        $sortLabels = ['newest' => __('admin.products.sort_newest'), 'oldest' => __('admin.products.sort_oldest'), 'price_asc' => __('admin.products.sort_price_asc'), 'price_desc' => __('admin.products.sort_price_desc'), 'name_asc' => __('admin.products.sort_name_asc'), 'name_desc' => __('admin.products.sort_name_desc')];
        $availabilityLabels = ['in_stock' => __('admin.products.in_stock'), 'out_of_stock' => __('admin.products.out_of_stock')];

        $activeFilters = [];
        if (request('search')) $activeFilters[] = ['label' => __('admin.common.search') . ': «' . request('search') . '»', 'url' => route('admin.products.index', request()->except(['page', 'search']))];
        if (request('category')) {
            $catName = $categories->firstWhere('id', (int) request('category'))?->trans('name') ?? __('admin.products.no_category');
            $activeFilters[] = ['label' => __('admin.common.category') . ': ' . $catName, 'url' => route('admin.products.index', request()->except(['page', 'category']))];
        }
        if (request('brand_id')) {
            $brandName = $brands->firstWhere('id', (int) request('brand_id'))?->trans('title') ?? __('admin.products.no_brand');
            $activeFilters[] = ['label' => __('admin.products.field_brand') . ': ' . $brandName, 'url' => route('admin.products.index', request()->except(['page', 'brand_id']))];
        }
        if (request('status') !== '' && request('status') !== null) $activeFilters[] = ['label' => __('admin.common.status') . ': ' . (request('status') == '1' ? __('admin.common.active') : __('admin.common.inactive')), 'url' => route('admin.products.index', request()->except(['page', 'status']))];
        if (request('sort') && request('sort') !== 'newest') $activeFilters[] = ['label' => __('admin.products.sort') . ': ' . ($sortLabels[request('sort')] ?? request('sort')), 'url' => route('admin.products.index', request()->except(['page', 'sort']))];
        if (request('availability')) $activeFilters[] = ['label' => __('admin.products.availability') . ': ' . ($availabilityLabels[request('availability')] ?? request('availability')), 'url' => route('admin.products.index', request()->except(['page', 'availability']))];
        if (request('price_min')) $activeFilters[] = ['label' => __('admin.products.min_price') . ': ' . number_format((int) request('price_min')), 'url' => route('admin.products.index', request()->except(['page', 'price_min']))];
        if (request('price_max')) $activeFilters[] = ['label' => __('admin.products.max_price') . ': ' . number_format((int) request('price_max')), 'url' => route('admin.products.index', request()->except(['page', 'price_max']))];
        if (request('featured')) $activeFilters[] = ['label' => __('admin.products.only_featured'), 'url' => route('admin.products.index', request()->except(['page', 'featured']))];
        if (request('best_seller')) $activeFilters[] = ['label' => __('admin.products.only_best'), 'url' => route('admin.products.index', request()->except(['page', 'best_seller']))];
    @endphp

    <div x-data="{ searchOpen: false }" class="px-4 sm:px-6 lg:px-8 py-8 max-w-screen-2xl mx-auto">

        <!-- هدر صفحه -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.products.title') }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.products.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 rounded-full px-3 py-1.5 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span><b class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $products->total() }}</b> {{ __('admin.products.count') }}</span>
                </span>
                <button type="button" id="open-search-modal" @click="searchOpen = true"
                        class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-400 dark:hover:border-indigo-500 font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    {{ __('admin.products.advanced_search') }}
                    @if(count($activeFilters) > 0)
                        <span class="bg-indigo-600 text-white text-xs font-bold min-w-5 h-5 px-1.5 rounded-full flex items-center justify-center">{{ count($activeFilters) }}</span>
                    @endif
                </button>
                @if(auth()->user()->canAccess('admin.products.create'))
                    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('admin.products.add_new') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- چیپ‌های فیلترهای فعال -->
        @if(count($activeFilters) > 0)
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.products.active_filters') }}</span>
                @foreach($activeFilters as $filter)
                    <a href="{{ $filter['url'] }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-xs font-medium hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors group">
                        {{ $filter['label'] }}
                        <svg class="w-3 h-3 text-indigo-400 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endforeach
                <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 inline-flex items-center gap-1 hover:underline">{{ __('admin.products.clear_all') }}</a>
            </div>
        @endif

        <!-- مودال جستجوی پیشرفته -->
        <div :class="searchOpen ? 'flex' : 'hidden'" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm admin-backdrop" @click="searchOpen = false"></div>
            <div class="relative min-h-screen flex items-start sm:items-center justify-center p-4 sm:p-6 pointer-events-none">
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl pointer-events-auto admin-modal-panel"
                     @keydown.escape.window="searchOpen = false">
                    <!-- هدر مودال -->
                    <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('admin.products.search_modal_title') }}</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('admin.products.search_modal_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="searchOpen = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="{{ __('admin.products.close') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="GET" action="{{ route('admin.products.index') }}" id="product-filter-form" class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- جستجو --}}
                            <div class="md:col-span-2">
                                <label for="filter-search" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.common.search') }}</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 start-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </span>
                                    <input type="text" name="search" id="filter-search" value="{{ request('search') }}" placeholder="{{ __('admin.products.search_ph') }}"
                                           class="input-custom ps-10 pe-14" data-filter-search>
                                    @if(request('search'))
                                        <a href="{{ route('admin.products.index', request()->except(['page', 'search'])) }}" class="absolute inset-y-0 start-9 flex items-center text-gray-400 hover:text-rose-500 transition-colors" title="حذف جستجو">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </a>
                                    @endif
                                    <kbd class="absolute inset-y-0 end-3 my-auto h-6 w-6 flex items-center justify-center rounded-md bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-xs font-semibold border border-gray-200 dark:border-gray-500 pointer-events-none" title="میانبر صفحه‌کلید: / یا Ctrl+K">/</kbd>
                                </div>
                            </div>

                            {{-- دسته‌بندی --}}
                            <div>
                                <label for="filter-category" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.common.category') }}</label>
                                <select name="category" id="filter-category" class="input-custom">
                                    <option value="">{{ __('admin.products.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @if(request('category') == $category->id) selected @endif>{{ $category->trans('name') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- برند --}}
                            <div>
                                <label for="filter-brand" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.field_brand') }}</label>
                                <select name="brand_id" id="filter-brand" class="input-custom">
                                    <option value="">{{ __('admin.products.all_brands') }}</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @if(request('brand_id') == $brand->id) selected @endif>{{ $brand->trans('title') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- وضعیت --}}
                            <div>
                                <label for="filter-status" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.status_show') }}</label>
                                <select name="status" id="filter-status" class="input-custom">
                                    <option value="">{{ __('admin.roles.all') }}</option>
                                    <option value="1" @if(request('status') == '1') selected @endif>{{ __('admin.common.active') }}</option>
                                    <option value="0" @if(request('status') == '0') selected @endif>{{ __('admin.common.inactive') }}</option>
                                </select>
                            </div>

                            {{-- مرتب‌سازی --}}
                            <div>
                                <label for="filter-sort" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.sort') }}</label>
                                <select name="sort" id="filter-sort" class="input-custom">
                                    <option value="newest" @if(request('sort')=='newest' || !request('sort')) selected @endif>{{ __('admin.products.sort_newest') }}</option>
                                    <option value="oldest" @if(request('sort')=='oldest') selected @endif>{{ __('admin.products.sort_oldest') }}</option>
                                    <option value="price_asc" @if(request('sort')=='price_asc') selected @endif>{{ __('admin.products.sort_price_asc') }}</option>
                                    <option value="price_desc" @if(request('sort')=='price_desc') selected @endif>{{ __('admin.products.sort_price_desc') }}</option>
                                    <option value="name_asc" @if(request('sort')=='name_asc') selected @endif>{{ __('admin.products.sort_name_asc') }}</option>
                                    <option value="name_desc" @if(request('sort')=='name_desc') selected @endif>{{ __('admin.products.sort_name_desc') }}</option>
                                </select>
                            </div>

                            {{-- موجودی --}}
                            <div>
                                <label for="filter-availability" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.availability') }}</label>
                                <select name="availability" id="filter-availability" class="input-custom">
                                    <option value="">{{ __('admin.roles.all') }}</option>
                                    <option value="in_stock" @if(request('availability')=='in_stock') selected @endif>{{ __('admin.products.in_stock') }}</option>
                                    <option value="out_of_stock" @if(request('availability')=='out_of_stock') selected @endif>{{ __('admin.products.out_of_stock') }}</option>
                                </select>
                            </div>

                            {{-- حداقل قیمت --}}
                            <div>
                                <label for="filter-price-min" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.min_price') }}</label>
                                <input type="number" name="price_min" id="filter-price-min" value="{{ request('price_min') }}" min="0" placeholder="0" dir="ltr" class="input-custom text-left">
                            </div>

                            {{-- حداکثر قیمت --}}
                            <div>
                                <label for="filter-price-max" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.products.max_price') }}</label>
                                <input type="number" name="price_max" id="filter-price-max" value="{{ request('price_max') }}" min="0" placeholder="{{ __('admin.products.no_limit') }}" dir="ltr" class="input-custom text-left">
                            </div>
                        </div>

                        {{-- فیلترهای سریع --}}
                        <div class="flex flex-wrap items-center gap-4 pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="featured" value="1" @if(request('featured')) checked @endif class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.products.only_featured') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="best_seller" value="1" @if(request('best_seller')) checked @endif class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.products.only_best') }}</span>
                            </label>
                        </div>
                    </form>

                    <!-- فوتر مودال -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 font-medium text-sm px-3 py-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            {{ __('admin.products.reset') }}
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="searchOpen = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium transition-all">{{ __('admin.common.cancel') }}</button>
                            <button type="submit" form="product-filter-form" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                {{ __('admin.products.apply_filter') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول داده‌ها -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.products.title') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.category') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.status') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.products.availability') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.products.price') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-700">
                                        @if($product->main_image_url)
                                            <img src="{{ $product->main_image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ms-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $product->trans('name') ?? '-' }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($product->brand)
                                                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $product->brand->trans('title') }}</span>
                                            @endif
                                            @if($product->sku)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ $product->category?->trans('name') ?? __('admin.products.no_category') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('admin.common.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ __('admin.common.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($product->stock > 0)
                                    <span class="text-gray-600 dark:text-gray-300 font-medium">{{ __('admin.products.units', ['count' => number_format($product->stock)]) }}</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400 font-medium">{{ __('admin.products.out_of_stock') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->has_discount)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-400 line-through">{{ number_format($product->price) }} {{ __('admin.products.price_unit') }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-bold text-rose-600 dark:text-rose-400">{{ number_format($product->final_price) }} {{ __('admin.products.price_unit') }}</span>
                                            <span class="bg-rose-100 text-rose-700 text-xs px-1.5 py-0.5 rounded font-bold dark:bg-rose-500/20 dark:text-rose-300">{{ $product->discount_percentage }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ number_format($product->price) }} {{ __('admin.products.price_unit') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2" x-data="{ open: false }">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10" title="{{ __('admin.common.edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <div class="relative">
                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('admin.products.more') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute end-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-10">
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('admin.products.delete_confirm') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-full text-start block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">{{ __('admin.products.delete_btn') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">{{ __('admin.products.no_products') }}</p>
                                    <p class="text-sm mt-1">
                                        @if(count($activeFilters) > 0)
                                            {{ __('admin.products.no_products_filters') }}
                                        @else
                                            {{ __('admin.products.no_products_yet') }}
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-3 mt-5">
                                        @if(count($activeFilters) > 0)
                                            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 px-4 py-2 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors">{{ __('admin.products.clear_all') }}</a>
                                        @endif
                                        @if(auth()->user()->canAccess('admin.products.create'))
                                            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl shadow-sm transition-all hover:shadow-md">{{ __('admin.products.add_new') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('product-filter-form');
        const searchInput = document.getElementById('filter-search');
        const openBtn = document.getElementById('open-search-modal');
        if (!form || !openBtn) return;

        // میانبر صفحه‌کلید: / یا Ctrl+K → باز کردن مودال جستجو
        document.addEventListener('keydown', function (e) {
            const tag = (e.target.tagName || '').toLowerCase();
            const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
            if (typing) return;

            if (e.key === '/' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) {
                e.preventDefault();
                openBtn.click();
                if (searchInput) setTimeout(function () { searchInput.focus(); }, 250);
            }
        });
    });
</script>
@endpush
