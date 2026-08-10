@extends('layouts.site.app')

@section('title', $title)

@section('content')
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <span style="color: var(--text-primary);">{{ $title }}</span>
    </p>

    <div class="max-w-3xl mx-auto">
        <div class="neu-flat rounded-3xl p-6 md:p-10">
            <h1 class="text-2xl md:text-3xl font-black mb-6">{{ $title }}</h1>
            <div class="text-sm md:text-base leading-8" style="color: var(--text-secondary);">
                {{ $description }}
            </div>
        </div>
    </div>
@endsection
