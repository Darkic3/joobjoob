@extends('layouts.admin.app')

@section('title', 'مانیتورینگ سیستم')

@section('content')
<div class="space-y-6">

    {{-- Status Banner --}}
    <div class="rounded-xl p-4 flex items-center gap-3
        {{ $systemStatus['color'] === 'success' ? 'bg-green-50 border border-green-200' : '' }}
        {{ $systemStatus['color'] === 'warning' ? 'bg-yellow-50 border border-yellow-200' : '' }}
        {{ $systemStatus['color'] === 'danger' ? 'bg-red-50 border border-red-200' : '' }}">
        <div class="text-2xl">
            @if($systemStatus['icon'] === 'check-circle')
                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            @elseif($systemStatus['icon'] === 'exclamation-circle')
                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            @else
                <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            @endif
        </div>
        <div>
            <h3 class="font-bold {{ $systemStatus['color'] === 'success' ? 'text-green-800' : '' }} {{ $systemStatus['color'] === 'warning' ? 'text-yellow-800' : '' }} {{ $systemStatus['color'] === 'danger' ? 'text-red-800' : '' }}">
                وضعیت سیستم: {{ $systemStatus['label'] }}
            </h3>
            <p class="text-sm {{ $systemStatus['color'] === 'success' ? 'text-green-600' : '' }} {{ $systemStatus['color'] === 'warning' ? 'text-yellow-600' : '' }} {{ $systemStatus['color'] === 'danger' ? 'text-red-600' : '' }}">
                {{ $systemStatus['message'] }}
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Errors --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">خطاهای حل‌نشده</p>
                    <p class="text-2xl font-bold {{ $errors['unresolved'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $errors['unresolved'] }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $errors['total_24h'] }} خطا در ۲۴ ساعت اخیر</p>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">پرداخت‌های ناموفق</p>
                    <p class="text-2xl font-bold {{ $payments['failed_24h'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                        {{ $payments['failed_24h'] }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $payments['failure_rate'] }}% نرخ شكست | {{ $payments['total_24h'] }} کل</p>
        </div>

        {{-- SMS --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">SMS ناموفق</p>
                    <p class="text-2xl font-bold {{ $sms['failed_24h'] > 0 ? 'text-purple-600' : 'text-green-600' }}">
                        {{ $sms['failed_24h'] }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $sms['failure_rate'] }}% نرخ شكست | {{ $sms['total_24h'] }} کل</p>
        </div>

        {{-- Performance --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">درخواست‌های کند</p>
                    <p class="text-2xl font-bold {{ $performance['slow_24h'] > 5 ? 'text-yellow-600' : 'text-green-600' }}">
                        {{ $performance['slow_24h'] }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">میانگین: {{ $performance['avg_response_ms'] }}ms</p>
        </div>
    </div>

    {{-- Detailed Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Errors --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-bold text-gray-800">آخرین خطاهای سیستمی</h3>
            </div>
            <div class="p-4">
                @forelse($errors['recent'] as $error)
                    <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $error->level === 'critical' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $error->level === 'error' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $error->level === 'warning' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ $error->level }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 truncate">{{ $error->short_message }}</p>
                            <p class="text-xs text-gray-400">{{ $error->short_file }} | {{ $error->created_at->diffForHumans() }}</p>
                        </div>
                        @unless($error->is_resolved)
                            <form action="{{ route('admin.monitoring.errors.resolve', $error->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-green-600 hover:text-green-800">حل شد</button>
                            </form>
                        @endunless
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">خطایی ثبت نشده ✅</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Payment Failures --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-bold text-gray-800">پرداخت‌های ناموفق اخیر</h3>
            </div>
            <div class="p-4">
                @forelse($payments['recent_failed'] as $payment)
                    <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                            {{ $payment->human_gateway }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">{{ $payment->human_action }}</p>
                            <p class="text-xs text-gray-400">
                                سفارش #{{ $payment->order_id ?? '—' }} |
                                {{ number_format($payment->amount ?? 0) }} تومان |
                                {{ $payment->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">پرداخت ناموفقی ثبت نشده ✅</p>
                @endforelse
            </div>
        </div>

        {{-- SMS Status --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-bold text-gray-800">وضعیت ارسال SMS</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $sms['sent_24h'] }}</p>
                        <p class="text-xs text-gray-500">ارسال شد</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $sms['failed_24h'] }}</p>
                        <p class="text-xs text-gray-500">ناموفق</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-400">{{ $sms['skipped_24h'] }}</p>
                        <p class="text-xs text-gray-500">رد شد</p>
                    </div>
                </div>
                @forelse($sms['recent_failed'] as $smsLog)
                    <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $smsLog->human_type }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">{{ $smsLog->error_message ?? 'خطای نامشخص' }}</p>
                            <p class="text-xs text-gray-400">{{ $smsLog->mobile }} | {{ $smsLog->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-2">SMS ناموفقی ثبت نشده ✅</p>
                @endforelse
            </div>
        </div>

        {{-- Failed Jobs --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-bold text-gray-800">Failed Jobs</h3>
            </div>
            <div class="p-4">
                @if($failedJobs['total'] > 0)
                    <div class="mb-4">
                        <p class="text-sm text-red-600 font-medium">{{ $failedJobs['total'] }} Job ناموفق</p>
                    </div>
                    @foreach($failedJobs['recent'] as $job)
                        <div class="py-2 {{ !$loop->last ? 'border-b' : '' }}">
                            <p class="text-sm text-gray-700 font-mono">{{ $job->queue ?? 'default' }}</p>
                            <p class="text-xs text-gray-400">{{ Str::limit($job->exception, 100) }}</p>
                            <p class="text-xs text-gray-400">{{ Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-400 text-center py-4">هیچ Job ناموفقی نیست ✅</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Slow Requests --}}
    @if($performance['recent_slow']->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-bold text-gray-800">درخواست‌های کند اخیر</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">مسیر</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">متد</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">وضعیت</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">زمان پاسخ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">زمان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($performance['recent_slow'] as $req)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 font-mono">{{ Str::limit($req->path, 50) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $req->method === 'GET' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $req->method === 'POST' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ $req->method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $req->status_code }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $req->response_time_color === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $req->response_time_color === 'warning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $req->response_time_color === 'danger' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ number_format($req->response_time_ms) }}ms
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $req->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
