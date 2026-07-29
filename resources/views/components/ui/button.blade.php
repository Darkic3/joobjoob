@props(['variant' => 'primary'])
@php
    $currentTheme = $activeTheme ?? config('themes.default');
    $baseClasses = config("themes.styles.{$currentTheme}.button_{$variant}", config('themes.styles.neumorphism.button_primary'));
@endphp

<button {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</button>
