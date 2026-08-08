@extends('layouts.admin.app')

@section('tab-name', __('admin.brands.create'))

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.brands.create') }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.brands.create_sub') }}</p>
            </div>
            <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('admin.common.back_to_list') }}
            </a>
        </div>

        <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            @csrf
            @include('admin.brands._form')

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('admin.brands.save_btn') }}
                </button>
            </div>
        </form>
    </div>
@endsection
