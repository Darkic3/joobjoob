@props(['compact' => false])

@php
    // شبکه‌های اجتماعی — منبع واحد حقیقت (کلیدهای تنظیمات + آیکون اختصاصی هر شبکه)
    $networks = [
        'instagram' => [
            'label' => __('messages.socials.instagram'),
            'icon'  => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.6" fill="currentColor" stroke="none"/>',
        ],
        'telegram' => [
            'label' => __('messages.socials.telegram'),
            'icon'  => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        ],
        'whatsapp' => [
            'label' => __('messages.socials.whatsapp'),
            'icon'  => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>',
        ],
        'twitter' => [
            'label' => __('messages.socials.twitter'),
            'icon'  => '<path fill="currentColor" stroke="none" d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93Z"/>',
        ],
        'youtube' => [
            'label' => __('messages.socials.youtube'),
            'icon'  => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/>',
        ],
    ];

    // فقط شبکه‌هایی که از تنظیمات مقدار دارند نمایش داده می‌شوند
    $active = collect($networks)->filter(fn ($net, $key) => setting('social_'.$key))->all();
@endphp

@if($active)
    <div class="site-social {{ $compact ? 'site-social--compact' : '' }}"
         role="list" aria-label="{{ __('messages.join_section_title') }}">
        @foreach($active as $key => $net)
            <a href="{{ setting('social_'.$key) }}" target="_blank" rel="noopener"
               class="site-social-btn site-social--{{ $key }}"
               title="{{ $net['label'] }}" aria-label="{{ $net['label'] }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    {!! $net['icon'] !!}
                </svg>
            </a>
        @endforeach
    </div>
@endif
