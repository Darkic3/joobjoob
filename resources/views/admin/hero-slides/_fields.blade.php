{{-- فیلدهای ادیتور تک‌هیرو — دادهٔ فرم (linkType/linkValue/hasButton/selectedModel) در HeroSlideController::formData آماده می‌شود --}}
{{-- نکته: داخل attribute نباید کوتیشن دوبل خام باشد (attribute را می‌شکند و کل اسکوپ x-data از کار می‌افتد) --}}
@props(['heroSlide' => null])

<div x-data="{
    buttonActive: '{{ old('button_active', $formData['hasButton'] ? '1' : '0') }}' === '1',
    linkType: '{{ old('link_type', $formData['linkType']) }}',
    customUrl: '{{ old('link_type', $formData['linkType']) === 'custom' ? old('link_value', $formData['linkValue']) : '' }}',
    pageValue: '{{ old('link_type', $formData['linkType']) === 'page' ? old('link_value', $formData['linkValue']) : '' }}',
    searchQuery: {{ json_encode($formData['selectedModel']['title'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }},
    searchResults: [],
    isSearching: false,
    selectedModel: {{ json_encode($formData['selectedModel'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }},
    linkableId: '{{ old('link_type', $formData['linkType']) === 'product' || old('link_type', $formData['linkType']) === 'product_category' || old('link_type', $formData['linkType']) === 'blog' || old('link_type', $formData['linkType']) === 'blog_category' ? old('link_value', $formData['linkValue']) : '' }}',

    get isCustomUrl() { return this.linkType === 'custom'; },
    get isStaticPage() { return this.linkType === 'page'; },
    get isModel() { return ['product', 'product_category', 'blog', 'blog_category'].includes(this.linkType); },
    get linkValue() { return this.isCustomUrl ? this.customUrl : (this.isStaticPage ? this.pageValue : this.linkableId); },

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
                {{ __('admin.hero_slides.text_info') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.title_fa') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title[fa]" value="{{ old('title.fa', $heroSlide?->title['fa'] ?? '') }}" class="input-custom" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.title_en') }}</label>
                    <input type="text" name="title[en]" value="{{ old('title.en', $heroSlide?->title['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.desc_fa') }}</label>
                    <textarea name="desc[fa]" rows="3" class="input-custom resize-y">{{ old('desc.fa', $heroSlide?->desc['fa'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.desc_en') }}</label>
                    <textarea name="desc[en]" rows="3" class="input-custom resize-y" dir="ltr">{{ old('desc.en', $heroSlide?->desc['en'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.badge_fa') }}</label>
                    <input type="text" name="badge[fa]" value="{{ old('badge.fa', $heroSlide?->badge['fa'] ?? '') }}" class="input-custom" placeholder="{{ __('admin.hero_slides.badge_ph') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.badge_en') }}</label>
                    <input type="text" name="badge[en]" value="{{ old('badge.en', $heroSlide?->badge['en'] ?? '') }}" class="input-custom" dir="ltr">
                </div>
            </div>
        </div>

        {{-- ۲. باکس تنظیمات دکمه و مقصد --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    {{ __('admin.hero_slides.link_settings') }}
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.btn_text_fa') }}</label>
                        <input type="text" name="btn_text[fa]" value="{{ old('btn_text.fa', $heroSlide?->btn_text['fa'] ?? '') }}" class="input-custom" placeholder="{{ __('admin.hero_slides.btn_ph') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.btn_text_en') }}</label>
                        <input type="text" name="btn_text[en]" value="{{ old('btn_text.en', $heroSlide?->btn_text['en'] ?? '') }}" class="input-custom" dir="ltr">
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.link_type') }}</label>
                        <select x-model="linkType" name="link_type" class="input-custom" @change="clearSelection()">
                            <option value="custom">{{ __('admin.hero_slides.link_custom') }}</option>
                            <option value="page">{{ __('admin.hero_slides.link_page') }}</option>
                            <option value="product">{{ __('admin.hero_slides.link_product') }}</option>
                            <option value="product_category">{{ __('admin.hero_slides.link_category') }}</option>
                            <option value="blog">{{ __('admin.hero_slides.link_blog') }}</option>
                            <option value="blog_category">{{ __('admin.hero_slides.link_blog_category') }}</option>
                        </select>
                    </div>

                    {{-- URL دلخواه --}}
                    <div x-show="isCustomUrl" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.link_url') }}</label>
                        <input type="url" x-model="customUrl" class="input-custom font-mono" dir="ltr" placeholder="https://example.com">
                    </div>

                    {{-- صفحات استاتیک --}}
                    <div x-show="isStaticPage" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.link_page_pick') }}</label>
                        <select x-model="pageValue" class="input-custom">
                            <option value="">-- {{ __('admin.hero_slides.link_page_pick_ph') }} --</option>
                            <option value="contact">{{ __('messages.pages.contact.title') }}</option>
                            <option value="about">{{ __('messages.pages.about.title') }}</option>
                            <option value="faq">{{ __('messages.pages.faq.title') }}</option>
                            <option value="terms">{{ __('messages.pages.terms.title') }}</option>
                            <option value="privacy">{{ __('messages.pages.privacy.title') }}</option>
                        </select>
                    </div>

                    {{-- مقدار نهایی لینک — یک فیلد واحد بر اساس نوع لینک --}}
                    <input type="hidden" name="link_value" :value="linkValue">

                    {{-- مدل‌ها (محصول، دسته‌بندی، مقاله) --}}
                    <div x-show="isModel" x-transition class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.hero_slides.link_search') }}</label>
                            {{-- @click.outside روی wrapper است تا کلیک روی خودِ نتایج، پنجره را نبندد --}}
                            <div class="relative" @click.outside="searchResults = []">
                                <input
                                    type="text"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="searchModels()"
                                    class="input-custom"
                                    placeholder="{{ __('admin.hero_slides.link_search_ph') }}"
                                >
                                <div x-show="isSearching" class="absolute left-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                </div>

                                {{-- نتایج جستجو — داخل همین باکس relative تا دقیقاً هم‌عرضِ input و چسبیده به آن باز شود --}}
                                <div x-show="searchResults.length > 0" x-transition class="absolute z-50 top-full mt-1 start-0 end-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ستون کناری: وضعیت و تصویر --}}
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('admin.hero_slides.display_settings') }}
            </h3>

            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.hero_slides.active_label') }}</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $heroSlide?->is_active ?? true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ __('admin.hero_slides.inactive_hint') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ __('admin.hero_slides.image') }}

                {{-- راهنمای fallback تصویر — فقط وقتی مدلی (محصول/مقاله/دسته) لینک شده باشد --}}
                <span x-show="isModel" class="relative inline-flex ms-1 group" aria-label="{{ __('admin.hero_slides.help') }}">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 cursor-help hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="absolute bottom-full start-1/2 -translate-x-1/2 mb-2 w-64 text-[11px] font-normal leading-relaxed text-white dark:text-gray-100 bg-gray-900 dark:bg-gray-700 rounded-lg px-3 py-2 opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none z-50 shadow-lg">
                        {{ __('admin.hero_slides.image_fallback_hint') }}
                    </span>
                </span>
            </h3>

            <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400 mb-4">{{ __('admin.hero_slides.image_no_hint') }}</p>

            {{-- پیش‌نمایش + کراپ تصویر با کامپوننت همگانی image-upload --}}
            <x-admin.image-upload
                name="image"
                :currentImage="$heroSlide?->imageUrl()"
                :removable="true"
                hint="{{ __('admin.hero_slides.image_hint') }}"
                :maxSize="20 * 1024 * 1024" />
        </div>
    </div>
</div>

{{-- دکمهٔ ذخیره --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ __('admin.hero_slides.save') }}
    </button>
</div>
