@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.error_detail'))

@section('content')
<div class="space-y-6 animate-slide-up max-w-4xl">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.monitoring.error_detail') }} #{{ $error->id }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $error->created_at->format('Y/m/d H:i:s') }}</p>
        </div>
        <a href="{{ route('admin.monitoring.errors') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl transition-all flex items-center gap-2">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('admin.monitoring.back_to_dashboard') }}
        </a>
    </div>

    {{-- Status & Level --}}
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-full
            {{ $error->level === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
            {{ $error->level === 'error' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}
            {{ $error->level === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}">
            {{ $error->level }}
        </span>
        @if($error->is_resolved)
            <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('admin.monitoring.resolved') }}
                @if($error->resolved_note)
                    <span class="text-xs font-normal ms-1 opacity-75">({{ $error->resolved_note }})</span>
                @endif
            </span>
        @else
            <form action="{{ route('admin.monitoring.errors.resolve', $error->id) }}" method="POST" class="inline" x-data="{ showNote: false }">
                @csrf
                <input type="hidden" name="note" :value="showNote ? $refs.noteInput.value : ''">
                <button type="button" @click="showNote = !showNote" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-xl transition-all">{{ __('admin.monitoring.mark_resolved') }}</button>
                <div x-show="showNote" x-transition class="inline-flex items-center gap-2 mt-2">
                    <input x-ref="noteInput" type="text" placeholder="{{ __('admin.monitoring.resolve_note_placeholder') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-64">
                    <button type="submit" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium rounded-xl transition-all">ذخیره</button>
                </div>
            </form>
        @endif
    </div>

    {{-- Message --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ __('admin.monitoring.message') }}</h3>
        <p class="text-base text-gray-800 dark:text-gray-200 leading-relaxed">{{ $error->message }}</p>
    </div>

    {{-- Exception & File Info --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ __('admin.monitoring.exception') }}</h3>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-mono">{{ $error->exception_class }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">{{ __('admin.monitoring.file') }}</h3>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-mono" dir="ltr">{{ $error->file }}{{ $error->line ? ':' . $error->line : '' }}</p>
        </div>
    </div>

    {{-- Request Info --}}
    @if($error->url || $error->method || $error->ip)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-3">اطلاعات درخواست</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @if($error->method)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">{{ __('admin.monitoring.method') }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $error->method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}">
                            {{ $error->method }}
                        </span>
                    </div>
                @endif
                @if($error->ip)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">{{ __('admin.monitoring.ip') }}</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200 font-mono" dir="ltr">{{ $error->ip }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">{{ __('admin.monitoring.user') }}</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $error->user?->name ?? __('admin.monitoring.no_user') }}</p>
                </div>
            </div>
            @if($error->url)
                <div class="mt-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">URL</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 font-mono break-all" dir="ltr">{{ $error->url }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Stack Trace --}}
    @if(filled($error->trace) && (is_array($error->trace) ? count($error->trace) > 0 : true))
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.monitoring.trace') }}</h3>
            </div>
            <div class="p-4">
                <pre class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto leading-relaxed" dir="ltr">{{ is_array($error->trace) ? implode("\n", $error->trace) : (string) $error->trace }}</pre>
            </div>
        </div>
    @endif

    {{-- Context --}}
    @if(filled($error->context) && (is_array($error->context) ? count($error->context) > 0 : true))
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.monitoring.context') }}</h3>
            </div>
            <div class="p-4">
                <pre class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 text-xs text-gray-700 dark:text-gray-300 overflow-x-auto leading-relaxed">{{ json_encode($error->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif

</div>
@endsection
