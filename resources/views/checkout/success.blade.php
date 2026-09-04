@extends('layouts.site.app')

@section('title', __('messages.place_order'))

@section('content')
    <div class="reveal-on-scroll">
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="neu-card text-center py-16 px-8 max-w-lg w-full">
                <div class="neu-icon-circle w-24 h-24 mx-auto grid place-items-center mb-6" style="background: color-mix(in srgb, var(--success) 15%, transparent);">
                    <svg class="w-12 h-12" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-black mb-3" style="color: var(--success);">پرداخت شما با موفقیت انجام شد!</h2>
                <p class="text-sm mb-2" style="color: var(--text-secondary);">
                    شماره سفارش: <span class="mono font-bold" dir="ltr">#{{ $order->id }}</span>
                </p>
                <p class="text-sm mb-6" style="color: var(--text-secondary);">
                    تاریخ: {{ show_date($order->created_at, 'd F Y - H:i') }}
                </p>

                <div class="neu-flat-sm rounded-2xl p-4 mb-6 text-right">
                    <h3 class="font-bold text-sm mb-3" style="color: var(--text-primary);">جزئیات سفارش</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">مبلغ کل</span>
                            <span class="mono font-bold" dir="ltr">{{ format_price($order->payment_price) }} {{ __('messages.toman') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">هزینه ارسال</span>
                            <span class="mono font-bold" dir="ltr">{{ format_price($order->shipping_price) }} {{ __('messages.toman') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">وضعیت</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                  style="background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success);">
                                پرداخت شده
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 justify-center flex-wrap">
                    <a href="{{ localizedRoute('dashboard.order-detail', $order->id) }}"
                       class="neu-btn px-5 py-3 text-sm font-bold rounded-full">
                        {{ __('messages.view_details') }}
                    </a>
                    <a href="{{ localizedRoute('home') }}"
                       class="neu-btn neu-btn-primary px-5 py-3 text-sm font-bold rounded-full">
                        {{ __('messages.home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
