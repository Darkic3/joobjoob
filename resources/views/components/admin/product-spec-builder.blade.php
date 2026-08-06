@props([
    'specGroups' => collect(),
    'textSpecifications' => collect(),
    'selectedSpecs' => [],
    'textSpecValues' => [],
    'existingVariants' => [],
    'existingProperties' => [],
])

@php
    // تبدیل داده‌ها به JSON برای Alpine
    $payload = [
        'groups' => $specGroups->map(fn($g) => [
            'id'       => $g->id,
            'title'    => $g->trans('title'),
            'is_color' => (bool) $g->is_color,
            'children' => $g->children->map(fn($c) => [
                'id'         => $c->id,
                'title'      => $c->trans('title'),
                'color_code' => $c->color_code,
            ])->values(),
        ])->values(),
        'textSpecs' => $textSpecifications->map(fn($s) => [
            'id'    => $s->id,
            'title' => $s->trans('title'),
        ])->values(),
        'selected'  => array_map(fn($ids) => array_map('intval', $ids), (array) $selectedSpecs),
        'textValues'=> (object) array_map('strval', (array) $textSpecValues),
        'variants'  => $existingVariants,
        'properties'=> $existingProperties,
    ];
@endphp

<div
    x-data="productSpecBuilder(@js($payload))"
    class="space-y-8"
