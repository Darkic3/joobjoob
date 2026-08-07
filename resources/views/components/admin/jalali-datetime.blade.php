@props([
    'name' => '',
    'value' => '',      // مقدار میلادی (Y-m-d H:i یا هر فرمت پارس‌شدنی) از دیتابیس
    'label' => '',
    'required' => false,
    'placeholder' => '۱۴۰۳/۰۵/۱۴ ۱۵:۳۰',
])

@php
    // تقویم از سرویس زبان‌ها — jalali → پیکر شمسی، gregorian → فیلد میلادی مستقیم
    $isPersian = locale()->isJalali(app()->getLocale());
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

@if($isPersian)
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
               class="input-custom text-left">

        <p class="text-[10px] text-gray-400 mt-1">{{ __('admin.common.jalali_hint') }}</p>

        @error($name)
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
@else
    <div>
        @if($label)
            <label for="{{ $inputId }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                {{ $label }} @if($required) <span class="text-rose-500">*</span> @endif
            </label>
        @endif

        {{-- فیلد میلادی مستقیم — مقدار همان چیزی است که در دیتابیس ذخیره می‌شود --}}
        <input type="datetime-local"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $greg }}"
               class="input-custom text-left"
               @if($required) required @endif>

        <p class="text-[10px] text-gray-400 mt-1">{{ __('admin.common.gregorian_hint') }}</p>

        @error($name)
            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif
