@extends('layouts.site.app')

@section('title', __('messages.dashboard'))

@section('content')
<div class="grid md:grid-cols-4 gap-6">
    <aside class="neu-card h-fit space-y-2">
        <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded-lg font-semibold" style="color: var(--accent);">{{ __('messages.account_info') }}</a>
        <a href="{{ route('dashboard.orders') }}" class="block px-4 py-2 rounded-lg hover:opacity-80">{{ __('messages.my_orders') }}</a>
        <a href="{{ route('dashboard.wishlist') }}" class="block px-4 py-2 rounded-lg hover:opacity-80">{{ __('messages.wishlist') }}</a>
        <a href="{{ route('dashboard.addresses') }}" class="block px-4 py-2 rounded-lg hover:opacity-80">{{ __('messages.addresses') }}</a>

        <form action="{{ localizedRoute('logout') }}" method="POST" class="pt-2 mt-2" style="border-top: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent);">
            @csrf
            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-80" style="color: var(--danger);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
                </svg>
                {{ __('messages.logout') }}
            </button>
        </form>
    </aside>

    <div class="md:col-span-3 space-y-6">
        <div class="neu-card">
            <h2 class="font-bold mb-2" style="color: var(--text-primary);">{{ __('messages.account_info') }}</h2>
            <p class="text-sm" style="color: var(--text-secondary);">{{ auth()->user()->name }} — {{ auth()->user()->email }}</p>
            @if(auth()->user()->mobile)
                <p class="text-sm mt-1" style="color: var(--text-secondary);" dir="ltr">{{ auth()->user()->mobile }}</p>
            @endif
        </div>

        <div class="neu-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold" style="color: var(--text-primary);">{{ __('messages.my_orders') }}</h2>
                @if($orders->isNotEmpty())
                    <a href="{{ route('dashboard.orders') }}" class="text-xs font-bold" style="color: var(--accent);">مشاهده همه →</a>
                @endif
            </div>
            @if($orders->isNotEmpty())
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <a href="{{ route('dashboard.order-detail', $order->id) }}" class="neu-flat-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all duration-200 hover:translate-y-[-1px] block" style="text-decoration: none; color: inherit;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold" style="color: var(--text-primary);">سفارش #{{ $order->id }}</p>
                                    <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ show_date($order->created_at, 'd F Y - H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($order->shippingMethod)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full" style="background: color-mix(in srgb, var(--accent) 8%, transparent); color: var(--accent);">{{ $order->shippingMethod->title }}</span>
                                @endif
                                @if($order->status)
                                    @php
                                        $badgeColor = match($order->status['badge'] ?? '') {
                                            'success' => 'var(--success)',
                                            'warning' => '#f59e0b',
                                            'danger' => 'var(--danger)',
                                            default => 'var(--text-secondary)',
                                        };
                                    @endphp
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background: color-mix(in srgb, {{ $badgeColor }} 12%, transparent); color: {{ $badgeColor }};">{{ $order->status['title'] }}</span>
                                @endif
                                <span class="mono font-black text-sm" style="color: var(--accent);" dir="ltr">{{ number_format($order->payment_price) }}</span>
                                <svg class="w-4 h-4" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <div class="neu-icon-circle w-16 h-16 mx-auto grid place-items-center mb-4" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">
                        <svg class="w-7 h-7" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-sm font-bold mb-1" style="color: var(--text-primary);">هنوز سفارشی ثبت نکرده‌اید</p>
                    <a href="{{ localizedRoute('products.index') }}" class="text-xs font-bold inline-block mt-2" style="color: var(--accent);">مشاهده محصولات →</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
