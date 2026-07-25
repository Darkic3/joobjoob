@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'shadow' => true,
    'border' => true,
    'hover' => false,
])

<div {{ $attributes->class([
    'bg-white rounded-xl overflow-hidden transition-shadow duration-200',
    'shadow-soft' => $shadow,
    'border border-gray-100' => $border,
    'hover:shadow-md' => $hover,
    'p-5 sm:p-6' => $padding,
]) }}>
    @if($title || $subtitle || isset($actions))
    <div class="flex items-center justify-between mb-5">
        <div>
            @if($title)
                <h3 class="text-base font-semibold text-gray-800">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-sm text-gray-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
    @endif

    @if(isset($header) && !$title && !$subtitle)
        <div class="mb-5">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}
</div>
