@extends('layouts.site.app')

@section('title', __('messages.blogs'))

@section('content')
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <span style="color: var(--text-primary);">{{ __('messages.blogs') }}</span>
    </p>

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- دسته‌بندی‌های مقالات --}}
        <aside class="neu-flat p-5 h-fit lg:sticky lg:top-24">
            <h3 class="font-extrabold text-sm mb-4">{{ __('messages.blog_categories') }}</h3>
            <div class="space-y-1 text-sm">
                <a href="{{ localizedRoute('blogs.index') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-xl transition-all hover:text-[var(--accent)] {{ !isset($activeCategory) ? 'neu-flat-sm' : '' }}">
                    <span>{{ __('messages.all') }}</span>
                </a>
                @foreach($categories as $cat)
                    <a href="{{ localizedRoute('blogs.category', ['category' => $cat->slug]) }}"
                       class="flex items-center justify-between px-3 py-2 rounded-xl transition-all hover:text-[var(--accent)] {{ isset($activeCategory) && $activeCategory->id === $cat->id ? 'neu-flat-sm' : '' }}">
                        <span>{{ $cat->title }}</span>
                        <span class="mono text-[11px]" style="color: var(--text-secondary);">({{ $cat->blogs_count }})</span>
                    </a>
                @endforeach
            </div>
        </aside>

        {{-- لیست مقالات --}}
        <div class="lg:col-span-3">
            <div class="flex items-center justify-between mb-5">
                <h1 class="font-extrabold text-lg">
                    {{ isset($activeCategory) ? $activeCategory->title : __('messages.blogs') }}
                </h1>
                <p class="text-xs" style="color: var(--text-secondary);">
                    <span class="mono font-bold" style="color: var(--text-primary);">{{ number_format($blogs->total()) }}</span>
                    {{ __('messages.blog') }}
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                @forelse($blogs as $blog)
                    <article class="neu-flat rounded-2xl overflow-hidden flex flex-col">
                        <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}" class="block">
                            <img src="{{ $blog->image_url ?? 'https://picsum.photos/seed/blog-'.$blog->id.'/600/400' }}"
                                 alt="{{ $blog->title }}"
                                 class="w-full h-44 object-cover transition-transform duration-300 hover:scale-105"
                                 loading="lazy">
                        </a>
                        <div class="p-5 flex flex-col gap-2 flex-1">
                            @if($blog->category)
                                <a href="{{ localizedRoute('blogs.category', ['category' => $blog->category->slug]) }}"
                                   class="text-[11px] font-bold uppercase tracking-wide" style="color: var(--accent);">
                                    {{ $blog->category->title }}
                                </a>
                            @endif
                            <h2 class="font-extrabold text-base leading-snug">
                                <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}"
                                   class="hover:text-[var(--accent)] transition-colors">
                                    {{ $blog->title }}
                                </a>
                            </h2>
                            @if($blog->excerpt)
                                <p class="text-xs leading-relaxed line-clamp-2" style="color: var(--text-secondary);">
                                    {{ $blog->excerpt }}
                                </p>
                            @endif
                            <div class="mt-auto pt-3 flex items-center justify-between">
                                <span class="text-[11px] mono" style="color: var(--text-secondary);">
                                    {{ $blog->created_at->translatedFormat('d M Y') }}
                                </span>
                                <a href="{{ localizedRoute('blogs.show', ['blog' => $blog->slug]) }}"
                                   class="text-[11px] font-bold hover:text-[var(--accent)] transition-colors">
                                    {{ __('messages.read_more') }} ←
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-20 neu-flat">
                        <p class="text-sm" style="color: var(--text-secondary);">{{ __('messages.no_blogs_found') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- صفحه‌بندی --}}
            @if($blogs->hasPages())
                <div class="flex items-center justify-center gap-2 mt-10">
                    @if($blogs->onFirstPage())
                        <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">›</span>
                    @else
                        <a href="{{ $blogs->previousPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">›</a>
                    @endif

                    @foreach($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                        @if($page == $blogs->currentPage())
                            <span class="neu-page-item active w-10 h-10 text-sm mono flex items-center justify-center font-bold text-[var(--accent)]">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="neu-page-item w-10 h-10 text-sm mono flex items-center justify-center hover:text-[var(--accent)] transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($blogs->hasMorePages())
                        <a href="{{ $blogs->nextPageUrl() }}" class="neu-page-item w-10 h-10 text-sm flex items-center justify-center hover:text-[var(--accent)] transition-colors">‹</a>
                    @else
                        <span class="neu-page-item w-10 h-10 text-sm flex items-center justify-center opacity-50 cursor-not-allowed">‹</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .neu-page-item {
            background: var(--bg-elevated);
            box-shadow: 3px 3px 6px var(--shadow-dark), -3px -3px 6px var(--shadow-light);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .neu-page-item:hover {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
        }
        .neu-page-item.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush
