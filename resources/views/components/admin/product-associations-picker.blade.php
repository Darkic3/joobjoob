@props([
    'products' => collect(),
    'relatedIds' => [],
    'complementIds' => [],
    'currentProductId' => null,
])

@php
    $productList = $products
        ->filter(fn($p) => (int) $p->id !== (int) $currentProductId)
        ->map(fn($p) => [
            'id'    => (int) $p->id,
            'label' => $p->trans('name') . ' (#' . $p->id . ')',
        ])
        ->values()
        ->all();

    $payload = [
        'products'      => $productList,
        'relatedIds'    => array_map('intval', (array) $relatedIds),
        'complementIds' => array_map('intval', (array) $complementIds),
    ];
@endphp

<div x-data="productAssociationsPicker(@js($payload))" class="space-y-6">

    {{-- راهنما --}}
    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 flex gap-3">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m10.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"></path></svg>
        <div class="text-sm text-indigo-800 dark:text-indigo-200">
            {!! __('admin.product_associations.help') !!}
        </div>
    </div>

    {{-- جستجو --}}
    <div class="relative">
        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" x-model="search" placeholder="{{ __('admin.product_associations.search_ph') }}" class="input-custom pr-10">
    </div>

    {{-- دو ستون: مرتبط و مکمل --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- محصولات مرتبط --}}
        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m10.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"/></svg>
                    {{ __('admin.product_associations.related') }}
                </span>
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-full" x-text="relatedIds.length + '{{ __('admin.product_associations.selected', ['count' => '']) }}'"></span>
            </div>
            <div class="p-3 space-y-1.5 max-h-72 overflow-y-auto custom-scrollbar">
                <template x-for="p in filteredProducts" :key="'r' + p.id">
                    <label class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all hover:bg-white dark:hover:bg-gray-800 group">
                        <input type="checkbox" :name="'related_ids[]'" :value="p.id" :checked="isRelated(p.id)" @change="toggleRelated(p.id)" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" x-text="p.label"></span>
                    </label>
                </template>
                <template x-if="filteredProducts.length === 0">
                    <p class="text-center text-sm text-gray-400 py-6">{{ __('admin.product_associations.no_products') }}</p>
                </template>
            </div>
        </div>

        {{-- محصولات مکمل --}}
        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('admin.product_associations.complement') }}
                </span>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-full" x-text="complementIds.length + '{{ __('admin.product_associations.selected', ['count' => '']) }}'"></span>
            </div>
            <div class="p-3 space-y-1.5 max-h-72 overflow-y-auto custom-scrollbar">
                <template x-for="p in filteredProducts" :key="'c' + p.id">
                    <label class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all hover:bg-white dark:hover:bg-gray-800 group">
                        <input type="checkbox" :name="'complement_ids[]'" :value="p.id" :checked="isComplement(p.id)" @change="toggleComplement(p.id)" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" x-text="p.label"></span>
                    </label>
                </template>
                <template x-if="filteredProducts.length === 0">
                    <p class="text-center text-sm text-gray-400 py-6">{{ __('admin.product_associations.no_products') }}</p>
                </template>
            </div>
        </div>
    </div>

    {{-- برچسب‌های انتخاب‌شده --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ __('admin.product_associations.selected_related') }}</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="p in selectedRelated" :key="'sr' + p.id">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-xs font-medium">
                        <span x-text="p.label"></span>
                        <button type="button" @click="toggleRelated(p.id)" class="hover:text-rose-500 transition-colors">✕</button>
                    </span>
                </template>
            </div>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ __('admin.product_associations.selected_complement') }}</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="p in selectedComplement" :key="'sc' + p.id">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-medium">
                        <span x-text="p.label"></span>
                        <button type="button" @click="toggleComplement(p.id)" class="hover:text-rose-500 transition-colors">✕</button>
                    </span>
                </template>
            </div>
        </div>
    </div>
</div>
