@props(['heroSlide' => null])

@php
    $currentType = old('hero_type', $heroSlide?->hero_type ?? 'internal');
    $currentLinkType = 'custom';
    if ($heroSlide?->linkable_type) {
        if ($heroSlide->linkable_type === 'url') $currentLinkType = 'custom';
        elseif (str_contains($heroSlide->linkable_type, 'Product')) $currentLinkType = 'product';
        elseif (str_contains($heroSlide->linkable_type, 'Category')) $currentLinkType = 'category';
        elseif (str_contains($heroSlide->linkable_type, 'Blog')) $currentLinkType = 'blog';
    }
    $currentLinkType = old('link_type', $currentLinkType);
@endphp

<div x-data="{
    heroType: '{{ $currentType }}',
    linkType: '{{ $currentLinkType }}',
    get isInfo() { return this.heroType === 'informational'; },
    get isExternal() { return this.heroType === 'external'; },
    get isInternal() { return this.heroType === 'internal'; },
    get isCustomLink() { return this.linkType === 'custom'; }
}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        {{-- ۱. انتخاب نوع هیرو (همیشه نمایش داده می‌شود) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>
                نوع بنر هیرو
            </h3>
            <select x-model="heroType" name="hero_type" class="input-custom">
                <option value="informational">۱. فقط اطلاع‌رسانی (عکس + متن + بج، بدون دکمه و قیمت)</option>
                <option value="external">۲. لینک خارجی (عکس + متن + دکمه با آدرس URL دلخواه)</option>
                <option value="internal">۳. لینک داخلی (محصول، دسته‌بندی یا مقاله + نمایش قیمت/امتیاز/بازدید)</option>
            </select>
        </div>

        {{-- ۲. اطلاعات متنی (برای هر ۳ حالت مشترک است) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                اطلاعات متنی
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان (فارسی) <span class="text-red-500">*</span></label>
                    <input type="text" name="title[fa]" value="{{ old('title.fa', $heroSlide?->title['fa'] ?? '') }}" class="input-custom" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title (English)</label>
                    <input type="text" name="title[en]" value="{{ old('title.en', $heroSlide?->title['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">توضیحات (فارسی)</label>
                    <textarea name="desc[fa]" rows="3" class="input-custom resize-y">{{ old('desc.fa', $heroSlide?->desc['fa'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (English)</label>
                    <textarea name="desc[en]" rows="3" class="input-custom resize-y" dir="ltr">{{ old('desc.en', $heroSlide?->desc['en'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نشان / برچسب (فارسی)</label>
                    <input type="text" name="badge[fa]" value="{{ old('badge.fa', $heroSlide?->badge['fa'] ?? '') }}" class="input-custom" placeholder="مثال: پیشنهاد ویژه">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Badge (English)</label>
                    <input type="text" name="badge[en]" value="{{ old('badge.en', $heroSlide?->badge['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
            </div>
        </div>

        {{-- ۳. تنظیمات لینک و دکمه (مخفی برای حالت informational) --}}
        <div x-show="!isInfo" x-transition class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                تنظیمات دکمه و مقصد
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">متن دکمه (فارسی)</label>
                    <input type="text" name="btn_text[fa]" value="{{ old('btn_text.fa', $heroSlide?->btn_text['fa'] ?? '') }}" class="input-custom" placeholder="مثال: مشاهده محصول">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button Text (English)</label>
                    <input type="text" name="btn_text[en]" value="{{ old('btn_text.en', $heroSlide?->btn_text['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع مقصد لینک</label>
                    <select x-model="linkType" name="link_type" class="input-custom">
                        <option value="custom">آدرس دلخواه (URL خارجی)</option>
                        <option value="product">محصول خاص</option>
                        <option value="category">دسته‌بندی محصول</option>
                        <option value="blog">مقاله وبلاگ</option>
                    </select>
                </div>

                <div x-show="isCustomLink" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس لینک (URL)</label>
                    <input type="url" name="link_value" value="{{ old('link_value', $heroSlide?->linkable_type === 'url' ? $heroSlide?->linkable_id : '') }}" class="input-custom font-mono" dir="ltr" placeholder="https://example.com">
                </div>

                <div x-show="!isCustomLink" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <span x-text="linkType === 'product' ? 'شناسه (ID) محصول' : (linkType === 'category' ? 'شناسه (ID) دسته‌بندی' : 'شناسه (ID) مقاله')"></span>
                    </label>
                    <input type="number" name="link_value" value="{{ old('link_value', $heroSlide?->linkable_type !== 'url' ? $heroSlide?->linkable_id : '') }}" class="input-custom" placeholder="مثال: 15">
                    <p class="text-xs text-amber-600 mt-1">⚠️ در حال حاضر ID را دستی وارد کنید. در آینده با انتخابگر هوشمند جایگزین می‌شود.</p>
                </div>
            </div>
        </div>

        {{-- ۴. اطلاعات بازاریابی (فقط برای حالت internal) --}}
        <div x-show="isInternal" x-transition class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                اطلاعات بازاریابی (نمایش در بنر)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">این مقادیر به‌صورت دستی روی بنر نمایش داده می‌شوند تا جذابیت بصری را افزایش دهند.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت (عدد)</label>
                    <input type="number" name="price" value="{{ old('price', $heroSlide?->price ?? 0) }}" class="input-custom" placeholder="مثال: 500000" min="0">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">قیمت قدیمی (اختیاری)</label>
                    <input type="number" name="old_price" value="{{ old('old_price', $heroSlide?->old_price ?? 0) }}" class="input-custom" placeholder="برای نمایش تخفیف">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">واحد پول</label>
                    <select name="currency" class="input-custom">
                        <option value="IRT" {{ old('currency', $heroSlide?->currency ?? 'IRT') == 'IRT' ? 'selected' : '' }}>تومان (IRT)</option>
                        <option value="USD" {{ old('currency', $heroSlide?->currency ?? '') == 'USD' ? 'selected' : '' }}>دلار (USD)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">امتیاز (Rating)</label>
                    <input type="text" name="rating" value="{{ old('rating', $heroSlide?->rating ?? '') }}" class="input-custom" placeholder="مثال: 4.8 از 5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تعداد بازدیدکننده زنده</label>
                    <input type="number" name="viewers" value="{{ old('viewers', $heroSlide?->viewers ?? 42) }}" class="input-custom" placeholder="مثال: 42">
                </div>
            </div>
        </div>
    </div>

    {{-- ستون کناری: تصویر و تنظیمات --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                تنظیمات نمایش
            </h3>

            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $heroSlide?->is_active ?? true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                تصویر اسلاید
            </h3>

            <div class="space-y-3">
                @if($heroSlide && $heroSlide->image)
                    <div class="relative group">
                        <img src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $heroSlide->image) }}" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 object-cover" alt="تصویر فعلی">
                        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                <input type="file" name="image" accept="image/*" class="input-custom file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 cursor-pointer w-full">
                <p class="text-xs text-gray-500 dark:text-gray-400">پیشنهاد: ابعاد 1920x600 پیکسل | حداکثر حجم: ۲ مگابایت</p>
            </div>
        </div>
    </div>
</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.hero-slides.index') }}" class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">انصراف</a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $heroSlide ? 'ویرایش اسلاید' : 'ایجاد اسلاید' }}
    </button>
</div>
