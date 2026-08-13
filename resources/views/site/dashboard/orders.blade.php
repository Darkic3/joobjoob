@extends('layouts.site.app')
@section('title', __('messages.my_orders'))
@section('content')
<h1 class="text-xl font-bold mb-6">{{ __('messages.my_orders') }}</h1>
<div class="space-y-3">
    @foreach($orders as $order)
        <div class="neu-card flex justify-between items-center text-sm">
            <span>#{{ $order->id }}</span>
            <span>{{ show_date($order->created_at, 'Y/m/d') }}</span>
            <span>{{ number_format($order->total) }}</span>
            <span class="neu-badge neu-badge-success">{{ $order->status }}</span>
        </div>
    @endforeach
</div>
{{ $orders->links() }}
@endsection
