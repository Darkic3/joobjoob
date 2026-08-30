@extends('layouts.admin.app')

@section('tab-name', __('admin.orders.detail_title') . ' #' . $order->id)

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.orders.detail_title') }} <span class="text-indigo-600">#{{ $order->id }}</span></h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.orders.date_full') }}: {{ show_date($order->created_at, 'd F Y - H:i') }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-5 py-2 rounded-xl transition-all duration-200">
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                {{ __('admin.orders.back') }}
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- اطلاعات سفارش --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    {{ __('admin.orders.order_info') }}
                </h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.customer') }}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->user?->name }} ({{ $order->user?->mobile }})</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.order_status') }}</span>
                        <form action="{{ route('admin.orders.change-order-status', $order->id) }}" method="POST">
                            @csrf
                            <select name="order_status" onchange="this.form.submit()" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                                @foreach(['paying','pending_confirmation','paid','deposit_paid','unpaid','cancelled'] as $status)
                                    <option value="{{ $status }}" {{ $order->order_status === $status ? 'selected' : '' }}>{{ __('admin.orders.' . $status) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.shipping_status') }}</span>
                        <form action="{{ route('admin.orders.change-shipping-status', $order->id) }}" method="POST">
                            @csrf
                            <select name="shipping_status_id" onchange="this.form.submit()" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none">
                                @foreach($shippingStatuses as $status)
                                    <option value="{{ $status->id }}" {{ $order->shipping_status_id == $status->id ? 'selected' : '' }}>{{ $status->title }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.shipping_method') }}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->shippingMethod?->title ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.payment_gateway') }}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->bank?->title ?? '—' }}</span>
                    </div>

                    {{-- کد پیگیری پستی --}}
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.tracking_code') }}</span>
                        <form action="{{ route('admin.orders.post-code') }}" method="POST" class="flex items-center gap-1.5">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <input type="text" name="post_code" value="{{ $order->post_code }}" placeholder="{{ __('admin.orders.tracking_code_placeholder') }}"
                                   class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs text-gray-800 dark:text-gray-200 w-28 focus:ring-2 focus:ring-indigo-500 outline-none" dir="ltr">
                            <button class="text-xs font-semibold px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors">{{ __('admin.orders.save_tracking') }}</button>
                        </form>
                    </div>

                    {{-- فیش واریزی --}}
                    @if($order->payment_receipt)
                        <div class="py-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm block mb-2">{{ __('admin.orders.payment_receipt') }}</span>
                            <a href="{{ asset('storage/' . $order->payment_receipt) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->payment_receipt) }}" alt="فیش" class="w-20 h-20 rounded-xl object-cover border border-gray-200 dark:border-gray-600">
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- آدرس تحویل --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    </div>
                    {{ __('admin.orders.delivery_address') }}
                </h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.name') }}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->receiptor_full_name }}</span>
                    </div>
                    @if($order->address)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.state') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->address['state'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.city') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $order->address['city'] ?? '—' }}</span>
                        </div>
                        <div class="py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400 text-xs block mb-1">{{ __('admin.orders.address') }}</span>
                            <p class="text-gray-800 dark:text-gray-200 leading-relaxed">{{ $order->address['address'] ?? '—' }}</p>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.postal_code') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $order->address['postal_code'] ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.mobile') }}</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $order->address['receiptor_mobile'] ?? '—' }}</span>
                        </div>
                    @endif
                    @if($order->user_description)
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-1">{{ __('admin.orders.user_desc') }}</p>
                            <p class="text-sm text-amber-800 dark:text-amber-300">{{ $order->user_description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- اقلام سفارش --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    {{ __('admin.orders.items') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.product') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.variant') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.quantity') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.weight') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.price') }}</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.row_total') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($order->items as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $item->product?->trans('name') ?? $item->product?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item->productVariant?->label ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $item->quantity }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item->weight ? number_format($item->weight) . 'g' : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200" dir="ltr">{{ number_format($item->price) }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-indigo-600 dark:text-indigo-400" dir="ltr">{{ number_format($item->price * $item->quantity) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- خلاصه مالی --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mt-6 max-w-sm ms-auto">
            <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                {{ __('admin.orders.financial_summary') }}
            </h2>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.subtotal') }}</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ number_format($order->total_price) }} {{ __('messages.toman') }}</span>
                </div>
                @if($order->discount_price > 0)
                    <div class="flex justify-between">
                        <span class="text-emerald-600 dark:text-emerald-400">{{ __('admin.orders.discount') }}</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400" dir="ltr">-{{ number_format($order->discount_price) }} {{ __('messages.toman') }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('admin.orders.shipping_cost') }}</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $order->shipping_price > 0 ? number_format($order->shipping_price) . ' ' . __('messages.toman') : __('admin.orders.free_shipping') }}</span>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3 flex justify-between">
                    <span class="font-bold text-gray-800 dark:text-white">{{ __('admin.orders.final_total') }}</span>
                    <span class="font-black text-lg text-indigo-600 dark:text-indigo-400" dir="ltr">{{ number_format($order->payment_price) }} {{ __('messages.toman') }}</span>
                </div>
                @if($order->bankTrackingCode)
                    <div class="pt-2">
                        <span class="text-xs text-gray-400">{{ __('admin.orders.tracking_code') }}: {{ $order->bankTrackingCode }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- تاریخچه --}}
        @if($order->histories->count())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        {{ __('admin.orders.history') }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-start">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.date') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.order_status') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('admin.orders.shipping_status') }}</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($order->histories as $h)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ show_date($h->created_at, 'd/m/Y H:i') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $h->status_name }}</td>
                                <td class="px-6 py-3">
                                    @if($h->shippingStatus)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-white" style="background:{{ $h->shippingStatus->color }}">{{ $h->shippingStatus->title }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
