@props(['heroSlide' => null])

@php
    $currentLinkType = 'custom';
    $currentLinkId = null;
    $hasButton = false;
    $hasRating = false;
    $hasViewers = false;

    if ($heroSlide) {
        $hasButton = !empty($heroSlide?->btn_text['fa']) || !empty($heroSlide?->linkable_type);
        $hasRating = !empty($heroSlide?->rating);
        $hasViewers = !empty($heroSlide?->viewers) && $heroSlide?->viewers > 0;

        if ($heroSlide?->linkable_type) {
            if ($heroSlide->linkable_type === 'url') {
                $currentLinkType = 'custom';
                $currentLinkId = $heroSlide->linkable_id;
            } elseif (str_contains($heroSlide->linkable_type, 'Product') && !str_contains($heroSlide->linkable_type, 'Category')) {
                $currentLinkType = 'product';
                $currentLinkId = $heroSlide->linkable_id;
            } elseif (str_contains($heroSlide->linkable_type, 'ProductCategory')) {
                $currentLinkType = 'product_category';
                $currentLinkId = $heroSlide->linkable_id;
            } elseif (str_contains($heroSlide->linkable_type, 'Blog') && !str_contains($heroSlide->linkable_type, 'Category')) {
                $currentLinkType = 'blog';
                $currentLinkId = $heroSlide->linkable_id;
            } elseif (str_contains($heroSlide->linkable_type, 'BlogCategory')) {
                $currentLinkType = 'blog_category';
                $currentLinkId = $heroSlide->linkable_id;
            } elseif (str_contains($heroSlide->linkable_type, 'Page')) {
                $currentLinkType = 'page';
                $currentLinkId = $heroSlide->linkable_id;
            }
        }
    }
    $currentLinkType = old('link_type', $currentLinkType);
    $currentLinkId = old('link_value', $currentLinkId);
@endphp

