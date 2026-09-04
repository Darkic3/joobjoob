@extends('layouts.admin.app')

@section('tab-name')
جزئیات کد تخفیف: {{ $discount->title }}
@endsection

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-4 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">جزئیات کد تخفیف</h1>
                <p class="text-gray-500 dark:text-gray-400 text-xs">{{ $discount->title }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->canAccess('admin.discounts.edit'))
                    <a href="{{ route('admin.discounts.edit', $discount) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-200 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        ویرایش
                    </a>
                @endif
                <a href="{{ route('admin.discounts.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    بازگشت
                </a>
            </div>
        </div>

        {{-- اطلاعات اصلی --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            {{-- اطلاعات پایه --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">اطلاعات پایه</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">عنوان</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->title }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">کد</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $discount->code }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">نوع</span>
                        @if($discount->type === 'percent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">{{ $discount->amount }}٪ درصد</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ number_format($discount->amount) }} تومان</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">وضعیت</span>
                        @if($discount->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">فعال</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">غیرفعال</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- محدودیت‌ها --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">محدودیت‌ها</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">حداقل خرید</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->basket_minimum_price > 0 ? number_format($discount->basket_minimum_price) . ' تومان' : 'ندارد' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">حداکثر استفاده کلی</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->count }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">حداکثر هر کاربر</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->max_usage_per_user }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">فقط خرید اول</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->first_purchase ? 'بله' : 'خیر' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">روی تخفیف‌دار</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->with_discount ? 'بله' : 'خیر' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">محدودیت نوع</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->pay_type ?? 'همه' }}</span>
                    </div>
                </div>
            </div>

            {{-- تاریخ و وضعیت --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">زمان‌بندی</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">تاریخ شروع</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->starts_at ? show_date($discount->starts_at, 'Y/m/d H:i') : '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">تاریخ انقضا</span>
                        <span class="font-semibold text-gray-800 dark:text-white">
                            @if($discount->expires_at)
                                {{ show_date($discount->expires_at, 'Y/m/d H:i') }}
                                @if($discount->expires_at->isPast())
                                    <span class="text-red-500 text-xs">(منقضی)</span>
                                @endif
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">مخصوص کاربر</span>
                        @if($discount->user)
                            <button type="button" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors cursor-pointer" data-bs-toggle="modal" data-bs-target="#userDetailModal">
                                {{ $discount->user->name }}
                            </button>
                        @else
                            <span class="font-semibold text-gray-800 dark:text-white">همه کاربران</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">تعداد استفاده</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->usage_records_count ?? $discount->usageRecords()->count() }} / {{ $discount->count }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- آیتم‌های مرتبط --}}
        @if($discount->hasPayTypeRestriction())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">آیتم‌های مرتبط ({{ $discount->pay_type }})</h3>
                <div class="flex flex-wrap gap-2">
                    @if($discount->pay_type === 'category')
                        @forelse($discount->categories as $cat)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ $cat->name }}</span>
                        @empty
                            <span class="text-gray-400 text-xs">هیچ دسته‌بندی انتخاب نشده</span>
                        @endforelse
                    @elseif($discount->pay_type === 'brand')
                        @forelse($discount->brands as $brand)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">{{ $brand->trans('title') }}</span>
                        @empty
                            <span class="text-gray-400 text-xs">هیچ برندی انتخاب نشده</span>
                        @endforelse
                    @elseif($discount->pay_type === 'product')
                        @forelse($discount->products as $product)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">{{ $product->name }}</span>
                        @empty
                            <span class="text-gray-400 text-xs">هیچ محصولی انتخاب نشده</span>
                        @endforelse
                    @endif
                </div>
            </div>
        @endif

        {{-- تاریخچه استفاده --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">تاریخچه استفاده ({{ $discount->usage_records_count ?? $recentUsage->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                @if($recentUsage->isEmpty())
                    <div class="text-center py-12 text-gray-400 text-sm">هنوز هیچ استفاده‌ای ثبت نشده</div>
                @else
                    <table class="w-full text-start">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">کاربر</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">سفارش</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">مبلغ تخفیف</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($recentUsage as $usage)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $usage->id }}</td>
                                    <td class="px-6 py-3 text-sm">
                                        @if($usage->user)
                                            <span class="font-semibold text-gray-800 dark:text-white">{{ $usage->user->name }}</span>
                                            <span class="text-xs text-gray-400 block">{{ $usage->user->email }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        @if($usage->order_id)
                                            <a href="{{ route('admin.orders.show', $usage->order_id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">سفارش #{{ $usage->order_id }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm font-semibold text-red-600 dark:text-red-400" dir="ltr">-{{ number_format($usage->discount_amount) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ show_date($usage->used_at, 'Y/m/d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

@if($discount->user)
<div class="modal fade" id="userDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark text-start">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">جزئیات کاربر</h5>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        @if($discount->user->avatar)
                            <img src="{{ asset('storage/' . $discount->user->avatar) }}" alt="{{ $discount->user->name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            {{ mb_substr($discount->user->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h4 class="font-bold text-lg text-gray-800 dark:text-white">{{ $discount->user->name }}</h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $discount->user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $discount->user->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400">ایمیل</span>
                        <span class="font-semibold text-gray-800 dark:text-white" dir="ltr">{{ $discount->user->email ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400">موبایل</span>
                        <span class="font-semibold text-gray-800 dark:text-white" dir="ltr">{{ $discount->user->mobile ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400">زبان</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->user->locale ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400">تاریخ عضویت</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ show_date($discount->user->created_at, 'Y/m/d') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400">تعداد سفارشات</span>
                        <span class="font-semibold text-gray-800 dark:text-white">{{ $discount->user->orders()->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
