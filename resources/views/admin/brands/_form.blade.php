@php
    $brand = $brand ?? null;
    $isEdit = $brand !== null;

    $oldVal = function($key, $default = '') use ($isEdit, $brand) {
        if (old($key) !== null) return old($key);
        if ($isEdit && $brand) {
            $keys = explode('.', $key);
            $value = $brand;
            foreach ($keys as $k) {
                if (is_object($value) && isset($value->$k)) $value = $value->$k;
                elseif (is_array($value) && isset($value[$k])) $value = $value[$k];
                else return $default;
            }
            return $value;
        }
        return $default;
    };
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام برند (فارسی) <span class="text-rose-500">*</span></label>
        <input type="text" name="title[fa]" value="{{ $oldVal('title.fa') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="مثال: نایک" required>
        @error('title.fa') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام انگلیسی</label>
        <input type="text" name="title[en]" value="{{ $oldVal('title.en') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="Nike" dir="ltr">
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">آدرس (اسلاگ) <span class="text-rose-500">*</span></label>
        <input type="text" name="url" value="{{ $oldVal('url') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="nike" dir="ltr" data-auto-slug data-slug-source="input[name='title[en]']">
        @error('url') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">لوگوی برند</label>
        @if($isEdit && $brand->image)
            <div class="mb-2">
                <img src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $brand->image) }}" class="w-24 h-24 object-contain rounded-xl border border-gray-200 dark:border-gray-600 bg-white" alt="لوگوی برند">
            </div>
        @endif
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 cursor-pointer">
        <p class="text-xs text-gray-400 mt-1.5">فرمت‌های مجاز: JPG, PNG, WebP | حداکثر ۲ مگابایت</p>
        @error('image') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات برند (فارسی)</label>
        <textarea name="description[fa]" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="توضیح کوتاهی درباره برند...">{{ $oldVal('description.fa') }}</textarea>
    </div>

    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">نمایش در سایت</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" class="sr-only peer" @if($oldVal('active', true)) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>

        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">نمایش در صفحه اول</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="show_in_first_page" value="0">
                <input type="checkbox" name="show_in_first_page" value="1" class="sr-only peer" @if($oldVal('show_in_first_page')) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>
    </div>
</div>
