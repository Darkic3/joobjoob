@extends('layouts.site.app')

@section('title', $product->trans('name'))

@section('content')
    <div class="grid md:grid-cols-2 gap-10 mb-14">

        {{-- ۱. گالری تصاویر --}}
        <div>
            @php
                $mainImage = $product->main_image_url ?? 'https://picsum.photos/seed/' . $product->slug . '/600/600';
            @endphp

            <div class="neu-flat-lg p-4 mb-4">
                <img id="main-image" src="{{ $mainImage }}"
                     alt="{{ $product->trans('name') }}"
                     class="w-full rounded-xl object-cover transition-all duration-300"
                     style="aspect-ratio:1/1;">
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
        </div>

        {{-- ۲. اطلاعات محصول --}}
        <div class="flex flex-col">
            <h1 class="text-2xl font-extrabold mb-3 leading-tight">{{ $product->trans('name') }}</h1>

            {{-- امتیاز و تعداد نظرات --}}
            @php
                $rating = round($product->average_rating);
                $reviewCount = $product->reviews_count;
            @endphp
            <div class="flex items-center gap-2 mb-5">
            <span class="text-[var(--gold)] tracking-wider">
                {{ str_repeat('★', $rating) }}{{ str_repeat('☆', 5 - $rating) }}
            </span>
                <span class="text-sm" style="color: var(--text-secondary);">
                ({{ $reviewCount }} {{ __('messages.reviews') }})
            </span>
            </div>

            {{-- قیمت --}}
            <div class="flex items-center gap-3 mb-6">
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
                <span class="text-sm font-semibold" style="color: var(--text-secondary);">تومان</span>
            </div>

            {{-- توضیحات --}}
            <p class="mb-8 leading-relaxed text-sm" style="color: var(--text-secondary);">
                {{ $product->trans('description') ?: $product->trans('short_description') ?: __('messages.no_description') }}
            </p>

            {{-- فرم افزودن به سبد خرید --}}
            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="space-y-6 mt-auto">
                @csrf

                {{-- انتخاب رنگ --}}
                @if(is_array($product->colors) && count($product->colors) > 0)
                    <div>
                        <label class="text-sm font-semibold block mb-3">{{ __('messages.select_color') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->colors as $color)
                                <label class="neu-flat-sm px-4 py-2 cursor-pointer text-sm has-[:checked]:font-bold has-[:checked]:text-[var(--accent)] has-[:checked]:shadow-[inset_2px_2px_4px_var(--shadow-dark),inset_-2px_-2px_4px_var(--shadow-light)] transition-all">
                                    <input type="radio" name="color" value="{{ $color }}" class="hidden" @checked($loop->first)>
                                    {{ $color }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- انتخاب سایز --}}
                @if(is_array($product->sizes) && count($product->sizes) > 0)
                    <div>
                        <label class="text-sm font-semibold block mb-3">{{ __('messages.select_size') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->sizes as $size)
                                <label class="neu-flat-sm px-4 py-2 cursor-pointer text-sm has-[:checked]:font-bold has-[:checked]:text-[var(--accent)] has-[:checked]:shadow-[inset_2px_2px_4px_var(--shadow-dark),inset_-2px_-2px_4px_var(--shadow-light)] transition-all">
                                    <input type="radio" name="size" value="{{ $size }}" class="hidden" @checked($loop->first)>
                                    {{ $size }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- تعداد و دکمه خرید --}}
                <div class="flex items-center gap-4 pt-4 border-t border-[var(--shadow-light)]">
                    <div class="neu-flat-sm flex items-center rounded-lg overflow-hidden">
                        <button type="button" onclick="decrementQty()" class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">-</button>
                        <input type="number" id="qty-input" name="qty" value="1" min="1" max="{{ $product->stock }}"
                               class="w-16 h-10 text-center bg-transparent border-none focus:ring-0 text-sm font-bold" readonly>
                        <button type="button" onclick="incrementQty({{ $product->stock }})" class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">+</button>
                    </div>

                    <button type="submit"
                            class="neu-btn neu-btn-primary flex-1 py-3 font-bold text-sm flex items-center justify-center gap-2"
                        @disabled($product->stock === 0)>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        {{ __('messages.add_to_cart') }}
                    </button>
                </div>

                {{-- وضعیت موجودی --}}
                <p class="text-sm font-semibold flex items-center gap-2" style="color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}"></span>
                    {{ $product->stock > 0 ? __('messages.in_stock') . " ({$product->stock} عدد)" : __('messages.out_of_stock') }}
                </p>
            </form>
        </div>
    </div>

    {{-- ۳. نظرات کاربران --}}
    <section class="mb-14 reveal-on-scroll">
        <h2 class="text-xl font-extrabold mb-6 flex items-center gap-2">
            {{ __('messages.reviews') }}
            <span class="text-sm font-normal px-3 py-1 neu-flat-sm" style="color: var(--text-secondary);">{{ $reviewCount }}</span>
        </h2>

        <div class="grid md:grid-cols-2 gap-5">
            @forelse($product->reviews as $review)
                <div class="neu-card p-5 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full neu-flat flex items-center justify-center text-sm font-bold" style="color: var(--accent);">
                            {{ mb_substr($review->user->name ?? 'U', 0, 1) }}
                        </span>
                            <div>
                                <p class="text-sm font-bold">{{ $review->user->name ?? __('messages.user') }}</p>
                                <p class="text-[10px]" style="color: var(--text-secondary);">{{ $review->created_at->diffForHumans() }}</p>
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
                    <p class="text-lg mb-2">💬</p>
                    <p style="color: var(--text-secondary);">{{ __('messages.no_reviews_yet') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ۴. محصولات مرتبط --}}
    @if($related->isNotEmpty())
        <section class="reveal-on-scroll">
            <h2 class="text-xl font-extrabold mb-6">{{ __('messages.related_products') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        function incrementQty(max) {
            const input = document.getElementById('qty-input');
            let val = parseInt(input.value);
            if (val < max) {
                input.value = val + 1;
            }
        }
        function decrementQty() {
            const input = document.getElementById('qty-input');
            let val = parseInt(input.value);
            if (val > 1) {
                input.value = val - 1;
            }
        }
    </script>
@endpush