>

    {{-- راهنما --}}
    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 flex gap-3">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="text-sm text-indigo-800 dark:text-indigo-200">
            <strong>سازنده تنوع:</strong> از گروه‌های انتخابی (مثل رنگ و سایز) مقادیر را علامت بزنید — ترکیب‌ها خودکار ساخته می‌شوند. برای هر ترکیب قیمت و موجودی مجزا تعیین کنید. اگر محصول واریانت داشته باشد، قیمت و موجودی اصلی به‌صورت خودکار از جمع واریانت‌ها محاسبه می‌شود.
        </div>
    </div>

    {{-- گروه‌های مشخصات انتخابی --}}
    <div>
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            مقادیر مشخصات (رنگ، سایز و ...)
        </h3>

        <template x-if="groups.length === 0">
            <div class="text-center py-10 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">هنوز مشخصه‌ای تعریف نشده است.</p>
                <a href="{{ route('admin.specifications.create') }}" target="_blank" class="inline-block mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">ایجاد مشخصه گروه ←</a>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <template x-for="group in groups" :key="group.id">
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="group.title"></span>
                        <span class="text-[10px] font-semibold text-gray-400 px-2 py-0.5 rounded-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700" x-text="(selected[group.id] || []).length + ' انتخاب'"></span>
                    </div>

                    {{-- انتخابگر رنگی --}}
                    <div x-show="group.is_color" class="flex flex-wrap gap-2.5">
                        <template x-for="child in group.children" :key="child.id">
                            <div class="flex flex-col items-center gap-1.5">
                                <button type="button"
                                        @click="toggle(group.id, child.id)"
                                        :title="child.title"
                                        :class="isSelected(group.id, child.id) ? 'ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-gray-800 scale-110' : 'ring-1 ring-gray-200 dark:ring-gray-600 hover:scale-105'"
                                        class="w-9 h-9 rounded-full shadow-sm transition-all duration-200"
                                        :style="{ backgroundColor: child.color_code || '#e5e7eb' }">
                                    <svg x-show="isSelected(group.id, child.id)" class="w-4 h-4 mx-auto text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 max-w-16 truncate" x-text="child.title"></span>
                            </div>
                        </template>
                        <template x-if="group.children.length === 0">
                            <p class="text-xs text-gray-400">مقداری تعریف نشده — از بخش «مشخصات» پنل، برای این گروه مقدار بسازید.</p>
                        </template>
                    </div>

                    {{-- انتخابگر متنی --}}
                    <div x-show="!group.is_color" class="flex flex-wrap gap-2">
                        <template x-for="child in group.children" :key="child.id">
                            <button type="button"
                                    @click="toggle(group.id, child.id)"
                                    :class="isSelected(group.id, child.id) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-indigo-400'"
                                    class="px-4 py-2 rounded-lg border text-sm font-semibold transition-all duration-200"
                                    x-text="child.title">
                            </button>
                        </template>
                        <template x-if="group.children.length === 0">
                            <p class="text-xs text-gray-400">مقداری تعریف نشده — از بخش «مشخصات» پنل، برای این گروه مقدار بسازید.</p>
                        </template>
                    </div>

                    {{-- hidden inputs برای ارسال انتخاب‌ها --}}
                    <template x-for="child in group.children" :key="'h' + child.id">
                        <input type="hidden" :name="'specs[' + group.id + '][]'" :value="child.id" :disabled="!isSelected(group.id, child.id)">
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- جدول واریانت‌ها --}}
    <div>
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            قیمت و موجودی تنوع‌ها
            <span class="text-xs font-normal text-gray-400">(ترکیب مقادیر انتخاب‌شده)</span>
        </h3>

        <template x-if="variants.length === 0">
            <div class="text-center py-12 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">برای ساخت تنوع، از بالا حداقل یک مقدار برای هر گروه انتخاب کنید.</p>
                <p class="text-xs text-gray-400 mt-1.5">مثال: رنگ (قرمز، مشکی) × سایز (S، M) → ۴ تنوع</p>
            </div>
        </template>

        <template x-if="variants.length > 0">
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3">تنوع</th>
                        <th class="px-4 py-3 w-36">قیمت (تومان)</th>
                        <th class="px-4 py-3 w-36">قیمت با تخفیف</th>
                        <th class="px-4 py-3 w-28">موجودی</th>
                        <th class="px-4 py-3 w-24">وزن (گرم)</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="(v, i) in variants" :key="v.key">
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-2.5">
                                <input type="hidden" :name="'variants[' + i + '][id]'" :value="v.id">
                                <template x-for="sid in v.spec_ids" :key="'s' + sid">
                                    <input type="hidden" :name="'variants[' + i + '][spec_ids][]'" :value="sid">
                                </template>
                                <span class="font-semibold text-gray-800 dark:text-gray-100" x-text="v.label"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" x-model="v.price" :name="'variants[' + i + '][price]'" min="0" placeholder="0" class="input-custom text-sm" dir="ltr">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" x-model="v.discount_price" :name="'variants[' + i + '][discount_price]'" min="0" placeholder="—" class="input-custom text-sm" dir="ltr">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" x-model="v.stock" :name="'variants[' + i + '][stock]'" min="0" placeholder="0" class="input-custom text-sm" dir="ltr">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" x-model="v.weight" :name="'variants[' + i + '][weight]'" min="0" placeholder="100" class="input-custom text-sm" dir="ltr">
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- مقادیر نوشتاری (text specs) --}}
    <div x-show="textSpecs.length > 0">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            ویژگی‌های نوشتاری
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="spec in textSpecs" :key="spec.id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5" x-text="spec.title"></label>
                    <input type="text" :name="'text_specs[' + spec.id + ']'" x-model="textValues[spec.id]" class="input-custom" placeholder="مقدار...">
                </div>
            </template>
        </div>
    </div>

    {{-- ویژگی‌های آزاد (Properties) --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                ویژگی‌های تکمیلی
                <span class="text-xs font-normal text-gray-400">(مثل جنس، کشور سازنده و ...)</span>
            </h3>
            <button type="button" @click="addProperty()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 px-3 py-1.5 rounded-lg transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                افزودن ویژگی
            </button>
        </div>

        <template x-if="properties.length === 0">
            <div class="text-center py-8 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-400">ویژگی‌ای ثبت نشده است.</p>
            </div>
        </template>

        <div class="space-y-3">
            <template x-for="(prop, i) in properties" :key="'p' + i">
                <div class="grid grid-cols-[1fr_1.5fr_auto] gap-3 items-center bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                    <input type="hidden" :name="'properties[' + i + '][id]'" :value="prop.id">
                    <input type="text" x-model="prop.title" :name="'properties[' + i + '][title]'" placeholder="عنوان (مثل: جنس)" class="input-custom text-sm">
                    <input type="text" x-model="prop.value" :name="'properties[' + i + '][value]'" placeholder="مقدار (مثل: چرم طبیعی)" class="input-custom text-sm">
                    <button type="button" @click="properties.splice(i, 1)" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all" title="حذف">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
