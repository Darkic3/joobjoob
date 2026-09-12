{{--
    مسیر راهنمای بصری صفحه — `<x-breadcrumbs />`

    منبع داده دقیقاً همان سرویسی است که اسکیمای `BreadcrumbList` را می‌سازد
    (`App\Services\Seo\Breadcrumbs`)، تا نسخهٔ بصری و نسخهٔ ساختاریافته هرگز از هم
    جدا نشوند — اختلاف بین این دو یکی از دلایل رایج هشدار سرچ کنسول است.

    در صفحهٔ اصلی فقط یک سطح وجود دارد و چیزی رندر نمی‌شود.
--}}
@if (count($items) > 1)
    <nav class="site-breadcrumb" aria-label="{{ __('messages.breadcrumb') }}">
        @foreach ($items as $item)
            @unless ($loop->first)
                <span class="site-breadcrumb__sep" aria-hidden="true">
                    <svg class="flip-rtl" width="10" height="10" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </span>
            @endunless

            @if (!empty($item['url']))
                <a href="{{ $item['url'] }}" class="site-breadcrumb__item site-breadcrumb__link">
                    @if ($loop->first)
                        <svg class="site-breadcrumb__home-icon flip-rtl" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            @else
                <span class="site-breadcrumb__item site-breadcrumb__current" title="{{ $item['label'] }}"
                      aria-current="page">
                    {{ \Illuminate\Support\Str::limit($item['label'], 40) }}
                </span>
            @endif
        @endforeach
    </nav>

    <style>
        .site-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 1.5rem;
            padding: 0.55rem 0.85rem;
            border-radius: var(--radius-md, 1rem);
            background: var(--bg-elevated);
            box-shadow: 3px 3px 7px var(--shadow-dark), -3px -3px 7px var(--shadow-light),
                        inset 0 1px 0 color-mix(in srgb, #fff 35%, transparent);
            font-size: 0.72rem;
            width: fit-content;
        }
        .site-breadcrumb__item {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }
        .site-breadcrumb__link {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 0.2rem 0.4rem;
            border-radius: 0.4rem;
            transition: color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .site-breadcrumb__link:hover {
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 8%, transparent);
        }
        .site-breadcrumb__home-icon {
            width: 13px;
            height: 13px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .site-breadcrumb__link:hover .site-breadcrumb__home-icon {
            opacity: 1;
        }
        .site-breadcrumb__sep {
            display: inline-flex;
            align-items: center;
            color: color-mix(in srgb, var(--shadow-dark) 50%, transparent);
            opacity: 0.6;
        }
        .site-breadcrumb__current {
            color: var(--text-primary);
            font-weight: 700;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 640px) {
            .site-breadcrumb {
                font-size: 0.68rem;
                padding: 0.45rem 0.7rem;
                gap: 0.25rem;
            }
            .site-breadcrumb__current {
                max-width: 140px;
            }
        }
    </style>
@endif
