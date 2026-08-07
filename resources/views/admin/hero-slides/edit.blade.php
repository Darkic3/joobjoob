@extends('layouts.admin.app')
@section('tab-name', __('admin.hero_slides.edit'))
@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.hero-slides.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.hero_slides.edit') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.hero_slides.edit_sub') }}</p>
            </div>
        </div>
        <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            @csrf
            @method('PUT')
            @include('admin.hero-slides._fields', ['heroSlide' => $heroSlide])
        </form>
    </div>
@endsection
