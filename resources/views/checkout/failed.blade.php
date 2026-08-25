@extends('layouts.site.app')

@section('title', __('messages.checkout'))

@section('content')
    <div class="reveal-on-scroll">
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="neu-card text-center py-16 px-8 max-w-lg w-full">
                <div class="neu-icon-circle w-24 h-24 mx-auto grid place-items-center mb-6" style="background: color-mix(in srgb, var(--danger) 15%, transparent);">
                    <svg class="w-12 h-12" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-black mb-3" style="color: var(--danger);">پرداخت ناموفق بود</h2>

                @if(session('error'))
                    <div class="neu-flat-sm rounded-2xl px-4 py-3 mb-4 text-sm font-bold"
                         style="background: color-mix(in srgb, var(--danger) 10%, transparent); color: var(--danger);">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-sm mb-6" style="color: var(--text-secondary);">
                    پرداخت شما تکمیل نشد. لطفاً مجدداً تلاش کنید.
                </p>

                <div class="flex gap-3 justify-center flex-wrap">
                    <a href="{{ localizedRoute('cart.index') }}"
                       class="neu-btn neu-btn-primary px-5 py-3 text-sm font-bold rounded-full">
                        بازگشت به سبد خرید
                    </a>
                    <a href="{{ localizedRoute('home') }}"
                       class="neu-btn px-5 py-3 text-sm font-bold rounded-full">
                        {{ __('messages.home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
