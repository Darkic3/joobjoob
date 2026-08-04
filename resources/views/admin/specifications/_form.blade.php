@php
    $spec = $spec ?? null;
    $isEdit = $spec !== null;

    $oldVal = function($key, $default = '') use ($isEdit, $spec) {
        if (old($key) !== null) return old($key);
        if ($isEdit && $spec) {
            $keys = explode('.', $key);
            $value = $spec;
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

<div x-data="{
    type: '{{ $oldVal('type', 'select') }}',
    isColor: {{ $oldVal('is_color') ? 'true' : 'false' }},
    colorCode: '{{ $oldVal('color_code', '#3B82F6') }}'
}" class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام مشخصه (فارسی) <span class="text-rose-500">*</span></label>
        <input type="text" name="title[fa]" value="{{ $oldVal('title.fa') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="مثال: رنگ یا سایز" required>
        @error('title.fa') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام انگلیسی</label>
        <input type="text" name="title[en]" value="{{ $oldVal('title.en') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="Color / Size" dir="ltr">
    </div>

    {{-- نوع مشخصه --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نوع مشخصه <span class="text-rose-500">*</span></label>
        <div class="grid grid-cols-2 gap-2">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="select" x-model="type" class="sr-only peer">
                <div class="text-center px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 transition-all">
                    <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">انتخابی</span>
                    <span class="block text-[11px] text-gray-400 mt-0.5">گروه + مقادیر (رنگ، سایز)</span>
                </div>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="type" value="text" x-model="type" class="sr-only peer">
                <div class="text-center px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-500/10 transition-all">
                    <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">نوشتاری</span>
                    <span class="block text-[11px] text-gray-400 mt-0.5">مقدار آزاد (جنس، کشور)</span>
                </div>
            </label>
        </div>
        @error('type') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- والد (فقط برای مقادیر انتخابی) --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">والد (گروه) <span class="text-xs font-normal text-gray-400">— برای تعریف مقدارِ یک گروه</span></label>
        <select name="parent_id" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
            <option value="">— گروه نیست (والد) —</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @if($oldVal('parent_id') == $parent->id) selected @endif>{{ $parent->trans('title') }}</option>
            @endforeach
        </select>
    </div>

    {{-- بخش مقادیر انتخابی (select) --}}
    <div x-show="type === 'select'" x-transition class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">پالت رنگی (نمایش به صورت دایره رنگی)</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_color" value="0">
                <input type="checkbox" name="is_color" value="1" x-model="isColor" class="sr-only peer" @if($oldVal('is_color')) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>

        <div x-show="isColor" x-transition class="flex items-center gap-3 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">کد رنگ</span>
            <input type="color" name="color_code" x-model="colorCode" class="w-12 h-9 rounded-lg border-0 cursor-pointer bg-transparent">
            <input type="text" name="color_code_display" x-model="colorCode" class="flex-1 rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm font-mono" dir="ltr">
            <input type="hidden" name="color_code" :value="colorCode">
        </div>

        <div class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 sm:col-span-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">نمایش در فیلتر محصولات</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_filter" value="0">
                <input type="checkbox" name="is_filter" value="1" class="sr-only peer" @if($oldVal('is_filter')) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>
    </div>

    {{-- وضعیت --}}
    <div class="md:col-span-2 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" class="sr-only peer" @if($oldVal('active', true)) checked @endif>
            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
            <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
        </label>
    </div>
</div>
