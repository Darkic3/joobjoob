@extends('layouts.admin.app')

@section('tab-name', 'مدیریت کدهای تخفیف')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">کدهای تخفیف</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">ایجاد، ویرایش و مدیریت کدهای تخفیف فروشگاه</p>
            </div>
            @if(auth()->user()->canAccess('admin.discounts.create'))
                <a href="{{ route('admin.discounts.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    افزودن کد تخفیف
                </a>
            @endif
        </div>

        {{-- فیلتر و جستجو --}}
        <form method="GET" class="mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو در عنوان یا کد..."
                   class="flex-1 min-w-[200px] px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="">همه وضعیت‌ها</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
            </select>
            <button type="submit" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-sm font-semibold transition-colors">اعمال فیلتر</button>
        </form>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">عنوان</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">کد</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">نوع</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">مبلغ</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">استفاده شده</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">وضعیت</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">انقضا</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($discounts as $discount)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $discount->id }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800 dark:text-white">{{ $discount->title }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $discount->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($discount->type === 'percent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">{{ $discount->amount }}٪</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ number_format($discount->amount) }} تومان</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($discount->amount) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $discount->usage_records_count ?? 0 }} / {{ $discount->count }}</td>
                                <td class="px-6 py-4">
                                    <button onclick="toggleDiscountStatus({{ $discount->id }}, this)"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $discount->is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $discount->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($discount->expires_at)
                                        {{ jdate($discount->expires_at)->format('Y/m/d') }}
                                        @if($discount->expires_at->isPast())
                                            <span class="text-red-500 text-xs">(منقضی)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('admin.discounts.show', $discount) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="جزئیات">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if(auth()->user()->canAccess('admin.discounts.edit'))
                                            <a href="{{ route('admin.discounts.edit', $discount) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" 
