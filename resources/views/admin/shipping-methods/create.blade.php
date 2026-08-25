@extends('layouts.admin.app')

@section('tab-name', 'افزودن روش ارسال')

@section('content')
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">🚚 افزودن روش ارسال جدید</h2>
    <form action="{{ route('admin.shipping-methods.store') }}" method="POST">
        @csrf
        @include('admin.shipping-methods._form')
    </form>
@endsection
