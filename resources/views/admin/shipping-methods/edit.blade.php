@extends('layouts.admin.app')

@section('tab-name', 'ویرایش روش ارسال: {{ $shippingMethod->title }}')

@section('content')
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">🚚 ویرایش روش ارسال: {{ $shippingMethod->title }}</h2>
    <form action="{{ route('admin.shipping-methods.update', $shippingMethod->id) }}" method="POST">
        @csrf
        @method('PUT')
        @php $method = $shippingMethod; @endphp
        @include('admin.shipping-methods._form')
    </form>
@endsection
