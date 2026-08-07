@props(['trustBadge' => null])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- آیکون (ایموجی یا کلاس) --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.trust_badges.field_icon') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="icon" value="{{ old('icon', $trustBadge->icon ?? '📦') }}"
                   class="input-custom text-xl"
                   placeholder="{{ __('admin.trust_badges.icon_ph') }}" dir="ltr" required>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('admin.trust_badges.icon_help') }}
            </p>
            @error('icon')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- عنوان --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.trust_badges.field_title') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $trustBadge->title ?? '') }}"
                   class="input-custom"
                   placeholder="{{ __('admin.trust_badges.title_ph') }}" required>
            @error('title')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- توضیحات --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.trust_badges.field_desc') }}
            </label>
            <textarea name="description" rows="4"
                      class="input-custom resize-y min-h-[100px]"
                      placeholder="{{ __('admin.trust_badges.desc_ph') }}">{{ old('description', $trustBadge->description ?? '') }}</textarea>
            @error('description')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">

        {{-- کارت تنظیمات نمایش --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('admin.trust_badges.display') }}
            </h3>

            <div class="space-y-4">
                {{-- ترتیب نمایش --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ __('admin.common.sort_order') }}
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $trustBadge->sort_order ?? 0) }}"
                           class="input-custom" min="0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.trust_badges.sort_help') }}</p>
                    @error('sort_order')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Toggle: فعال --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.trust_badges.active') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $trustBadge->is_active ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.trust-badges.index') }}"
       class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">
        {{ __('admin.common.cancel') }}
    </a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $trustBadge ? __('admin.trust_badges.edit_btn') : __('admin.trust_badges.create_btn') }}
    </button>
</div>
