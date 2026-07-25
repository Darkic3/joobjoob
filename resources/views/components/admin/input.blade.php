@props([
    'type' => 'text',
    'name' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'hint' => null,
    'icon' => null,
    'disabled' => false,
    'required' => false,
    'error' => null,
    'id' => null,
])

@php
    $inputId = $id ?? ($name ? "input-{$name}" : "input-" . md5($label ?? 'input'));
@endphp

<div {{ $attributes->class(['space-y-1.5']) }}>
    @if($label)
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500 mr-0.5">*</span>
        @endif
    </label>
    @endif

    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
            {!! $icon !!}
        </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {!! $error ? 'aria-invalid="true" aria-describedby="' . $inputId . '-error"' : '' !!}
            {{ $attributes->merge([
                'class' => 'block w-full rounded-lg border bg-white text-sm text-gray-800 shadow-sm
                           transition-colors duration-200
                           placeholder:text-gray-400
                           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none
                           disabled:bg-gray-50 disabled:text-gray-500
                           ' . ($error
                               ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
                               : 'border-gray-300 hover:border-gray-400')
                           . ($icon ? ' pr-10' : ' pr-3.5')
                           . ' pl-3.5 py-2.5',
            ]) }}
        >
    </div>

    @if($error)
    <p id="{{ $inputId }}-error" class="text-sm text-red-600 flex items-center gap-1">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $error }}
    </p>
    @elseif($hint)
    <p class="text-sm text-gray-400">{{ $hint }}</p>
    @endif
</div>
