@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.performance_list'))

@section('content')
<div class="space-y-6 animate-slide-up">

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.monitoring.performance_list') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.monitoring.performance_subtitle') }}</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
        <form action="{{ route('admin.monitoring.performance') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.monitoring.path') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_method') }}</label>
                <select name="method" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <option value="">{{ __('admin.monitoring.all_types') }}</option>
                    <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                    <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                    <option value="PUT" {{ request('method') === 'PUT' ? 'selected' : '' }}>PUT</option>
                    <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_date') }} از</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">تا</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
            </div>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 cursor-pointer">
                    <input type="checkbox" name="slow_only" value="1" request()->boolean('slow_only') ? 'checked' : '' class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-gray-700 dark:text-gray-300">{{ __('admin.monitoring.filter_slow') }}</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-all">فیلتر</button>
                <a href="{{ route('admin.monitoring.performance') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">{{ __('admin.common.clear') }}</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-start">
                <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.path') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.method') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.status_code') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.response_time') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.ip') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($performanceLogs as $log)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-6 py-4 text-gray-500">{{ $log->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 font-mono max-w-xs truncate" dir="ltr" title="{{ $log->path }}">{{ Str::limit($log->path, 60) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                    {{ $log->method === 'POST' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                    {{ $log->method === 'PUT' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                    {{ $log->method === 'DELETE' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                    {{ $log->method }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $log->status_code >= 400 && $log->status_code < 500 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                    {{ $log->status_code >= 500 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                    {{ $log->status_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->response_time_color === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $log->response_time_color === 'info' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                    {{ $log->response_time_color === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                    {{ $log->response_time_color === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                    {{ number_format($log->response_time_ms) }}ms
                                </span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 ms-1">{{ $log->response_time_label }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 font-mono" dir="ltr">{{ $log->ip ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('admin.monitoring.no_errors') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $performanceLogs->links() }}
        </div>
    </div>
</div>
@endsection
