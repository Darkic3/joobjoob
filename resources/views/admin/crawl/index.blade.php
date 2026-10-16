@extends('layouts.admin.app')

@section('tab-name', __('admin.crawl.tab'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.crawl.title') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.crawl.subtitle') }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.crawl.index') }}" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                {{ __('admin.common.refresh') }}
            </a>

            <form action="{{ route('admin.crawl.run') }}" method="POST" class="flex flex-col sm:flex-row gap-3 items-end">
                @csrf
                <div class="flex gap-2">
                    <input type="number" name="limit" placeholder="{{ __('admin.crawl.limit_placeholder') }}" min="1" max="1000" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 outline-none w-48">
                    <input type="text" name="pages" placeholder="{{ __('admin.crawl.pages_placeholder') }}" value="1-88" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 outline-none w-40">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('admin.crawl.run_now') }}
                </button>
            </form>
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

    @if(session('warning'))
        <div class="rounded-xl p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-1.251 1.06-.192.39-.276.835-.208 1.28.098.622.554 1.138 1.176 1.33.454.14 1.024.123 1.618-.145 1.696-.769 2.88-2.039 3.053-2.855A4 4 0 109.646 16H5a4 4 0 01-3.932-4.096z"/></svg>
            <div class="flex-1">
                <p class="text-sm text-amber-700 dark:text-amber-300">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <div class="flex-1">
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 8.068 9.246 8.997 8 9.062a5 5 0 00-1.603 0C5.967 8.997 4.381 8.068 4 7.063a12.6 12.6 0 00-.667 6.009A5.006 5.006 0 004.12 14c.185.037.364.078.55.114.125.025.248.064.372.118L7 15m4-12 3.293 3.293a1 1 0 01-1.414 1.414L7 10.414a1 1 0 010-1.414z"/></svg>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.total_crawled') }}</span>
            </div>
            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['total_crawled'] }}</p>
        </div>

        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.total_in_db') }}</span>
            </div>
            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['total_in_db'] }}</p>
        </div>

        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.in_stock') }}</span>
            </div>
            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['in_stock'] }}</p>
        </div>

        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.out_of_stock') }}</span>
            </div>
            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['out_of_stock'] }}</p>
        </div>
    </div>

    {{-- Last Crawl Info --}}
    @if($stats['last_crawl'])
        <div class="rounded-xl p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100">{{ __('admin.crawl.last_crawl') }}</h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($stats['last_crawl']->status === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                    @elseif($stats['last_crawl']->status === 'partial') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                    @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                    @if($stats['last_crawl']->status === 'success') {{ __('admin.crawl.status_success') }}
                    @elseif($stats['last_crawl']->status === 'partial') {{ __('admin.crawl.status_partial') }}
                    @else {{ __('admin.crawl.status_failed') }} @endif
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">{{ __('admin.crawl.products_imported') }}</p>
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $stats['last_crawl']->products_imported }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">{{ __('admin.crawl.products_failed') }}</p>
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $stats['last_crawl']->products_failed }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">{{ __('admin.crawl.pages_crawled') }}</p>
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $stats['last_crawl']->pages_crawled }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">{{ __('admin.crawl.duration') }}</p>
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ $stats['last_crawl']->duration ?? __('admin.common.no_user') }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-gray-600 dark:text-gray-300">{{ __('admin.crawl.triggered_by') }}:</span>
                <span class="font-medium text-gray-800 dark:text-gray-100">{{ $stats['last_crawl']->user?->name ?? __('admin.common.no_user') }}</span>
                <span class="text-gray-400 dark:text-gray-500">{{ $stats['last_crawl']->started_at?->format('Y/m/d H:i') }}</span>
            </div>
        </div>
    @endif

    {{-- Crawl History Table --}}
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">{{ __('admin.crawl.tab') }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.status_success') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.products_imported') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.products_failed') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.pages_crawled') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.duration') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.triggered_by') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.crawl.date') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentCrawls as $crawl)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium
                                    @if($crawl->status === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                    @elseif($crawl->status === 'partial') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                    @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                                    @if($crawl->status === 'success') {{ __('admin.crawl.status_success') }}
                                    @elseif($crawl->status === 'partial') {{ __('admin.crawl.status_partial') }}
                                    @else {{ __('admin.crawl.status_failed') }} @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ $crawl->products_imported }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ $crawl->products_failed }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ $crawl->pages_crawled }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ $crawl->duration ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ $crawl->user?->name ?? __('admin.common.no_user') }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $crawl->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.crawl.show', $crawl->id) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">{{ __('admin.common.view') }}</a>
                                <form action="{{ route('admin.crawl.destroy', $crawl->id) }}" method="POST" class="inline-block"
                                      data-swal-confirm data-swal-danger="1"
                                      data-swal-title="{{ __('admin.crawl.delete_confirm') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                {{ __('admin.crawl.no_crawls') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $recentCrawls->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Handle crawl run button state
    document.querySelector('form[action="{{ route("admin.crawl.run") }}"]').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> در حال انجام...';
        btn.disabled = true;
    });
</script>
@endpush
