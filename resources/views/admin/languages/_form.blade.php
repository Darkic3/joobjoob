{{-- دادهٔ nameLocales در LanguageController (create/edit) آماده می‌شود --}}
@props(['language' => null])

<div x-data="{
        active: {{ $language ? ($language->is_active ? 'true' : 'false') : 'true' }},
        isDefault: {{ $language ? ($language->is_default ? 'true' : 'false') : 'false' }}
    }">

    {{-- کد زبان — در ویرایش فقط‌خواندنی --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                {{ __('admin.languages.field_code') }} <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="code" value="{{ $language ? old('code', $language->code) : old('code') }}"
                   class="input-custom text-sm" placeholder="{{ __('admin.languages.code_ph') }}"
                   dir="ltr" {{ $language ? 'disabled' : 'required' }}
                   pattern="[a-z]{2,8}" maxlength="8">
            @error('code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            @if($language)
                <p class="text-[10px] text-gray-400 mt-1">{{ __('admin.languages.code_readonly') }}</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                {{ __('admin.languages.field_native_name') }} <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="native_name" value="{{ $language ? old('native_name', $language->native_name) : old('native_name') }}"
                   class="input-custom text-sm" placeholder="{{ __('admin.languages.native_name_ph') }}" required>
            @error('native_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- نام‌های ترجمه‌شده — یک ورودی برای هر زبان فعال --}}
    <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('admin.languages.field_names') }} <span class="text-rose-500">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($nameLocales as $code => $lang)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ $lang->localizedName() }} ({{ $code }}) @if($code === 'fa') <span class="text-rose-500">*</span> @endif
                    </label>
                    <input type="text" name="name[{{ $code }}]" value="{{ old("name.{$code}", $language ? ($language->name[$code] ?? '') : '') }}"
                           class="input-custom text-sm" dir="auto" @if($code === 'fa') required @endif>
                </div>
            @endforeach
        </div>
        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        @error('name.fa') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- جهت — ترتیب در ایندکس با درگ‌اندروپ تنظیم می‌شود --}}
    <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('admin.languages.field_dir') }} <span class="text-rose-500">*</span>
        </label>
        <select name="dir" class="input-custom text-sm" required>
            <option value="rtl" @selected(old('dir', $language ? $language->dir : config('languages.'.old('code', '').'.dir', 'ltr')) === 'rtl')>{{ __('admin.languages.rtl') }}</option>
            <option value="ltr" @selected(old('dir', $language ? $language->dir : config('languages.'.old('code', '').'.dir', 'ltr')) === 'ltr')>{{ __('admin.languages.ltr') }}</option>
        </select>
        @error('dir') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- فعال + پیش‌فرض --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
        <label class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <span>
                <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('admin.languages.field_active') }}</span>
                <span class="block text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ __('admin.languages.active_hint') }}</span>
            </span>
            <input type="checkbox" name="is_active" value="1" x-model="active"
                   class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0">
        </label>

        <label class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
               :class="{ 'opacity-50 pointer-events-none': ! active }">
            <span>
                <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('admin.languages.field_default') }}</span>
                <span class="block text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ __('admin.languages.default_hint') }}</span>
            </span>
            <input type="checkbox" name="is_default" value="1" x-model="isDefault"
                   class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0">
        </label>
    </div>
</div>
