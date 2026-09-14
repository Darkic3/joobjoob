@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.sms_list'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- هشدار: جداول مانیتورینگ مایگریت نشده‌اند --}}
    @if(isset($tablesReady) && !$tablesReady)
        <div class="rounded-2xl p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
            <h3 class="font-bold text-amber-800 dark:text-amber-300">جداول مانیتورینگ یافت نشد</h3>
            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">مایگریشن‌ها اجرا نشده‌اند — این دستور را روی سرور اجرا کنید:</p>
            <code class="block mt-2 px-3 py-2 bg-amber-100/60 dark:bg-amber-900/40 rounded-lg text-xs font-mono text-amber-800 dark:text-amber-300" dir="ltr">php artisan migrate --force</code>
        </div>
    @endif

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.monitoring.sms_list') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.monitoring.sms_subtitle') }}</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
        <form action="{{ route('admin.monitoring.sms') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="موبایل / الگو / خطا"
                       class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_type') }}</label>
                <select name="type" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <option value="">{{ __('admin.monitoring.all_types') }}</option>
                    <option value="otp" {{ request('type') === 'otp' ? 'selected' : '' }}>کد یکبارمصرف</option>
                    <option value="notification" {{ request('type') === 'notification' ? 'selected' : '' }}>اعلان</option>
                    <option value="welcome" {{ request('type') === 'welcome' ? 'selected' : '' }}>خوشامدگویی</option>
                    <option value="bulk" {{ request('type') === 'bulk' ? 'selected' : '' }}>ارسال گروهی</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('admin.monitoring.filter_status') }}</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    <option value="">{{ __('admin.monitoring.all_statuses') }}</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>ارسال شد</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>رد شد</option>
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
                <a href="{{ route('admin.monitoring.sms') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl transition-all">{{ __('admin.common.clear') }}</a>
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
                        <th class="px-6 py-4">{{ __('admin.monitoring.type') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.mobile') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.template') }}</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.status') ?? 'وضعیت' }}</th>
                        <th class="px-6 py-4">خطا</th>
                        <th class="px-6 py-4">{{ __('admin.monitoring.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($smsLogs as $log)
                        @php $status = $log->human_status; @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-6 py-4 text-gray-500">{{ $log->id }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">{{ $log->human_type }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 font-mono" dir="ltr">{{ $log->mobile }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $log->template ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                    {{ ($status['color'] ?? '') === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ ($status['color'] ?? '') === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    {{ ($status['color'] ?? '') === 'secondary' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                    {{ $status['label'] ?? $log->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-red-600 dark:text-red-400 max-w-xs truncate">{{ $log->error_message ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('admin.monitoring.no_sms_failed') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $smsLogs->links() }}
        </div>
    </div>
</div>
@endsection
