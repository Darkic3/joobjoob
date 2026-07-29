@php
// دریافت تم فعال از متغیری که Middleware ست کرده (مثلاً $activeTheme)
$currentTheme = $activeTheme ?? config('themes.default');

// دریافت کلاس‌های مربوط به کارت در این تم خاص
$baseClasses = config("themes.styles.{$currentTheme}.card", config('themes.styles.neumorphism.card'));
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</div>
