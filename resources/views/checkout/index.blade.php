@extends('layouts.app')

@section('title', __('messages.checkout'))

@section('content')
<h1 class="text-xl font-bold mb-6">{{ __('messages.checkout') }}</h1>

<div class="grid md:grid-cols-3 gap-8">
    <form action="{{ route('checkout.store') }}" method="POST" class="md:col-span-2 neu-card space-y-5">
        @csrf

        <div>
            <label class="text-sm font-semibold block mb-2">{{ __('messages.shipping_address') }}</label>
            <textarea name="address" rows="4" class="neu-input" required>{{ old('address') }}</textarea>
            @error('address') <p class="text-xs mt-1" style="color: var(--danger);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-semibold block mb-2">{{ __('messages.payment_method') }}</label>
            <div class="flex gap-3">
                <label class="neu-flat-sm px-4 py-3 flex-1 text-center cursor-pointer has-[:checked]:font-bold">
                    <input type="radio" name="payment_method" value="online" class="hidden" checked>
                    پرداخت آنلاین
                </label>
                <label class="neu-flat-sm px-4 py-3 flex-1 text-center cursor-pointer has-[:checked]:font-bold">
                    <input type="radio" name="payment_method" value="cod" class="hidden">
                    پرداخت در محل
                </label>
            </div>
        </div>

        <button type="submit" class="neu-btn neu-btn-primary w-full">{{ __('messages.place_order') }}</button>
    </form>

    <div class="neu-card h-fit">
        <h3 class="font-bold mb-4">{{ __('messages.total') }}</h3>
        <div class="space-y-2 text-sm mb-4">
            @foreach($items as $item)
                <div class="flex justify-between">
                    <span>{{ $item['name'] }} × {{ $item['qty'] }}</span>
                    <span>{{ number_format($item['price'] * $item['qty']) }}</span>
                </div>
            @endforeach
        </div>
        <div class="neu-pressed p-3 flex justify-between font-bold">
            <span>{{ __('messages.total') }}</span>
            <span style="color: var(--accent);">{{ number_format($cart->total()) }}</span>
        </div>
    </div>
</div>
@endsection
