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
            <h2 class="font-bold mb-4">{{ __('messages.account_info') }}</h2>
            <p class="text-sm" style="color: var(--text-secondary);">{{ auth()->user()->name }} — {{ auth()->user()->email }}</p>
        </div>

        <div class="neu-card">
            <h2 class="font-bold mb-4">{{ __('messages.my_orders') }}</h2>
            <div class="space-y-3">
                @forelse($orders as $order)
                    <div class="neu-flat-sm p-4 flex justify-between items-center text-sm">
                        <span>#{{ $order->id }}</span>
                        <span>{{ number_format($order->total) }}</span>
                        <span class="neu-badge neu-badge-success">{{ $order->status['title'] ?? $order->order_status }}</span>
                    </div>
                @empty
                    <p style="color: var(--text-secondary);">—</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
