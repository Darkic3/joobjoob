@extends('layouts.site.app')

@section('title', $blog->title)

@section('content')
    @php
        $blogBreadcrumb = [['label' => __('messages.blogs'), 'url' => localizedRoute('blogs.index')]];
        if ($blog->category) {
            $blogBreadcrumb[] = [
                'label' => $blog->category->title,
                'url' => localizedRoute('blogs.category', ['category' => $blog->category->slug]),
            ];
        }
        $blogBreadcrumb[] = ['label' => $blog->title];
    @endphp
    @include('site.partials.breadcrumb', ['items' => $blogBreadcrumb])

    <div class="max-w-3xl mx-auto">
        <article class="neu-flat rounded-3xl overflow-hidden">
            @if($blog->image_url)
                <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" class="w-full max-h-96 object-cover">
            @endif

            <div class="p-6 md:p-10">
                <div class="flex flex-wrap items-center gap-3 mb-5 text-[11px]" style="color: var(--text-secondary);">
                    @if($blog->category)
                        <a href="{{ localizedRoute('blogs.category', ['category' => $blog->category->slug]) }}"
                           class="font-bold uppercase tracking-wide" style="color: var(--accent);">
                            {{ $blog->category->title }}
                        </a>
                        <span class="w-px h-3" style="background: var(--shadow-dark);"></span>
                    @endif
                    <span class="mono">{{ $blog->localizedDate() }}</span>
                    <span class="w-px h-3" style="background: var(--shadow-dark);"></span>
                    <span>{{ number_format($blog->views) }} {{ __('messages.views') }}</span>
                </div>

                <h1 class="text-2xl md:text-3xl font-black leading-snug mb-6">{{ $blog->title }}</h1>

                @if($blog->excerpt)
                    <p class="text-sm font-medium mb-6 leading-relaxed" style="color: var(--text-secondary);">
                        {{ $blog->excerpt }}
                    </p>
                @endif

                <div class="prose-custom text-sm md:text-base leading-8">
                    {!! $blog->content !!}
                </div>
            </div>
        </article>

        {{-- مقالات مرتبط --}}
        @if($related->isNotEmpty())
            <div class="mt-10">
                <h2 class="font-extrabold text-lg mb-5">{{ __('messages.related_posts') }}</h2>
                <div class="grid sm:grid-cols-3 gap-5">
                    @foreach($related as $post)
                        <a href="{{ localizedRoute('blogs.show', ['blog' => $post->slug]) }}"
                           class="neu-flat rounded-2xl overflow-hidden group">
                            <img src="{{ $post->image_url ?? 'https://picsum.photos/seed/blog-'.$post->id.'/600/400' }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-32 object-cover transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy">
                            <div class="p-4">
                                <h3 class="text-sm font-bold leading-snug line-clamp-2 group-hover:text-[var(--accent)] transition-colors">
                                    {{ $post->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- بازگشت به فهرست --}}
        <div class="text-center mt-10">
            <a href="{{ localizedRoute('blogs.index') }}" class="neu-btn px-6 py-2.5 text-sm font-bold">
                ← {{ __('messages.all_posts') }}
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* استایل محتوای مقاله — بدون وابستگی به کلاس‌های خارجی */
        .prose-custom p { margin-bottom: 1.25rem; }
        .prose-custom h2, .prose-custom h3 { font-weight: 800; margin: 1.75rem 0 0.75rem; }
        .prose-custom h2 { font-size: 1.25rem; }
        .prose-custom h3 { font-size: 1.1rem; }
        .prose-custom ul, .prose-custom ol { margin: 0 1.25rem 1.25rem 0; padding-inline-start: 1.25rem; }
        .prose-custom ul { list-style: disc; }
        .prose-custom ol { list-style: decimal; }
        .prose-custom blockquote {
            border-inline-start: 3px solid var(--accent);
            padding-inline-start: 1rem;
            margin: 1.25rem 0;
            font-style: italic;
            color: var(--text-secondary);
        }
        .prose-custom img { border-radius: 1rem; margin: 1.25rem 0; max-width: 100%; }
        .prose-custom a { color: var(--accent); text-decoration: underline; }
    </style>
@endpush
