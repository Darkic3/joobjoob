@extends('layouts.app')

@section('title', __('messages.products'))

@section('content')
<div class="grid md:grid-cols-4 gap-6">

    {{-- فیلترها --}}
    <aside class="neu-card h-fit md:sticky md:top-24">
        <h3 class="font-bold mb-4">{{ __('messages.filter') }}</h3>
        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
            <div>
                <label class="text-sm font-semibold block mb-2">{{ __('messages.categories') }}</label>
                <select name="category" class="neu-input text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.view_all') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold block mb-2">{{ __('messages.brand') }}</label>
                <select name="brand" class="neu-input text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.view_all') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold block mb-2">{{ __('messages.price') }}</label>
                <div class="flex gap-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="از" class="neu-input text-sm">
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="تا" class="neu-input text-sm">
                </div>
            </div>

            <button type="submit" class="neu-btn neu-btn-primary w-full text-sm">{{ __('messages.filter') }}</button>
        </form>
    </aside>

    {{-- لیست محصولات --}}
    <div class="md:col-span-3">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <p class="text-sm" style="color: var(--text-secondary);">{{ $products->total() }} {{ __('messages.products') }}</p>

            <form method="GET" class="flex items-center gap-2">
                @foreach(request()->except('sort') as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <label class="text-sm">{{ __('messages.sort_by') }}</label>
                <select name="sort" class="neu-input text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.view_all') }}</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('messages.price') }} ↑</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('messages.price') }} ↓</option>
                    <option value="newest" @selected(request('sort') === 'newest')>{{ __('messages.newest') ?? 'جدیدترین' }}</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-full text-center py-10" style="color: var(--text-secondary);">{{ __('messages.out_of_stock') }}</p>
            @endforelse
        </div>

        {{-- pagination نئومورفیک --}}
        <div class="flex items-center justify-center gap-2 mt-8">
            @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="neu-page-item {{ $page === $products->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
        </div>
    </div>
</div>
@endsection