<div x-data="{
    buttonActive: '{{ old('button_active', $hasButton ? '1' : '0') }}' === '1',
    ratingActive: '{{ old('rating_active', $hasRating ? '1' : '0') }}' === '1',
    viewersActive: '{{ old('viewers_active', $hasViewers ? '1' : '0') }}' === '1',
    linkType: '{{ $currentLinkType }}',
    searchQuery: '',
    searchResults: [],
    isSearching: false,
    selectedModel: null,
    linkableId: '{{ $currentLinkId }}',

    get isCustomUrl() { return this.linkType === 'custom'; },
    get isStaticPage() { return this.linkType === 'page'; },
    get isModel() { return ['product', 'product_category', 'blog', 'blog_category'].includes(this.linkType); },

    async searchModels() {
        if (this.searchQuery.length < 2) {
            this.searchResults = [];
            return;
        }
        this.isSearching = true;
        try {
            const response = await fetch(`/admin/hero-slides/search-model?type=${this.linkType}&q=${encodeURIComponent(this.searchQuery)}`);
            const data = await response.json();
            this.searchResults = data;
        } catch (error) {
            console.error('Search error:', error);
            this.searchResults = [];
        }
        this.isSearching = false;
    },

    selectModel(model) {
        this.selectedModel = model;
        this.linkableId = model.id;
        this.searchQuery = model.title || model.name || '';
        this.searchResults = [];
    },

    clearSelection() {
        this.selectedModel = null;
        this.linkableId = '';
        this.searchQuery = '';
    }
}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        {{-- ۱. اطلاعات متنی --}}
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

        {{-- ۲. باکس تنظیمات دکمه و مقصد --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    تنظیمات دکمه و مقصد
                </h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="button_active" value="0">
                    <input type="checkbox" name="button_active" value="1" x-model="buttonActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <div x-show="buttonActive" x-transition class="space-y-4">
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
                        <select x-model="linkType" name="link_type" class="input-custom" @change="clearSelection()">
                            <option value="custom">آدرس دلخواه (URL خارجی)</option>
                            <option value="page">صفحه استاتیک</option>
                            <option value="product">محصول</option>
                            <option value="product_category">دسته‌بندی محصول</option>
                            <option value="blog">مقاله وبلاگ</option>
                            <option value="blog_category">دسته‌بندی مقالات</option>
                        </select>
                    </div>

                    {{-- URL دلخواه --}}
                    <div x-show="isCustomUrl" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">آدرس لینک (URL)</label>
                        <input type="url" name="link_value" value="{{ old('link_value', $currentLinkType === 'custom' ? $currentLinkId : '') }}" class="input-custom font-mono" dir="ltr" placeholder="https://example.com">
                    </div>

                    {{-- صفحات استاتیک --}}
                    <div x-show="isStaticPage" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">انتخاب صفحه</label>
                        <select name="link_value" class="input-custom">
                            <option value="">-- انتخاب کنید --</option>
                            <option value="contact" {{ old('link_value', $currentLinkId) === 'contact' ? 'selected' : '' }}>تماس با ما</option>
                            <option value="about" {{ old('link_value', $currentLinkId) === 'about' ? 'selected' : '' }}>درباره ما</option>
                            <option value="faq" {{ old('link_value', $currentLinkId) === 'faq' ? 'selected' : '' }}>سوالات متداول</option>
                            <option value="terms" {{ old('link_value', $currentLinkId) === 'terms' ? 'selected' : '' }}>قوانین و مقررات</option>
                            <option value="privacy" {{ old('link_value', $currentLinkId) === 'privacy' ? 'selected' : '' }}>حریم خصوصی</option>
                        </select>
                    </div>

                    {{-- مدل‌ها (محصول، دسته‌بندی، مقاله) --}}
                    <div x-show="isModel" x-transition class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">جستجو و انتخاب</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="searchModels()"
                                    @click.outside="searchResults = []"
                                    class="input-custom"
                                    placeholder="برای جستجو تایپ کنید..."
                                >
                                <input type="hidden" name="link_value" :value="linkableId">
                                <div x-show="isSearching" class="absolute left-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                            </div>

                            {{-- نتایج جستجو --}}
                            <div x-show="searchResults.length > 0" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="model in searchResults" :key="model.id">
                                    <div
                                        @click="selectModel(model)"
                                        class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0"
                                    >
                                        <div class="flex items-center gap-3">
                                            <img x-show="model.image" :src="model.image" class="w-10 h-10 rounded-lg object-cover">
                                            <div>
                                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="model.title"></div>
                                                <div x-show="model.subtitle" class="text-xs text-gray-500 dark:text-gray-400" x-text="model.subtitle"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- نمایش آیتم انتخاب شده --}}
                        <div x-show="selectedModel" class="flex items-center gap-3 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
                            <img x-show="selectedModel?.image" :src="selectedModel?.image" class="w-12 h-12 rounded-lg object-cover">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="selectedModel?.title"></div>
                                <div x-show="selectedModel?.subtitle" class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedModel?.subtitle"></div>
                            </div>
                            <button type="button" @click="clearSelection()" class="text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- اطلاعات بازاریابی محصول --}}
                        <div x-show="linkType === 'product' && selectedModel" class="space-y-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">اطلاعات بازاریابی محصول</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">قیمت فعلی</label>
                                    <input type="number" name="price" value="{{ old('price', $heroSlide?->price ?? '') }}" class="input-custom" placeholder="قیمت محصول" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">قیمت قدیمی (اختیاری)</label>
                                    <input type="number" name="old_price" value="{{ old('old_price', $heroSlide?->old_price ?? '') }}" class="input-custom" placeholder="برای نمایش تخفیف">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">واحد پول</label>
                                <select name="currency" class="input-custom">
                                    <option value="IRT" {{ old('currency', $heroSlide?->currency ?? 'IRT') == 'IRT' ? 'selected' : '' }}>تومان (IRT)</option>
                                    <option value="USD" {{ old('currency', $heroSlide?->currency ?? '') == 'USD' ? 'selected' : '' }}>دلار (USD)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ۳. باکس امتیاز (Rating) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    امتیاز (Rating)
                </h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="rating_active" value="0">
                    <input type="checkbox" name="rating_active" value="1" x-model="ratingActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <div x-show="ratingActive" x-transition>
                <input type="text" name="rating" value="{{ old('rating', $heroSlide?->rating ?? '') }}" class="input-custom" placeholder="مثال: 4.8 از 5">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">اگر مقدار وارد شود، در سمت سایت نمایش داده می‌شود.</p>
            </div>
        </div>

        {{-- ۴. باکس بازدیدکنندگان زنده --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    بازدیدکنندگان زنده
                </h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="viewers_active" value="0">
                    <input type="checkbox" name="viewers_active" value="1" x-model="viewersActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <div x-show="viewersActive" x-transition>
                <input type="number" name="viewers" value="{{ old('viewers', $heroSlide?->viewers ?? '') }}" class="input-custom" placeholder="مثال: 42" min="0">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">اگر مقدار وارد شود، در سمت سایت نمایش داده می‌شود.</p>
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

            {{-- ترتیب با درگ‌اندروپ در صفحهٔ ایندکس تنظیم می‌شود — اینجا فیلدی ندارد --}}
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
