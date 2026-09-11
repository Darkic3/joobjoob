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

        {{-- کارت ایمیل حساب کاربری — مشاهده / ثبت / ویرایش / حذف --}}
        <style>
            .email-delete-btn {
                display: inline-flex;
                align-items: center;
                gap: .55rem;
                padding: 4px;
                padding-inline-end: 14px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 800;
                white-space: nowrap;
                cursor: pointer;
                color: #ef4444;
                background: color-mix(in srgb, #ef4444 7%, transparent);
                border: 1px solid color-mix(in srgb, #ef4444 30%, transparent);
                transition: background .25s ease, color .25s ease, box-shadow .25s ease, transform .18s ease, border-color .25s ease;
            }
            .email-delete-btn:hover {
                background: linear-gradient(135deg, #f87171, #ef4444);
                border-color: transparent;
                color: #fff;
                box-shadow: 0 6px 16px rgba(239, 68, 68, .4);
                transform: translateY(-1px);
            }
            .email-delete-btn:active { transform: scale(.96); box-shadow: none; }
            .email-delete-icon {
                display: grid;
                place-items: center;
                width: 28px;
                height: 28px;
                border-radius: 999px;
                background: color-mix(in srgb, #ef4444 13%, transparent);
                transition: background .25s ease;
            }
            .email-delete-btn:hover .email-delete-icon { background: rgba(255, 255, 255, .22); }
        </style>
        <div class="neu-card">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl grid place-items-center shrink-0"
                     style="background: color-mix(in srgb, var(--accent) 12%, transparent); color: var(--accent);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold" style="color: var(--text-primary);">ایمیل</h2>
                    <p class="text-xs" style="color: var(--text-secondary);">
                        @if(auth()->user()->email)
                            ایمیل فعلی شما — برای تغییر، ایمیل جدید را وارد و ذخیره کنید
                        @else
                            هنوز ایمیلی ثبت نکرده‌اید
                        @endif
                    </p>
                </div>
            </div>

            @if(auth()->user()->email)
                <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl mb-3"
                     style="background: color-mix(in srgb, var(--success) 7%, transparent); border: 1px solid color-mix(in srgb, var(--success) 18%, transparent);">
                    <span class="text-sm font-bold truncate min-w-0" style="color: var(--text-primary);" dir="ltr">{{ auth()->user()->email }}</span>
                    <form action="{{ route('dashboard.email.destroy') }}" method="POST" class="shrink-0"
                          onsubmit="return confirm('ایمیل شما حذف شود؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="email-delete-btn">
                            <span class="email-delete-icon" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </span>
                            حذف ایمیل
                        </button>
                    </form>
                </div>
            @endif

            <form action="{{ route('dashboard.email.update') }}" method="POST" class="flex flex-col sm:flex-row gap-2.5">
                @csrf
                @method('PUT')
                <input type="email" name="email" required maxlength="190" dir="ltr"
                       value="{{ old('email', auth()->user()->email) }}"
                       placeholder="example@mail.com"
                       class="neu-input flex-1 text-sm py-3"
                       @error('email') style="border-color: var(--danger);" @enderror>
                <button type="submit"
                        class="neu-btn neu-btn-primary px-6 py-3 text-xs font-bold rounded-full shrink-0 transition-all duration-200 hover:scale-[1.03] active:scale-[0.98]">
                    {{ auth()->user()->email ? 'ویرایش ایمیل' : 'ثبت ایمیل' }}
                </button>
            </form>
            @error('email')
                <p class="text-[11px] mt-2 font-bold" style="color: var(--danger);">{{ $message }}</p>
            @enderror
        </div>

        {{-- کارت عضویت در خبرنامه — سوییچ روشن/خاموش (غیرفعال = خاکستری) --}}
        @php $newsletterOn = ($newsletterEnabled ?? false); @endphp
        <div class="neu-card" @unless($newsletterOn) title="خبرنامه غیرفعال است" @endunless>
            <div class="flex items-center gap-4" @unless($newsletterOn) style="opacity: 0.55; filter: grayscale(1);" @endunless>
                <div class="w-10 h-10 rounded-2xl grid place-items-center shrink-0"
                     style="background: color-mix(in srgb, var(--accent) 12%, transparent); color: var(--accent);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path d="M6 6h.008v.008H6V6Z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold" style="color: var(--text-primary);">عضویت در خبرنامه</h2>
                    <p class="text-xs mt-0.5" style="color: var(--text-secondary);">
                        <span id="newsletterStatus">{{ ($newsletterEnabled ?? false) && ($newsletterSubscribed ?? false) ? 'عضو هستید' : 'عضو نیستید' }}</span>
                        @unless($newsletterEnabled ?? false)
                            <span> — خبرنامه در حال حاضر غیرفعال است</span>
                        @endunless
                    </p>
                </div>
                @if($newsletterEnabled ?? false)
                    <button type="button" id="newsletterToggle"
                            data-subscribed="{{ ($newsletterSubscribed ?? false) ? '1' : '0' }}"
                            onclick="toggleNewsletterSubscription()"
                            role="switch" aria-checked="{{ ($newsletterSubscribed ?? false) ? 'true' : 'false' }}"
                            aria-label="عضویت در خبرنامه"
                            class="relative w-14 h-8 rounded-full transition-colors duration-300 shrink-0"
                            style="background: {{ ($newsletterSubscribed ?? false) ? 'var(--success)' : 'color-mix(in srgb, var(--text-primary) 15%, transparent)' }};">
                        <span id="newsletterKnob"
                              class="absolute top-1 w-6 h-6 rounded-full bg-white shadow transition-all duration-300"
                              style="inset-inline-start: {{ ($newsletterSubscribed ?? false) ? 'calc(100% - 1.75rem)' : '0.25rem' }};"></span>
                    </button>
                @else
                    <button type="button" disabled aria-checked="false" aria-label="عضویت در خبرنامه (غیرفعال)"
                            title="خبرنامه غیرفعال است"
                            class="relative w-14 h-8 rounded-full shrink-0 cursor-not-allowed"
                            style="background: color-mix(in srgb, var(--text-primary) 15%, transparent);">
                        <span class="absolute top-1 w-6 h-6 rounded-full bg-white shadow"
                              style="inset-inline-start: 0.25rem;"></span>
                    </button>
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
function toggleNewsletterSubscription() {
    var btn = document.getElementById('newsletterToggle');
    if (!btn || btn.disabled) return;
    btn.disabled = true;

    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('{{ localizedRoute('dashboard.newsletter.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.content : '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function (r) { return r.json().then(function (d) { return { status: r.status, body: d }; }); })
    .then(function (res) {
        if (res.body && res.body.success) {
            applyNewsletterState(!!res.body.subscribed);
        }
        if (typeof showToast === 'function' && res.body && res.body.message) {
            showToast(res.body.message, res.body.success ? 'success' : 'error');
        }
    })
    .catch(function () {
        if (typeof showToast === 'function') showToast('خطایی رخ داده است؛ لطفاً دوباره تلاش کنید.', 'error');
    })
    .finally(function () { btn.disabled = false; });
}

function applyNewsletterState(on) {
    var btn = document.getElementById('newsletterToggle');
    var knob = document.getElementById('newsletterKnob');
    var status = document.getElementById('newsletterStatus');
    if (btn) {
        btn.setAttribute('data-subscribed', on ? '1' : '0');
        btn.setAttribute('aria-checked', on ? 'true' : 'false');
        btn.style.background = on ? 'var(--success)' : 'color-mix(in srgb, var(--text-primary) 15%, transparent)';
    }
    if (knob) knob.style.setProperty('inset-inline-start', on ? 'calc(100% - 1.75rem)' : '0.25rem');
    if (status) status.textContent = on ? 'عضو هستید' : 'عضو نیستید';
}

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
