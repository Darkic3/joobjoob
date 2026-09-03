@extends('layouts.admin.app')

@section('tab-name', 'افزودن کد تخفیف')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-4 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">افزودن کد تخفیف جدید</h1>
                <p class="text-gray-500 dark:text-gray-400 text-xs">ایجاد کد تخفیف جدید با تنظیمات دلخواه</p>
            </div>
            <a href="{{ route('admin.discounts.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت به لیست
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-4">
                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.discounts.store') }}" method="POST">
            @csrf
            @include('admin.discounts._form')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ذخیره کد تخفیف
                </button>
                <a href="{{ route('admin.discounts.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-sm font-semibold transition-colors">
                    انصراف
                </a>
            </div>
        </form>
    </div>
@endsection
