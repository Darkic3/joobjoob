@extends('layouts.admin.app')

@section('tab-name')
{{ __('admin.banks.edit_title') }}: {{ $bank->title }}
@endsection

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-4 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">{{ __('admin.banks.edit_title') }}</h1>
                <p class="text-gray-500 dark:text-gray-400 text-xs">{{ $bank->title }}</p>
            </div>
            <a href="{{ route('admin.banks.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('admin.banks.back_to_list') }}
            </a>
        </div>

        <form action="{{ route('admin.banks.update', $bank->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.banks._form')
        </form>
    </div>
@endsection
