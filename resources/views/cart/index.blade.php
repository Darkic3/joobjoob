@extends('layouts.app')

@section('title', __('messages.cart'))

@section('content')
<h1 class="text-xl font-bold mb-6">{{ __('messages.cart') }}</h1>

@if($items->isEmpty())
    <div class="neu-card text-center py-16">
        <p style="color: var(--text-secondary);" class="mb-4">🛒</p>
        <a href="{{ route('products.index') }}" class="neu-btn neu-btn-primary">{{ __('messages.products') }}</a>
    </div>
@else
    <div class="grid md:grid-cols-3 gap-8">
        <div class="md:col-span-2 space-y-4">
            @foreach($items as $item)
                <div class="neu-card flex items-center gap-4">
                    <img src="{{ $item['image'] ?? 'https://picsum.photos/100' }}" class="w-20 h-20 rounded-xl object-cover">

                    <div class="flex-1">
                        <p class="font-semibold text-sm">{{ $item['name'] }}</p>
                        <p class="text-xs" style="color: var(--text-secondary);">
                            {{ $item['color'] }} {{ $item['size'] ? '- '.$item['size'] : '' }}
                        </p>
                        <p class="font-bold mt-1" style="color: var(--accent);">{{ number_format($item['price']) }}</p>
                    </div>

                    <form action="{{ route('cart.update', $item['rowId']) }}" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" class="neu-input w-16 text-center text-sm"
                               onchange="this.form.submit()">
                    </form>

                    <form action="{{ route('cart.remove', $item['rowId']) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="neu-btn neu-btn-icon text-sm" style="color: var(--danger);">✕</button>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- خلاصه سفارش --}}
        <div class="neu-card h-fit">
            <h3 class="font-bold mb-4">{{ __('messages.total') }}</h3>

            <form action="{{ route('cart.coupon') }}" method="POST" class="flex gap-2 mb-5">
                @csrf
                <input type="text" name="code" placeholder="{{ __('messages.coupon_code') }}" class="neu-input text-sm">
                <button type="submit" class="neu-btn text-sm">{{ __('messages.apply') }}</button>
            </form>

            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between"><span>{{ __('messages.subtotal') }}</span><span>{{ number_format($cart->subtotal()) }}</span></div>
                <div class="flex justify-between"><span>{{ __('messages.discount') }}</span><span style="color: var(--danger);">-{{ number_format($cart->discount()) }}</span></div>
            </div>

            <div class="neu-pressed p-3 flex justify-between font-bold mb-5">
                <span>{{ __('messages.total') }}</span>
                <span style="color: var(--accent);">{{ number_format($cart->total()) }}</span>
            </div>

            {{-- <a href="{{ route('checkout.index') }}" class="neu-btn neu-btn-primary w-full text-center block">
                {{ __('messages.proceed_to_checkout') }}
            </a> --}}
        </div>
    </div>
@endif
@endsection
