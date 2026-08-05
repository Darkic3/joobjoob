@extends('layouts.site.app')

@section('title', $product->trans('name'))

@section('content')
    @php
        $mainImage = $product->main_image_url ?? 'https://picsum.photos/seed/' . $product->slug . '/600/600';

        // ===== ساختار مشخصات برای نمایش =====
        // ۱) مشخصه‌های انتخابی گروه‌بندی‌شده: والد → [مقادیر]
        $specGroups = [];
        foreach ($product->productSpecifications as $ps) {
            if ($ps->value === null && $ps->parent_id === null && $ps->specification) {
                $group = $ps->specification;
                $values = $ps->children->map(fn($c) => $c->specification?->trans('title'))->filter()->implode('، ');
                $specGroups[$group->id] = ['title' => $group->trans('title'), 'values' => $values];
            }
        }
        // ۲) مقادیر نوشتاری
        $textValues = [];
        foreach ($product->productSpecifications as $ps) {
            if ($ps->value !== null && $ps->specification) {
                $textValues[] = ['title' => $ps->specification->trans('title'), 'value' => $ps->value];
            }
        }

        // ===== ساختار واریانت برای انتخابگر =====
        // main_specs: [{id, title, is_color, children: [{id, title, color_code, variant_ids}]}]
        $mainSpecs = [];
        $variants = $product->variants;
        foreach ($variants as $variant) {
            foreach ($variant->specifications as $spec) {
                if (!$spec->parent) continue;
                $groupId = $spec->parent->id;
                if (!isset($mainSpecs[$groupId])) {
                    $mainSpecs[$groupId] = [
                        'id' => $groupId,
                        'title' => $spec->parent->trans('title'),
                        'is_color' => (bool) $spec->parent->is_color,
                        'children' => [],
                    ];
                }
                $childKey = $spec->id;
                if (!isset($mainSpecs[$groupId]['children'][$childKey])) {
                    $mainSpecs[$groupId]['children'][$childKey] = [
                        'id' => $spec->id,
                        'title' => $spec->trans('title'),
                        'color_code' => $spec->color_code,
                        'variant_ids' => [],
                    ];
                }
                $mainSpecs[$groupId]['children'][$childKey]['variant_ids'][] = $variant->id;
            }
        }
        $mainSpecs = array_values(array_map(fn($g) => [
            'id' => $g['id'],
            'title' => $g['title'],
            'is_color' => $g['is_color'],
            'children' => array_values($g['children']),
        ], $mainSpecs));

        // واریانت‌ها با specification_ids و قیمت برای JS
        $variantsData = $variants->map(fn($v) => [
            'id' => $v->id,
            'spec_ids' => $v->specifications->pluck('id')->map(fn($id) => (int) $id)->sort()->values()->all(),
            'price' => (int) $v->price,
            'discount_price' => (int) $v->discounted_price,
            'final_price' => (int) $v->final_price,
            'stock' => (int) $v->stock,
            'percent' => $v->percent,
        ])->values();

        // ارزان‌ترین واریانت موجود
        $minVariant = $variants->where('stock', '>', 0)->where('final_price', '>', 0)->sortBy('final_price')->first();
    @endphp

    {{-- Breadcrumb --}}
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ route('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('products.index') }}"
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

            {{-- برند + امتیاز --}}
            <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                @if($product->brand)
                    <a href="{{ route('products.index', ['brand' => $product->brand->url]) }}"
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
            <div class="grid md:grid-cols-2 gap-5">
                @forelse($product->reviews as $review)
                    <div class="neu-card p-5 flex flex-col h-full">
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
                                       style="color: var(--text-secondary);">{{ jdate_format('d F Y', strtotime($review->created_at)) }}</p>
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
                if (priceEl) priceEl.textContent = new Intl.NumberFormat('fa-IR').format(selectedVariant.final_price);
                if (oldEl) {
                    if (selectedVariant.discount_price > 0) {
                        oldEl.textContent = new Intl.NumberFormat('fa-IR').format(selectedVariant.price);
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
