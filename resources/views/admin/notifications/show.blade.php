@extends('layouts.admin.app')

@section('tab-name', 'جزئیات اعلان')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-3xl mx-auto">

        {{-- هدر --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('admin.notifications.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>

        {{-- کارت اعلان --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-start gap-4">
                <span class="text-3xl">{{ $notification->icon ?? '🔔' }}</span>
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ $notification->title }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ show_date($notification->created_at, 'd F Y - H:i') }}
                    </p>
                </div>
                @if(!$notification->is_read)
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500" title="برخی‌نشده"></span>
                @else
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600" title="خوانده‌شده"></span>
                @endif
            </div>

            @if($notification->body)
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ $notification->body }}
                    </p>
                </div>
            @endif

            <div class="px-6 py-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">نوع اعلان</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $notification->type }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">وضعیت</p>
                        <p class="font-semibold {{ $notification->is_read ? 'text-gray-500' : 'text-indigo-600' }} dark:text-{{ $notification->is_read ? 'gray-400' : 'indigo-400' }}">
                            {{ $notification->is_read ? 'خوانده‌شده' : 'برخی‌نشده' }}
                        </p>
                    </div>
                </div>

                @if($notification->link)
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">مقصد این اعلان:</p>
                        <a href="{{ $notification->link }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
                           target="_blank">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            مشاهده جزئیات
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- دکمه‌های عملیاتی --}}
        <div class="flex items-center gap-3 mt-4">
            <a href="{{ route('admin.notifications.index') }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                بازگشت
            </a>
        </div>
    </div>
@endsection
