@props(['category' => null, 'parentCategories' => []])

@php
    // دسترسی امن برای حالت create ($category می‌تواند null باشد)
    $categoryName = $category?->name ?? [];
    $categoryDescription = $category?->description ?? [];
    $categoryMetaTitle = $category?->meta_title ?? [];
    $categoryMetaDescription = $category?->meta_description ?? [];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ستون اصلی --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- نام (چندزبانه) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                نام دسته‌بندی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نام (فارسی) <span class="text-red-500">*</span></label>
                    <input type="text" name="name[fa]" value="{{ old('name.fa', $categoryName['fa'] ?? '') }}"
                           class="input-custom @error('name.fa') border-red-500 @enderror" required>
                    @error('name.fa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (English) <span class="text-red-500">*</span></label>
                    <input type="text" name="name[en]" value="{{ old('name.en', $categoryName['en'] ?? '') }}"
                           class="input-custom @error('name.en') border-red-500 @enderror" dir="ltr" required>
                    @error('name.en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- URL و ساختار --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                آدرس و ساختار
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس URL (اسلاگ) <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ url('/') }}/</span>
                        <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}"
                               class="input-custom flex-1 @error('slug') border-red-500 @enderror"
                               placeholder="mobile-accessories" dir="ltr"
                               data-auto-slug data-slug-source="input[name='name[fa]']">
                    </div>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی والد</label>
                    <select name="parent_id" class="input-custom @error('parent_id') border-red-500 @enderror">
                        <option value="">-- بدون والد --</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}"
                                {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->trans('name') }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آیکون (کلاس یا ایموجی)</label>
                    <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}"
                           class="input-custom @error('icon') border-red-500 @enderror" placeholder="مثال: 🛒 یا fa-mobile">
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ترتیب نمایش</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                           class="input-custom @error('sort_order') border-red-500 @enderror" min="0">
                    @error('sort_order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- توضیحات (چندزبانه) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>
                توضیحات و سئو
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">توضیحات (فارسی)</label>
                    <textarea name="description[fa]" rows="3" class="input-custom resize-y">{{ old('description.fa', $categoryDescription['fa'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (English)</label>
                    <textarea name="description[en]" rows="3" class="input-custom resize-y" dir="ltr">{{ old('description.en', $categoryDescription['en'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">متا تایتل (فارسی)</label>
                    <input type="text" name="meta_title[fa]" value="{{ old('meta_title.fa', $categoryMetaTitle['fa'] ?? '') }}" class="input-custom">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title (English)</label>
                    <input type="text" name="meta_title[en]" value="{{ old('meta_title.en', $categoryMetaTitle['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">متا دیسکریپشن (فارسی)</label>
                    <textarea name="meta_description[fa]" rows="2" class="input-custom resize-y">{{ old('meta_description.fa', $categoryMetaDescription['fa'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description (English)</label>
                    <textarea name="meta_description[en]" rows="2" class="input-custom resize-y" dir="ltr">{{ old('meta_description.en', $categoryMetaDescription['en'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ستون کناری: تصویر و وضعیت --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                تصویر دسته‌بندی
            </h3>
            <x-admin.image-upload
                name="image"
                label="تصویر دسته‌بندی"
                :currentImage="$category?->image_url"
                :removable="true"
                hint="پیشنهاد: مربع ۴۰۰×۴۰۰ پیکسل | حداکثر حجم: ۲ مگابایت" />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                تنظیمات نمایش
            </h3>

            <x-admin.toggle
                name="is_active"
                label="دسته‌بندی فعال باشد"
                :checked="old('is_active', $category->is_active ?? true)" />
            <p class="text-xs text-gray-500 dark:text-gray-400">دسته غیرفعال در فروشگاه نمایش داده نمی‌شود.</p>
        </div>
    </div>
</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.product-categories.index') }}"
       class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">
        انصراف
    </a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $category ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی' }}
    </button>
</div>
