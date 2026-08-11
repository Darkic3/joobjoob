@extends('layouts.site.app')

@section('title', $title)

@section('content')

    {{-- هدر سینمایی صفحه — حباب‌های رنگی شناور + ورود پله‌ای --}}
    <section class="page-hero reveal-on-scroll">
        <div class="page-hero-blobs" aria-hidden="true">
            <span class="page-blob page-blob--1"></span>
            <span class="page-blob page-blob--2"></span>
            <span class="page-blob page-blob--3"></span>
        </div>

        <div class="relative z-10">
            <nav class="page-breadcrumb" aria-label="{{ __('messages.breadcrumb') }}">
                <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
                <span class="page-breadcrumb-sep" aria-hidden="true">
                    <svg class="flip-rtl" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </span>
                <span style="color: var(--text-primary);">{{ $title }}</span>
            </nav>

            <span class="page-eyebrow">{{ $data['hero_badge'] ?? '' }}</span>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-desc">{{ $description }}</p>
        </div>
    </section>

    {{-- محتوای اختصاصی هر صفحه (قوانین و حریم خصوصی از یک پارتشل مشترک استفاده می‌کنند) --}}
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        @include('site.pages._' . (in_array($page, ['terms', 'privacy'], true) ? 'legal' : $page))
    </div>

@endsection
