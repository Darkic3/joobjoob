@extends("layouts.admin.app")
@section("tab-name", "تاریخچه محصول")
@section("content")
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">تاریخچه محصول <span class="text-indigo-600">#{{ $product->id }}</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $product->trans("name") ?? $product->name }} | قیمت: {{ number_format($product->price) }} تومان | موجودی: {{ $product->stock }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route("admin.products.index") }}" class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-5 py-2 rounded-xl">
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                بازگشت
            </a>
            <a href="{{ route("admin.audit-logs.index") }}" class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-5 py-2 rounded-xl">لاگ کلی</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">موجودی فعلی</p>
            <p class="text-2xl font-black text-indigo-600">{{ $product->stock }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">قیمت فعلی</p>
            <p class="text-2xl font-black text-emerald-600">{{ number_format($product->price) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">تعداد تغییرات</p>
            <p class="text-2xl font-black text-amber-600">{{ $logs->total() }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-start">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">#</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">تاریخ</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">رویداد</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">توضیحات</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">تغییرات</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">کاربر</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">عملیات</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-sm">{{ $log->id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->created_at->format("d/m/Y H:i") }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-bold
                            @if(str_contains($log->event, "stock_deducted")) bg-red-100 text-red-700
                            @elseif(str_contains($log->event, "stock_added")) bg-green-100 text-green-700
                            @elseif(str_contains($log->event, "price_changed")) bg-amber-100 text-amber-700
                            @elseif(str_contains($log->event, "created")) bg-blue-100 text-blue-700
                            @else bg-indigo-100 text-indigo-700
                            @endif
                        ">{{ $log->event_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($log->meta && isset($log->meta["difference"]))
                            <span class="{{ $log->meta["difference"] > 0 ? "text-green-600" : "text-red-600" }} font-bold">
                                {{ $log->meta["difference"] > 0 ? "+" : "" }}{{ $log->meta["difference"] }}
                            </span>
                        @elseif($log->old_values && $log->new_values)
                            <span class="text-gray-400 text-xs">تغییر ثبت شده</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? "-" }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route("admin.audit-logs.show", $log->id) }}" class="text-indigo-600 text-xs font-semibold">جزئیات</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 text-sm">هیچ لاگی برای این محصول یافت نشد.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection