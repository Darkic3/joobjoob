@extends('layouts.admin.app')

@section('tab-name', __('admin.contact.tab'))

@section('content')
    <div class="max-w-3xl space-y-6 animate-slide-up">

        {{-- هدر --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.contact.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('admin.contact.back') }}
                </a>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2">{{ __('admin.contact.title') }}</h1>
            </div>
            @if(auth()->user()->canAccess('admin.contact.destroy'))
                <form action="{{ route('admin.contact.destroy', $contact) }}" method="POST" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('admin.common.delete') }}
                    </button>
                </form>
            @endif
        </div>

        {{-- جزئیات پیام --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-bold">
                            {{ mb_substr($contact->name, 0, 1) }}
                        </span>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-gray-100">{{ $contact->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $contact->email }}</div>
                        </div>
                    </div>
                    <div class="ms-auto flex flex-wrap items-center gap-2">
                        @if($contact->is_read)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ __('admin.contact.read') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                {{ __('admin.contact.unread') }}
                            </span>
                        @endif
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ show_date($contact->created_at, 'Y/m/d - H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6 grid sm:grid-cols-2 gap-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.contact.department') }}</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ $contact->department ? __('messages.pages.contact.departments.' . $contact->department, [], $contact->department) : __('admin.contact.no_department') }}
                    </div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.contact.phone') }}</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200" dir="ltr">{{ $contact->phone ?: __('admin.contact.no_department') }}</div>
                </div>
            </div>

            <div class="p-6">
                <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">{{ __('admin.contact.message') }}</div>
                <p class="text-sm leading-7 text-gray-700 dark:text-gray-200 whitespace-pre-line">{{ $contact->message }}</p>
            </div>
        </div>
    </div>
@endsection
