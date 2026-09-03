@extends('layouts.site.app')

@section('title', __('messages.my_orders'))

@section('content')
<div class="grid md:grid-cols-4 gap-6">
    <div class="neu-card p-4 h-fit sticky top-24">
        @include('site.dashboard._sidebar')
    </div>

    <div class="md:col-span-3 space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold" style="color: var(--text-primary);">{{ __('messages.my_orders') }}</h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold" style="color: var(--text-secondary);">← {{ __('messages.back') }}</a>
        </div>

        @if($orders->isEmpty())
            <div class="neu-card text-center py-16">
                <div class="w-20 h-20 mx-auto grid place-items-center mb-5 rounded-3xl" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                    <svg class="w-9 h-9" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="text-sm font-bold mb-2" style="color: var(--text-primary);">{{ __('messages.no_orders_yet') }}</p>
                <p class="text-xs mb-4" style="color: var(--text-secondary);">{{ __('messages.start_shopping') }}</p>
                <a href="{{ localizedRoute('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold rounded-full transition-all hover:scale-[1.02]" style="background: var(--accent); color: var(--accent-contrast);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    {{ __('messages.view_products') }}
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($orders as $order)
                    <div class="neu-card p-5 transition-all duration-200 hover:translate-y-[-1px]">
                        {{-- Header: order number + date + badges --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold" style="color: var(--text-primary);">{{ __('messages.order_number') }}{{ $order->id }}</p>
                                    <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ show_date($order->created_at, 'd F Y - H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($order->status)
                                    @php
                                        $badgeColor = match($order->status['badge'] ?? '') {
                                            'success' => 'var(--success)',
                                            'warning' => '#f59e0b',
                                            'danger' => 'var(--danger)',
                                            default => 'var(--text-secondary)',
                                        };
                                    @endphp
                                    <span class="text-[11px] px-2.5 py-1 rounded-full font-bold" style="background: color-mix(in srgb, {{ $badgeColor }} 12%, transparent); color: {{ $badgeColor }};">{{ $order->status['title'] }}</span>
                                @endif
                                @if($order->shippingStatus)
                                    <span class="text-[11px] px-2.5 py-1 rounded-full font-bold text-white" style="background: {{ $order->shippingStatus->color }};">{{ $order->shippingStatus->title }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Footer: details + price + actions --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3" style="border-top: 1px solid color-mix(in srgb, var(--text-primary) 8%, transparent);">
                            <div class="flex items-center gap-3 text-xs" style="color: var(--text-secondary);">
                                @if($order->shippingMethod)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25m-2.25 0h-2.25m0 0v-.958c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.958m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        {{ $order->shippingMethod->title }}
                                    </span>
                                @endif
                                @if($order->bank)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5Z"/></svg>
                                        {{ $order->bank->title }}
                                    </span>
                                @endif
                                <span>{{ $order->items->count() }} {{ __('messages.product') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="mono font-black text-base" style="color: var(--accent);" dir="ltr">{{ number_format($order->payment_price) }} <span class="text-xs font-bold">{{ __('messages.toman') }}</span></span>
                                <a href="{{ route('dashboard.order-detail', $order->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-full transition-all hover:scale-[1.02]" style="color: var(--accent); border: 1px solid color-mix(in srgb, var(--accent) 20%, transparent);">
                                    {{ __('messages.details') }}
                                </a>
                                @if($order->order_status === 'paid')
                                    <a href="{{ route('dashboard.order-factor', $order->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-full transition-all hover:scale-[1.02]" style="color: var(--success); border: 1px solid color-mix(in srgb, var(--success) 20%, transparent);">
                                        📄 {{ __('messages.invoice') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
