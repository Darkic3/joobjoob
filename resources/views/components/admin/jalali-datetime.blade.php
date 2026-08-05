@props([
    'name' => '',
    'value' => '',      // مقدار میلادی (Y-m-d H:i یا هر فرمت پارس‌شدنی) از دیتابیس
    'label' => '',
    'required' => false,
    'placeholder' => '۱۴۰۳/۰۵/۱۴ ۱۵:۳۰',
])

@php
    $inputId = 'jdp-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $name) . '-' . uniqid();
    $hiddenId = $inputId . '-hidden';
    $greg = '';
    $jalaliInit = '';

    if ($value) {
        try {
            $greg = \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
            // تبدیل به شمسی با ارقام انگلیسی (برای پارس در JS)
            $jalaliInit = jdate_format('Y/m/d H:i', strtotime($greg), '', 'Asia/Tehran', 'en');
        } catch (\Throwable $e) {
            // مقدار نامعتبر → خالی
        }
    }
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }} @if($required) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    {{-- مقدار واقعی (میلادی) که به سرور ارسال می‌شود — دیتابیس همیشه میلادی می‌ماند --}}
    <input type="hidden" name="{{ $name }}" id="{{ $hiddenId }}" value="{{ $greg }}">

    {{-- اینپوت شمسی نمایشی (پیکر تقویم جلالی) --}}
    <input type="text"
           id="{{ $inputId }}"
           data-jdp
           data-jdp-format="YYYY/MM/DD HH:mm"
           data-jdp-init-date="{{ $jalaliInit }}"
           value="{{ $jalaliInit }}"
           data-target="{{ $hiddenId }}"
           placeholder="{{ $placeholder }}"
           autocomplete="off"
           dir="ltr"
           class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 text-left">

    <p class="text-[10px] text-gray-400 mt-1">تاریخ شمسی — به‌صورت خودکار به میلادی برای ذخیره تبدیل می‌شود.</p>

    @error($name)
        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
