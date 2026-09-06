@extends("layouts.admin.app")
@section("tab-name", "جزئیات لاگ حسابرسی")
@section("content")
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">جزئیات لاگ #{{ $log->id }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $log->created_at->format("d/m/Y H:i:s") }}</p>
        </div>
        <div class="flex gap-2">
            @if(!empty($backOrderId))
                <a href="{{ route("admin.audit-logs.order-history", $backOrderId) }}" class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-800/50 text-amber-700 dark:text-amber-400 font-semibold text-sm px-5 py-2 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    بازگشت به تاریخچه سفارش #{{ $backOrderId }}
                </a>
            @endif
            <a href="{{ route("admin.audit-logs.index") }}" class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-5 py-2 rounded-xl">
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                {{ !empty($backOrderId) ? 'لاگ کلی' : 'بازگشت' }}
            </a>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4">اطلاعات کلی</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500">شناسه</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">#{{ $log->id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500">رویداد</span>
                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $log->event_label }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500">تاریخ</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $log->created_at->format("d/m/Y H:i:s") }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500">کاربر</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $log->user?->name ?? "سیستم" }}</span>
                </div>
                <div class="py-2">
                    <span class="text-gray-500 text-xs block mb-1">توضیحات</span>
                    <p class="text-gray-800 dark:text-gray-200 leading-relaxed">{{ $log->description }}</p>
                </div>
            </div>
        </div>
        @if($log->old_values)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-sm font-bold text-red-600 dark:text-red-400 mb-4">مقدار قبلی</h2>
                <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-xl overflow-auto direction-ltr text-left">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
        @if($log->new_values)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mb-4">مقدار جدید</h2>
                <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-xl overflow-auto direction-ltr text-left">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
        @if($log->meta)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 lg:col-span-2">
                <h2 class="text-sm font-bold text-blue-600 dark:text-blue-400 mb-4">اطلاعات تکمیلی</h2>
                <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-xl overflow-auto direction-ltr text-left">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection