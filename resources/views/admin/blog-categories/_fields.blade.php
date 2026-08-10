@props(['category' => null, 'parentCategories' => []])

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- عنوان --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('admin.blog_categories.field_title') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="title" value="{{ old('title', $category->title ?? '') }}"
               class="input-custom @error('title') border-red-500 @enderror"
               placeholder="{{ __('admin.blog_categories.title_ph') }}">
        @error('title')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- URL --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('admin.blog_categories.field_url') }} <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ url('/') }}/</span>
            <input type="text" name="url" value="{{ old('url', $category->url ?? '') }}"
                   class="input-custom flex-1 @error('url') border-red-500 @enderror"
                   placeholder="technology" dir="ltr" data-auto-slug data-slug-source="input[name='title']">
        </div>
        @error('url')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- دسته‌بندی والد --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('admin.blog_categories.field_parent') }}
        </label>
        <select name="parent_id" class="input-custom @error('parent_id') border-red-500 @enderror">
            <option value="">-- {{ __('admin.common.no_parent') }} --</option>
            @foreach($parentCategories as $parent)
                <option value="{{ $parent->id }}"
                    {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->title }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- ترتیب نمایش --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('admin.blog_categories.field_sort') }}
        </label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
               class="input-custom @error('sort_order') border-red-500 @enderror"
               min="0">
        @error('sort_order')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- تصویر — تمام‌عرض در گرید دو ستونه --}}
    <div class="md:col-span-2">
        <x-admin.image-upload
            name="image"
            :label="__('admin.blog_categories.field_image')"
            :currentImage="$category?->image_url"
            :cropAspectRatio="1"
            :cropWidth="800"
            :cropHeight="800" />
    </div>

    {{-- توضیحات --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('admin.blog_categories.field_desc') }}
        </label>
        <textarea name="description" rows="4"
                  class="input-custom @error('description') border-red-500 @enderror"
                  placeholder="{{ __('admin.blog_categories.desc_ph') }}">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Toggle Switches --}}
    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-admin.toggle
            name="is_active"
            :label="__('admin.blog_categories.active_label')"
            :checked="old('is_active', $category->is_active ?? true)" />

        <x-admin.toggle
            name="show_in_first_page"
            :label="__('admin.blog_categories.show_home')"
            :checked="old('show_in_first_page', $category->show_in_first_page ?? false)" />
    </div>

</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.blog-categories.index') }}"
       class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">
        {{ __('admin.common.cancel') }}
    </a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ __('admin.blog_categories.save_btn') }}
    </button>
</div>
