@extends("layouts.admin.app")
@section("tab-name", "لاگ حسابرسی")
@section("content")
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">لاگ حسابرسی</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">تاریخچه کامل تغییرات سفارشات و محصولات</p>
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
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">کاربر</th>
                <th class="px-4 py-3 text-xs font-semibold text-gray-500">عملیات</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-sm">{{ $log->id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->created_at->format("d/m/Y H:i") }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $log->event_label }}</span></td>
                    <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? "-" }}</td>
                    <td class="px-4 py-3"><a href="{{ route("admin.audit-logs.show", $log->id) }}" class="text-indigo-600 text-xs font-semibold">جزئیات</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500 text-sm">هیچ لاگی یافت نشد.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $logs->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
