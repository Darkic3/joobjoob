@extends('layouts.admin.app')

@section('tab-name', __('admin.contact.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.contact.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.contact.subtitle') }}</p>
            </div>
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('admin.contact.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('admin.contact.search_ph') }}"
                           class="input-custom w-full">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer select-none">
                    <input type="checkbox" name="status" value="1"
                           @checked(request()->boolean('status'))
                           class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                    <span>{{ __('admin.contact.unread_only') }}</span>
                </label>
                <button type="submit" class="btn-primary px-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('admin.common.search') }}</span>
                </button>
            </form>
        </div>

        {{-- ═══ جدول پیام‌ها — دسکتاپ ═══ --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.sender') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.email') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.department') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.message') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.status') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.contact.sent_at') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors {{ ! $contact->is_read ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $contacts->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-800 dark:text-gray-100">{{ $contact->name }}</span>
                                    @if(! $contact->is_read)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white">
                                            {{ __('admin.contact.unread') }}
                                        </span>
                                    @endif
                                </div>
                                @if($contact->phone)
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" dir="ltr">{{ $contact->phone }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300" dir="ltr">{{ $contact->email }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $contact->department ? __('messages.pages.contact.departments.' . $contact->department, [], $contact->department) : __('admin.contact.no_department') }}
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 max-w-xs">{{ $contact->message }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if($contact->is_read)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('admin.contact.read') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ __('admin.contact.unread') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ show_date($contact->created_at, 'Y/m/d - H:i') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    @if(auth()->user()->canAccess('admin.contact.show'))
                                        <a href="{{ route('admin.contact.show', $contact) }}"
                                           class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-all"
                                           title="{{ __('admin.contact.view') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->canAccess('admin.contact.destroy'))
                                        <form action="{{ route('admin.contact.destroy', $contact) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                                                    title="{{ __('admin.common.delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 mb-4">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.contact.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($contacts->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>

        {{-- ═══ کارت پیام‌ها — موبایل ═══ --}}
        <div class="md:hidden space-y-3">
            @forelse($contacts as $contact)
                @php
                    $cardBorder = ! $contact->is_read ? 'border-r-4 border-r-primary-400' : '';
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm {{ $cardBorder }} overflow-hidden">
                    {{-- هدر کارت --}}
                    <div class="flex items-center justify-between p-4 pb-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-bold flex-shrink-0">
                                {{ mb_substr($contact->name, 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm truncate">{{ $contact->name }}</p>
                                @if($contact->phone)
                                    <p class="text-xs text-gray-400 dark:text-gray-500" dir="ltr">{{ $contact->phone }}</p>
                                @endif
                            </div>
                        </div>
                        @if(! $contact->is_read)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white flex-shrink-0">
                                {{ __('admin.contact.unread') }}
                            </span>
                        @endif
                    </div>

                    {{-- ایمیل + دپارتمان --}}
                    <div class="px-4 pb-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400" dir="ltr">{{ $contact->email }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            {{ $contact->department ? __('messages.pages.contact.departments.' . $contact->department, [], $contact->department) : __('admin.contact.no_department') }}
                        </span>
                    </div>

                    {{-- پیام --}}
                    <div class="px-4 pb-3">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed line-clamp-2">{{ $contact->message }}</p>
                    </div>

                    {{-- فوتر: تاریخ + اقدامات --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ show_date($contact->created_at, 'Y/m/d - H:i') }}</span>
                        <div class="flex items-center gap-1">
                            @if(auth()->user()->canAccess('admin.contact.show'))
                                <a href="{{ route('admin.contact.show', $contact) }}"
                                   class="p-2.5 text-gray-400 dark:text-gray-500 hover:text-primary-600 rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/>
                                    </svg>
                                </a>
                            @endif
                            @if(auth()->user()->canAccess('admin.contact.destroy'))
                                <form action="{{ route('admin.contact.destroy', $contact) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-2.5 text-gray-400 dark:text-gray-500 hover:text-red-600 rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-12 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.contact.empty') }}</p>
                </div>
            @endforelse
        </div>

        {{-- صفحه‌بندی موبایل --}}
        @if($contacts->hasPages())
            <div class="md:hidden bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-4 py-3">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
@endsection
