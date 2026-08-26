@extends('layouts.admin.app')

@section('tab-name')
ویرایش روش ارسال: {{ $shippingMethod->title }}
@endsection

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">ویرایش روش ارسال</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $shippingMethod->title }}</p>
            </div>
            <a href="{{ route('admin.shipping-methods.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت به لیست
            </a>
        </div>

        <form action="{{ route('admin.shipping-methods.update', $shippingMethod->id) }}" method="POST">
            @csrf
            @method('PUT')
            @php $method = $shippingMethod; @endphp
            @include('admin.shipping-methods._form')
        </form>
    </div>
@endsection
