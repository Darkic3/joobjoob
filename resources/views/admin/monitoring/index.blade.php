@extends('layouts.admin.app')

@section('tab-name', __('admin.monitoring.tab'))

@section('content')
<div class="space-y-6 animate-slide-up">

    {{-- هشدار: جداول مانیتورینگ روی این محیط مایگریت نشده‌اند --}}
    @if(isset($tablesReady) && !$tablesReady)
        <div class="rounded-2xl p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-800 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-base text-amber-800 dark:text-amber-300">جداول مانیتورینگ یافت نشد</h3>
                <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">مایگریشن‌های مانیتورینگ هنوز روی دیتابیس این محیط اجرا نشده‌اند. روی سرور این دستور را اجرا کنید:</p>
                <code class="block mt-2 px-3 py-2 bg-amber-100/60 dark:bg-amber-900/40 rounded-lg text-xs font-mono text-amber-800 dark:text-amber-300" dir="ltr">php artisan migrate --force</code>
            </div>
        </div>
    @endif

    {{-- Status Banner --}}
    <div class="rounded-2xl p-5 flex items-center gap-4 border transition-colors
        {{ $systemStatus['color'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700' : '' }}
        {{ $systemStatus['color'] === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700' : '' }}
        {{ $systemStatus['color'] === 'danger' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700' : '' }}">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
            {{ $systemStatus['color'] === 'success' ? 'bg-emerald-100 dark:bg-emerald-800' : '' }}
            {{ $systemStatus['color'] === 'warning' ? 'bg-amber-100 dark:bg-amber-800' : '' }}
            {{ $systemStatus['color'] === 'danger' ? 'bg-red-100 dark:bg-red-800' : '' }}">
            @if($systemStatus['icon'] === 'check-circle')
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($systemStatus['icon'] === 'exclamation-circle')
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            @else
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            @endif
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-base
                {{ $systemStatus['color'] === 'success' ? 'text-emerald-800 dark:text-emerald-300' : '' }}
                {{ $systemStatus['color'] === 'warning' ? 'text-amber-800 dark:text-amber-300' : '' }}
                {{ $systemStatus['color'] === 'danger' ? 'text-red-800 dark:text-red-300' : '' }}">
                {{ __('admin.monitoring.system_status') }}: {{ $systemStatus['label'] }}
            </h3>
            <p class="text-sm mt-0.5
                {{ $systemStatus['color'] === 'success' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                {{ $systemStatus['color'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : '' }}
                {{ $systemStatus['color'] === 'danger' ? 'text-red-600 dark:text-red-400' : '' }}">
                {{ $systemStatus['message'] }}
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Errors --}}
        <a href="{{ route('admin.monitoring.errors') }}" class="stat-card group cursor-pointer h-full">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 bg-red-50 dark:bg-red-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                @if($errorStats['unresolved'] > 0)
                    <span class="text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-lg">{{ $errorStats['unresolved'] }} {{ __('admin.monitoring.unresolved') }}</span>
                @else
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">✓</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $errorStats['unresolved'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.unresolved_errors') }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $errorStats['total_24h'] }} {{ __('admin.monitoring.errors_24h') }}</p>
        </a>

        {{-- Payments --}}
        <a href="{{ route('admin.monitoring.payments') }}" class="stat-card group cursor-pointer h-full">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 bg-orange-50 dark:bg-orange-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                @if($payments['failed_24h'] > 0)
                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-lg">{{ $payments['failure_rate'] }}%</span>
                @else
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">✓</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $payments['failed_24h'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.failed_payments') }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $payments['total_24h'] }} {{ __('admin.monitoring.total_24h') }}</p>
        </a>

        {{-- SMS --}}
        <a href="{{ route('admin.monitoring.sms') }}" class="stat-card group cursor-pointer h-full">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 bg-purple-50 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                @if($sms['failed_24h'] > 0)
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded-lg">{{ $sms['failure_rate'] }}%</span>
                @else
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">✓</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $sms['failed_24h'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.failed_sms') }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $sms['total_24h'] }} {{ __('admin.monitoring.total_24h') }}</p>
        </a>

        {{-- Performance --}}
        <a href="{{ route('admin.monitoring.performance') }}" class="stat-card group cursor-pointer h-full">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                @if($performance['slow_24h'] > 5)
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-lg">{{ $performance['slow_24h'] }} {{ __('admin.monitoring.slow_requests') }}</span>
                @else
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">✓</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $performance['avg_response_ms'] }}<span class="text-sm font-normal text-gray-400">ms</span></p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.avg_response') }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $performance['total_24h'] }} {{ __('admin.monitoring.total_24h') }}</p>
        </a>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.monitoring.errors') }}" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            {{ __('admin.monitoring.errors_list') }}
        </a>
        <a href="{{ route('admin.monitoring.payments') }}" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            {{ __('admin.monitoring.payments_list') }}
        </a>
        <a href="{{ route('admin.monitoring.sms') }}" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            {{ __('admin.monitoring.sms_list') }}
        </a>
        <a href="{{ route('admin.monitoring.performance') }}" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ __('admin.monitoring.performance_list') }}
        </a>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Errors --}}
        <x-admin.card :title="__('admin.monitoring.recent_errors')">
            @forelse($errorStats['recent'] as $error)
                <div class="flex items-start gap-3 py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium flex-shrink-0
                        {{ $error->level === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        {{ $error->level === 'error' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}
                        {{ $error->level === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}">
                        {{ $error->level }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
                            <a href="{{ route('admin.monitoring.errors.show', $error->id) }}" class="hover:text-primary-600 dark:hover:text-primary-400">{{ $error->short_message }}</a>
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $error->short_file }} · {{ $error->created_at->diffForHumans() }}</p>
                    </div>
                    @unless($error->is_resolved)
                        <form action="{{ route('admin.monitoring.errors.resolve', $error->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 font-medium px-2 py-1 rounded hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">{{ __('admin.monitoring.mark_resolved') }}</button>
                        </form>
                    @endunless
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">{{ __('admin.monitoring.no_errors') }}</p>
            @endforelse
        </x-admin.card>

        {{-- Recent Failed Payments --}}
        <x-admin.card :title="__('admin.monitoring.recent_failed_payments')">
            @forelse($payments['recent_failed'] as $payment)
                <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 flex-shrink-0">
                        {{ $payment->human_gateway }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $payment->human_action }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            سفارش #{{ $payment->order_id ?? '—' }} · {{ number_format($payment->amount ?? 0) }} تومان · {{ $payment->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">{{ __('admin.monitoring.no_payments_failed') }}</p>
            @endforelse
        </x-admin.card>

        {{-- SMS Status --}}
        <x-admin.card :title="__('admin.monitoring.sms_status')">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $sms['sent_24h'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.sent') }}</p>
                </div>
                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $sms['failed_24h'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.failed') }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                    <p class="text-2xl font-bold text-gray-400 dark:text-gray-500">{{ $sms['skipped_24h'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.monitoring.skipped') }}</p>
                </div>
            </div>
            @forelse($sms['recent_failed'] as $smsLog)
                <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 flex-shrink-0">{{ $smsLog->human_type }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $smsLog->error_message ?? 'خطای نامشخص' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $smsLog->mobile }} · {{ $smsLog->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">{{ __('admin.monitoring.no_sms_failed') }}</p>
            @endforelse
        </x-admin.card>

        {{-- Failed Jobs --}}
        <x-admin.card :title="__('admin.monitoring.failed_jobs')">
            @if($failedJobs['total'] > 0)
                <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-3">{{ $failedJobs['total'] }} Job ناموفق</p>
                @foreach($failedJobs['recent'] as $job)
                    <div class="py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                        <p class="text-sm text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $job->queue ?? 'default' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ Str::limit($job->exception, 100) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->diffForHumans() : '' }}</p>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">{{ __('admin.monitoring.no_failed_jobs') }}</p>
            @endif
        </x-admin.card>
    </div>

    {{-- Slow Requests Table --}}
    @if($performance['recent_slow']->isNotEmpty())
        <x-admin.card :title="__('admin.monitoring.slow_requests_table')">
            <x-admin.table :headers="[
                ['label' => __('admin.monitoring.path')],
                ['label' => __('admin.monitoring.method')],
                ['label' => __('admin.monitoring.status_code')],
                ['label' => __('admin.monitoring.response_time')],
                ['label' => __('admin.monitoring.date')],
            ]">
                @foreach($performance['recent_slow'] as $req)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 font-mono" dir="ltr">{{ Str::limit($req->path, 50) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $req->method === 'GET' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                {{ $req->method === 'POST' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}">
                                {{ $req->method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $req->status_code }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $req->response_time_color === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                {{ $req->response_time_color === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                {{ $req->response_time_color === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}">
                                {{ number_format($req->response_time_ms) }}ms
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $req->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </x-admin.card>
    @endif

</div>
@endsection
