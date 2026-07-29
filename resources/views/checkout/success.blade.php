@extends('layouts.app')

@section('title', __('messages.checkout'))

@section('content')
<div class="neu-card text-center py-16 max-w-lg mx-auto">
    <p class="text-4xl mb-4" style="color: var(--success);">✓</p>
    <h1 class="text-xl font-bold mb-2">{{ __('messages.order_success') ?? 'سفارش شما با موفقیت ثبت شد' }}</h1>
    <p class="mb-6" style="color: var(--text-secondary);">{{ __('messages.order_number') ?? 'شماره سفارش' }}: #{{ $order->id }}</p>
    <a href="{{ route('dashboard.orders') }}" class="neu-btn neu-btn-primary">{{ __('messages.my_orders') }}</a>
</div>
@endsection
