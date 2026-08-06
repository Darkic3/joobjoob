@extends('layouts.admin.app')

@section('tab-name', 'ویرایش نقش')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">ویرایش نقش «{{ $role->name }}»</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">دسترسی‌های این نقش را تغییر دهید. تغییرات روی همه کاربران این نقش اعمال می‌شود.</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت به لیست
            </a>
        </div>

        @if($role->is_system)
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <div>این یک <strong>نقش سیستمی</strong> است — فقط سوپرادمین می‌تواند آن را ویرایش کند و هرگز قابل حذف نیست.</div>
            </div>
        @endif

        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            @csrf @method('PUT')
            @include('admin.roles._form')

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all">انصراف</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection
