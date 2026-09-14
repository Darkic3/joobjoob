@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.errors_list'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- هشدار: جداول مانیتورینگ مایگریت نشده‌اند --}}        @if(isset($tablesReady) && !$tablesReady)
        <div class="rounded-2xl p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
            <h3 class="font-bold text-amber-800 dark:text-amber-300">جداول مانیتورینگ یافت نشد</h3>
            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">مایگریشن‌ها اجرا نشده‌اند — این دستور را روی سرور اجرا کنید:</p>
            <code class="block mt-2 px-3 py-2 bg-amber-100/60 dark:bg-amber-900/40 rounded-lg text-xs font-mono text-amber-800 dark:text-amber-300" dir="ltr">php artisan migrate --force</code>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.monitoring.errors_list') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.monitoring.errors_subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($tablesReady && $errorLogs->where('is_resolved', false)->count() > 0)
                <form action="{{ route('admin.monitoring.errors.bulk-resolve') }}" method="POST" class="inline" onsubmit="return confirm('آیا همه خطاهای حل‌نشده علامت‌گذاری شوند؟')">
                    @csrf
                    <input type="hidden" name="note" value="حل دسته‌ای">
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition-all">{{ __('admin.monitoring.bulk_resolve') }}</button>
                </form>
            @endif
            <form action="{{ route('admin.monitoring.errors.delete-resolved') }}" method="POST" class="inline" onsubmit="return confirm('خطاهای حل‌شده قدیمی (بیش از ۳۰ روز) حذف شوند؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition-all">{{ __('admin.monitoring.delete_resolved') }}</button>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
        <form action="{{ route('admin.monitoring.errors') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.monitoring.search') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_level') }}</label>
                <select name="level" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <option value="">{{ __('admin.monitoring.all_levels') }}</option>
                    <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>{{ __('admin.monitoring.level_critical') }}</option>
                    <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>{{ __('admin.monitoring.level_error') }}</option>
                    <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>{{ __('admin.monitoring.level_warning') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_status') }}</label>
                <select name="resolved" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <option value="">{{ __('admin.monitoring.all_statuses') }}</option>
                    <option value="no" {{ request('resolved') === 'no' ? 'selected' : '' }}>{{ __('admin.monitoring.unresolved') }}</option>
                    <option value="yes" {{ request('resolved') === 'yes' ? 'selected' : '' }}>{{ __('admin.monitoring.resolved') }}</option>
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
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-all">فیلتر</button>
                <a href="{{ route('admin.monitoring.errors') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">{{ __('admin.common.clear') }}</a>
            </div>
        </form>
    </div>

    {{-- Errors Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-start">
                <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.level') ?? 'سطح' }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.message') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.file') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.user') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.status') ?? 'وضعیت' }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.date') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($errorLogs as $error)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-6 py-4 text-gray-500">{{ $error->id }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ $error->level === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    {{ $error->level === 'error' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}
                                    {{ $error->level === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}">
                                    {{ $error->level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <a href="{{ route('admin.monitoring.errors.show', $error->id) }}" class="text-gray-800 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 truncate block">{{ $error->short_message }}</a>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $error->short_file }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $error->user?->name ?? __('admin.monitoring.no_user') }}</td>
                            <td class="px-6 py-4">
                                @if($error->is_resolved)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('admin.monitoring.resolved') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ __('admin.monitoring.unresolved') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $error->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.monitoring.errors.show', $error->id) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="جزئیات">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @unless($error->is_resolved)
                                        <form action="{{ route('admin.monitoring.errors.resolve', $error->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-all" title="{{ __('admin.monitoring.mark_resolved') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                {{ __('admin.monitoring.no_errors') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $errorLogs->links() }}
        </div>
    </div>
</div>
@endsection
