@props(['brand' => null])

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.brands.title_fa') }} <span class="text-rose-500">*</span></label>
        <input type="text" name="title[fa]" value="{{ old('title.fa', $brand?->title['fa'] ?? '') }}" class="input-custom" placeholder="{{ __('admin.brands.title_fa_ph') }}" required>
        @error('title.fa') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.brands.title_en') }}</label>
        <input type="text" name="title[en]" value="{{ old('title.en', $brand?->title['en'] ?? '') }}" class="input-custom" placeholder="{{ __('admin.brands.title_en_ph') }}" dir="ltr">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.brands.url_slug') }} <span class="text-rose-500">*</span></label>
        <input type="text" name="url" value="{{ old('url', $brand?->url ?? '') }}" class="input-custom" placeholder="{{ __('admin.brands.url_ph') }}" dir="ltr" data-auto-slug data-slug-source="input[name='title[en]']">
        @error('url') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.brands.logo') }}</label>
        @if($brand && $brand->image)
            <div class="mb-2">
                <img src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $brand->image) }}" class="w-24 h-24 object-contain rounded-xl border border-gray-200 dark:border-gray-600 bg-white" alt="{{ __('admin.brands.logo') }}">
            </div>
        @endif
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="input-custom cursor-pointer">
        <p class="text-xs text-gray-400 mt-1.5">{{ __('admin.brands.logo_formats') }}</p>
        @error('image') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('admin.brands.desc_fa') }}</label>
        <textarea name="description[fa]" rows="3" class="input-custom" placeholder="{{ __('admin.brands.desc_ph') }}">{{ old('description.fa', $brand?->description['fa'] ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.brands.show_site') }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" class="sr-only peer" @if(old('active', $brand?->active ?? true)) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>

        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.brands.show_home') }}</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="show_in_first_page" value="0">
                <input type="checkbox" name="show_in_first_page" value="1" class="sr-only peer" @if(old('show_in_first_page', $brand?->show_in_first_page ?? false)) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>
    </div>
</div>
