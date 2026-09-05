@extends('layouts.site.app')

@section('title', __('messages.dashboard'))

@section('content')
<div class="grid md:grid-cols-4 gap-6">
    {{-- سایدبار --}}
    <div class="md:col-span-1">
        <div class="neu-card p-4 h-fit sticky top-24">
            @include('site.dashboard._sidebar')
        </div>
    </div>

    {{-- محتوای اصلی --}}
    <div class="md:col-span-3 space-y-5">

        {{-- کارت اطلاعات کاربر --}}
        <div class="neu-card">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl grid place-items-center shrink-0"
                     style="background: color-mix(in srgb, var(--accent) 12%, transparent); color: var(--accent);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold" style="color: var(--text-primary);">{{ __('messages.account_info') }}</h2>
                    <p class="text-xs" style="color: var(--text-secondary);">{{ __('messages.welcome_back') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="p-3 rounded-xl" style="background: color-mix(in srgb, var(--text-primary) 4%, transparent);">
                    <p class="text-[10px] mb-1" style="color: var(--text-secondary);">نام</p>
                    <p class="text-sm font-bold" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                </div>
                @if(auth()->user()->mobile)
                    <div class="p-3 rounded-xl" style="background: color-mix(in srgb, var(--text-primary) 4%, transparent);">
                        <p class="text-[10px] mb-1" style="color: var(--text-secondary);">موبایل</p>
                        <p class="text-sm font-bold" style="color: var(--text-primary);" dir="ltr">{{ auth()->user()->mobile }}</p>
                    </div>
                @endif
                @if(auth()->user()->email)
                    <div class="p-3 rounded-xl" style="background: color-mix(in srgb, var(--text-primary) 4%, transparent);">
                        <p class="text-[10px] mb-1" style="color: var(--text-secondary);">ایمیل</p>
                        <p class="text-sm font-bold truncate" style="color: var(--text-primary);" dir="ltr">{{ auth()->user()->email }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- کارت کد تخفیف خوشامدگویی --}}
        @php
            $welcomeDiscount = \App\Models\Discount::where('user_id', auth()->id())
                ->where('title', 'تخفیف خوشامدگویی')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
        @endphp
        @if($welcomeDiscount && !$welcomeDiscount->hasReachedLimit(auth()->id()))
            <div class="neu-card relative overflow-hidden">
                <div aria-hidden="true"
                     style="position: absolute; inset-inline-end: -30px; top: -30px; width: 120px; height: 120px; border-radius: 999px;
                            background: radial-gradient(circle, color-mix(in srgb, var(--success) 12%, transparent), transparent 70%); pointer-events: none;"></div>
                <div class="relative flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl grid place-items-center shrink-0"
                         style="background: color-mix(in srgb, var(--success) 12%, transparent); color: var(--success);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"/>
                            <rect x="2" y="7" width="20" height="5"/>
                            <line x1="12" y1="22" x2="12" y2="7"/>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold" style="color: var(--text-primary);">🎁 کد تخفیف خوشامدگویی شما</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-black tracking-wider"
                                  style="background: color-mix(in srgb, var(--success) 10%, transparent); color: var(--success);"
                                  dir="ltr">{{ $welcomeDiscount->code }}</span>
                            <button type="button"
                                    onclick="copyToClipboard('{{ $welcomeDiscount->code }}', this)"
                                    class="text-[11px] font-bold px-2 py-1 rounded-lg transition-all hover:opacity-80"
                                    style="background: color-mix(in srgb, var(--accent) 10%, transparent); color: var(--accent);">
                                کپی
                            </button>
                        </div>
                        <p class="text-[11px] mt-2" style="color: var(--text-secondary);">
                            {{ $welcomeDiscount->amount }}٪ تخفیف — تا {{ show_date($welcomeDiscount->expires_at, 'd F Y') }} معتبر
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- آخرین سفارشات --}}
        <div class="neu-card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl grid place-items-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h2 class="font-bold" style="color: var(--text-primary);">{{ __('messages.my_orders') }}</h2>
                </div>
                @if($orders->isNotEmpty())
                    <a href="{{ route('dashboard.orders') }}" class="text-xs font-bold" style="color: var(--accent);">مشاهده همه →</a>
                @endif
            </div>

            @if($orders->isNotEmpty())
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <a href="{{ route('dashboard.order-detail', $order->id) }}"
                           class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 hover:translate-y-[-1px]"
                           style="background: color-mix(in srgb, var(--text-primary) 3%, transparent); text-decoration: none; color: inherit;">
                            <div class="w-10 h-10 rounded-xl grid place-items-center shrink-0" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);">
                                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate" style="color: var(--text-primary);">سفارش #{{ $order->id }}</p>
     
                                <p class="text-[11px]" style="color: var(--text-secondary);">{{ show_date($order->created_at, "d F Y - H:i") }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($order->status)
                                    @php $badgeColor = match($order->status["badge"] ?? "") { "success" => "var(--success)", "warning" => "#f59e0b", "danger" => "var(--danger)", default => "var(--text-secondary)" }; @endphp
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background: color-mix(in srgb, {{ $badgeColor }} 12%, transparent); color: {{ $badgeColor }};">{{ $order->status["title"] }}</span>
                                @endif
                                <span class="text-sm font-black mono" style="color: var(--accent);" dir="ltr">{{ format_price($order->payment_price) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <div class="w-16 h-16 rounded-3xl grid place-items-center mx-auto mb-4" style="background: color-mix(in srgb, var(--text-secondary) 8%, transparent);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-sm font-bold mb-1" style="color: var(--text-primary);">هنوز سفارشی ثبت نکرده‌اید</p>
                    <p class="text-xs mb-3" style="color: var(--text-secondary);">برای شروع خرید، محصولات ما را ببینید</p>
                    <a href="{{ localizedRoute("products.index") }}" class="neu-btn neu-btn-primary px-5 py-2.5 text-xs font-bold rounded-full">مشاهده محصولات</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<script>
function copyToClipboard(text, btn) {
    var originalText = btn.textContent;
    function showCopied() {
        btn.textContent = '✓ کپی شد';
        btn.style.color = 'var(--success)';
        setTimeout(function() {
            btn.textContent = originalText;
            btn.style.color = '';
        }, 2000);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(showCopied).catch(function() {
            fallbackCopy(text, showCopied);
        });
    } else {
        fallbackCopy(text, showCopied);
    }
    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        cb();
    }
}
</script>
