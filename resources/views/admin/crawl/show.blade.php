@extends('layouts.admin.app')

@section('tab-name', __('admin.crawl.tab'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.crawl.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-2 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت به تاریخچه
            </a>
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">لاگ کرラル #{{ $crawlLog->id }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.crawl.subtitle') }}</p>
        </div>

        <div class="flex gap-3">
            @if($crawlLog->status === 'failed')
                <form action="{{ route('admin.crawl.destroy', $crawlLog->id) }}" method="POST" class="inline-block"
                          data-swal-confirm data-swal-danger="1"
                          data-swal-title="{{ __('admin.crawl.delete_confirm') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        حذف لاگ
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-xl p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <div class="flex-1">
                <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Status Badge --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium
                @if($crawlLog->status === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-700
                @elseif($crawlLog->status === 'partial') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-700
                @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-700 @endif">
                @if($crawlLog->status === 'success') {{ __('admin.crawl.status_success') }}
                @elseif($crawlLog->status === 'partial') {{ __('admin.crawl.status_partial') }}
                @else {{ __('admin.crawl.status_failed') }} @endif
            </span>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('admin.crawl.products_imported') }}</p>
            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $crawlLog->products_imported }}</p>
        </div>
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('admin.crawl.products_failed') }}</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $crawlLog->products_failed }}</p>
        </div>
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('admin.crawl.pages_crawled') }}</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $crawlLog->pages_crawled }}</p>
        </div>
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('admin.crawl.duration') }}</p>
            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $crawlLog->duration ?? '-' }}</p>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100">جزئیات کرラル</h3>
        </div>
        <div class="p-6">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 w-1/3">{{ __('admin.crawl.status_success') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">
                            @if($crawlLog->status === 'success') {{ __('admin.crawl.status_success') }}
                            @elseif($crawlLog->status === 'partial') {{ __('admin.crawl.status_partial') }}
                            @else {{ __('admin.crawl.status_failed') }} @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('admin.crawl.products_imported') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->products_imported }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('admin.crawl.products_failed') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->products_failed }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('admin.crawl.pages_crawled') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->pages_crawled }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('admin.crawl.duration') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->duration ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ __('admin.crawl.triggered_by') }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->user?->name ?? __('admin.common.no_user') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">شروع در</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->started_at?->format('Y/m/d H:i:s') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">تکمیل در</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->finished_at?->format('Y/m/d H:i:s') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">تاریخ ثبت</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->created_at->format('Y/m/d H:i:s') }}</td>
                    </tr>
                    @if($crawlLog->limit)
                        <tr>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">محدودیت محصول</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $crawlLog->limit }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Error Message (if any) --}}
    @if($crawlLog->error_message)
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-700 p-5">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <div>
                    <h4 class="font-semibold text-red-800 dark:text-red-200 mb-1">پیام خطا</h4>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-xs font-mono text-red-700 dark:text-red-300 overflow-x-auto">
                        {{ $crawlLog->error_message }}
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
