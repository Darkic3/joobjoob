@extends('layouts.site.app')

@section('title', __('messages.order_detail') . ' #' . $order->id)

@section('content')
<div class="grid md:grid-cols-4 gap-6">

    {{-- سایدبار --}}
        <div class="neu-card p-4 h-fit sticky top-24">
            @include('site.dashboard._sidebar')
        </div>

    <div class="md:col-span-3 space-y-5">

        {{-- هدر سفارش --}}
        <div class="neu-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 12%, transparent);">
                        <svg class="w-6 h-6" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-black" style="color: var(--text-primary);">سفارش #{{ $order->id }}</h1>
                        <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ show_date($order->created_at, 'd F Y - H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($order->status)
                        @php
                            $badgeColor = match($order->status['badge'] ?? '') {
                                'success' => 'var(--success)',
                                'warning' => '#f59e0b',
                                'danger' => 'var(--danger)',
                                default => 'var(--text-secondary)',
                            };
                        @endphp
                        <span class="text-xs px-3 py-1.5 rounded-full font-bold" style="background: color-mix(in srgb, {{ $badgeColor }} 12%, transparent); color: {{ $badgeColor }};">{{ $order->status['title'] }}</span>
                    @endif
                    @if($order->shippingStatus)
                        <span class="text-xs px-3 py-1.5 rounded-full font-bold text-white" style="background: {{ $order->shippingStatus->color }};">{{ $order->shippingStatus->title }}</span>
                    @endif
                    @if($order->order_status === 'paid')
                        <a href="{{ route('dashboard.order-factor', $order->id) }}" target="_blank" class="text-xs px-3 py-1.5 rounded-full font-bold" style="background: color-mix(in srgb, var(--success) 12%, transparent); color: var(--success);">📄 فاکتور</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- اطلاعات سفارش + آدرس --}}
        <div class="grid sm:grid-cols-2 gap-5">

            {{-- اطلاعات سفارش --}}
            <div class="neu-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color: var(--text-primary);">اطلاعات سفارش</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                        <span class="text-xs" style="color: var(--text-secondary);">شماره سفارش</span>
                        <span class="text-sm font-bold mono" dir="ltr">#{{ $order->id }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                        <span class="text-xs" style="color: var(--text-secondary);">تاریخ ثبت</span>
                        <span class="text-sm font-semibold" style="color: var(--text-primary);">{{ show_date($order->created_at, 'd F Y - H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                        <span class="text-xs" style="color: var(--text-secondary);">روش ارسال</span>
                        <span class="text-sm font-semibold" style="color: var(--text-primary);">{{ $order->shippingMethod?->title ?? '—' }}</span>
                    </div>
                    @if($order->shipping_price > 0)
                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                            <span class="text-xs" style="color: var(--text-secondary);">هزینه ارسال</span>
                            <span class="text-sm font-semibold" style="color: var(--text-primary);">{{ format_price($order->shipping_price) }} تومان</span>
                        </div>
                    @elseif($order->shippingMethod)
                        <div class="flex items-center justify-between py-2" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 6%, transparent);">
                            <span class="text-xs" style="color: var(--text-secondary);">هزینه ارسال</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: color-mix(in srgb, var(--success) 12%, transparent); color: var(--success);">رایگان</span>
                        </div>
                    @endif
                    @if($order->post_code)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-xs" style="color: var(--text-secondary);">کد پیگیری پستی</span>
                            <span class="text-sm font-bold mono" dir="ltr">{{ $order->post_code }}</span>
                        </div>
                    @endif
                    @if($order->bankTrackingCode)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-xs" style="color: var(--text-secondary);">کد پیگیری بانکی</span>
                            <span class="text-sm font-bold mono" dir="ltr">{{ $order->bankTrackingCode }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- آدرس تحویل --}}
            <div class="neu-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color: var(--text-primary);">آدرس تحویل</h3>
                </div>
                @if($order->address)
                    @php
                        $addr = $order->address;
                        $stateName = $order->state?->name ?? ($addr['state'] ?? '');
                        $cityName = $order->city?->name ?? ($addr['city'] ?? '');
                        $street = $addr['address'] ?? '';
                        $postal = $addr['postal_code'] ?? '';
                        $mobile = $addr['receiptor_mobile'] ?? '';
                    @endphp
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                                <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px]" style="color: var(--text-secondary);">تحویل‌گیرنده</p>
                                <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $order->receiptor_full_name ?? '—' }}</p>
                            </div>
                        </div>
                        @if($stateName || $cityName)
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px]" style="color: var(--text-secondary);">استان و شهر</p>
                                    <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ $stateName }} — {{ $cityName }}</p>
                                </div>
                            </div>
                        @endif
                        @if($street)
                            <div class="flex items-start gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px]" style="color: var(--text-secondary);">آدرس</p>
                                    <p class="text-sm leading-relaxed" style="color: var(--text-primary);">{{ $street }}</p>
                                </div>
                            </div>
                        @endif
                        @if($postal)
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px]" style="color: var(--text-secondary);">کدپستی</p>
                                    <p class="text-sm font-bold mono" dir="ltr">{{ $postal }}</p>
                                </div>
                            </div>
                        @endif
                        @if($mobile)
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px]" style="color: var(--text-secondary);">موبایل</p>
                                    <p class="text-sm font-bold mono" dir="ltr">{{ $mobile }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm" style="color: var(--text-secondary);">آدرسی ثبت نشده</p>
                @endif
            </div>
        </div>

        {{-- اطلاعات کارت به کارت (قابل ویرایش) --}}
        @if($order->isCardToCard)
            <div class="neu-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5Z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color: var(--text-primary);">اطلاعات پرداخت کارت به کارت</h3>
                </div>

                <form action="{{ route('dashboard.order-receipt.update', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="text-xs font-bold mb-1 block" style="color: var(--text-secondary);">کد پیگیری <span class="text-rose-500">*</span></label>
                        <input type="text" name="receipt_tracking_code" value="{{ $order->receipt_tracking_code }}" inputmode="numeric" maxlength="16" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               placeholder="کد پیگیری واریزی"
                               class="w-full px-4 py-2.5 rounded-xl text-sm font-bold transition-all outline-none"
                               style="background: color-mix(in srgb, var(--text-primary) 4%, transparent); color: var(--text-primary); border: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent);"
                               dir="ltr" required>
                        @error('receipt_tracking_code')<p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold mb-1 block" style="color: var(--text-secondary);">شماره کارت واریزکننده <span class="text-[10px] font-normal">(اختیاری)</span></label>
                        <input type="text" name="receipt_payer_card" value="{{ $order->receipt_payer_card }}" inputmode="numeric" maxlength="16" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               placeholder="شماره کارت واریزکننده"
                               class="w-full px-4 py-2.5 rounded-xl text-sm font-bold transition-all outline-none"
                               style="background: color-mix(in srgb, var(--text-primary) 4%, transparent); color: var(--text-primary); border: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent);"
                               dir="ltr">
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-black text-white transition-all hover:scale-[1.01]"
                            style="background: var(--accent);">
                        ذخیره اطلاعات
                    </button>
                </form>
            </div>
        @endif

        {{-- اقلام سفارش --}}
        <div class="neu-card">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                    <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-sm font-bold" style="color: var(--text-primary);">اقلام سفارش ({{ $order->items->count() }})</h3>
            </div>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 hover:translate-y-[-1px]" style="background: color-mix(in srgb, var(--text-primary) 3%, transparent);">
                        <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0" style="background: color-mix(in srgb, var(--text-primary) 5%, transparent);">
                            @if($item->product?->images?->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full grid place-items-center">
                                    <svg class="w-6 h-6" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate" style="color: var(--text-primary);">{{ $item->product?->trans('name') ?? $item->product?->name ?? 'محصول حذف شده' }}</p>
                            @if($item->productVariant)
                                <p class="text-[10px] mt-0.5" style="color: var(--text-secondary);">{{ $item->productVariant->label ?? '' }}</p>
                            @endif
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-[10px]" style="color: var(--text-secondary);">تعداد: {{ $item->quantity }}</span>
                                @if($item->weight)
                                    <span class="text-[10px]" style="color: var(--text-secondary);">{{ format_price($item->weight) }} گرم</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-left shrink-0">
                            <p class="text-sm font-black mono" style="color: var(--accent);" dir="ltr">{{ format_price($item->price * $item->quantity) }}</p>
                            <p class="text-[10px]" style="color: var(--text-secondary);">تومان</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- خلاصه مالی --}}
        <div class="neu-card">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                    <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold" style="color: var(--text-primary);">خلاصه مالی</h3>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs" style="color: var(--text-secondary);">جمع کل محصولات</span>
                    <span class="text-sm font-bold mono" style="color: var(--text-primary);" dir="ltr">{{ format_price($order->total_price) }} <span class="text-[10px]">تومان</span></span>
                </div>
                @if($order->discount_price > 0)
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs" style="color: var(--success);">تخفیف</span>
                        <span class="text-sm font-bold mono" style="color: var(--success);" dir="ltr">-{{ format_price($order->discount_price) }} <span class="text-[10px]">تومان</span></span>
                    </div>
                @endif
                @if($order->shipping_price > 0)
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs" style="color: var(--text-secondary);">هزینه ارسال</span>
                        <span class="text-sm font-bold mono" style="color: var(--text-primary);" dir="ltr">{{ format_price($order->shipping_price) }} <span class="text-[10px]">تومان</span></span>
                    </div>
                @endif
                <div class="flex items-center justify-between py-3 mt-2" style="border-top: 2px solid color-mix(in srgb, var(--accent) 15%, transparent);">
                    <span class="text-sm font-bold" style="color: var(--text-primary);">مبلغ قابل پرداخت</span>
                    <span class="text-lg font-black mono" style="color: var(--accent);" dir="ltr">{{ format_price($order->payment_price) }} <span class="text-xs font-bold">تومان</span></span>
                </div>
            </div>
        </div>

        {{-- تاریخچه --}}
        @if($order->histories->count())
            <div class="neu-card">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: color-mix(in srgb, var(--accent) 10%, transparent);">
                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold" style="color: var(--text-primary);">تاریخچه سفارش</h3>
                </div>
                <div class="space-y-3">
                    @foreach($order->histories as $history)
                        <div class="flex items-center gap-3 p-3 rounded-xl" style="background: color-mix(in srgb, var(--text-primary) 3%, transparent);">
                            <div class="w-2 h-2 rounded-full shrink-0" style="background: var(--accent);"></div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ $history->status_name }}</p>
                                <p class="text-[10px] mt-0.5" style="color: var(--text-secondary);">{{ show_date($history->created_at, 'd/m/Y H:i') }}</p>
                            </div>
                            @if($history->shippingStatus)
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold text-white" style="background: {{ $history->shippingStatus->color }};">{{ $history->shippingStatus->title }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- دکمه بازگشت --}}
        <a href="{{ route('dashboard.orders') }}" class="inline-flex items-center gap-2 text-sm font-bold transition-all duration-200 hover:opacity-80" style="color: var(--accent);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            بازگشت به لیست سفارشات
        </a>

    </div>
</div>
@endsection
