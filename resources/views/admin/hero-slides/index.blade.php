@extends('layouts.admin.app')

@section('tab-name', __('admin.hero_slides.tab'))

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.hero_slides.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.hero_slides.subtitle') }}</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                {{ __('admin.hero_slides.preview_site') }}
            </a>
        </div>

        <form action="{{ route('admin.hero-slides.update') }}" method="POST" enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

            {{-- اسلایدهای قدیمی/اضافی — در نسخهٔ مینیمال فقط همین هیرو نمایش داده می‌شود --}}
            @if($extraCount > 0)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3.5">
                    <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-300 inline-flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('admin.hero_slides.extras_hidden', ['count' => $extraCount]) }}
                    </p>
                    <label class="relative inline-flex items-center gap-2.5 cursor-pointer shrink-0">
                        <input type="hidden" name="prune_extras" value="0">
                        <input type="checkbox" name="prune_extras" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">{{ __('admin.hero_slides.prune_extras') }}</span>
                    </label>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                @include('admin.hero-slides._fields', ['heroSlide' => $heroSlide])
            </div>
        </form>
    </div>
@endsection
