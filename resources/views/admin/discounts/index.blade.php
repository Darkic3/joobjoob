@extends('layouts.admin.app')

@section('tab-name', 'مدیریت کدهای تخفیف')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">کدهای تخفیف</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">ایجاد، ویرایش و مدیریت کدهای تخفیف فروشگاه</p>
            </div>
            @if(auth()->user()->canAccess('admin.discounts.create'))
                <a href="{{ route('admin.discounts.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    افزودن کد تخفیف
                </a>
            @endif
        </div>

        {{-- فیلتر و جستجو --}}
        <form method="GET" class="mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو در عنوان یا کد..."
                   class="flex-1 min-w-[200px] px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="">همه وضعیت‌ها</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
            </select>
            <select name="type" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                <option value="">همه انواع</option>
                <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>کد عمومی</option>
                <option value="welcome" {{ request('type') === 'welcome' ? 'selected' : '' }}>خوش آمد گویی</option>
                <option value="first_purchase" {{ request('type') === 'first_purchase' ? 'selected' : '' }}>خرید اول</option>
                <option value="category" {{ request('type') === 'category' ? 'selected' : '' }}>مرتبط دسته‌بندی</option>
                <option value="brand" {{ request('type') === 'brand' ? 'selected' : '' }}>مرتبط برند</option>
                <option value="product" {{ request('type') === 'product' ? 'selected' : '' }}>مرتبط محصول</option>
                <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>مرتبط کاربر</option>
            </select>
            <button type="submit" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-sm font-semibold transition-colors">اعمال فیلتر</button>
        </form>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">عنوان</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">کد</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">نوع</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">استفاده شده</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">وضعیت</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">انقضا</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($discounts as $discount)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $discount->id }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800 dark:text-white">{{ $discount->title }}</td>
                                <td class="px-6 py-4">
                                    {{-- data-digit-raw: کد تخفیف «داده» است و ارقامش باید همیشه لاتین بماند --}}
                                    <span data-digit-raw class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $discount->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($discount->type === 'percent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">{{ $discount->amount }}٪</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ number_format($discount->amount) }} تومان</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $discount->usage_records_count ?? 0 }} / {{ $discount->count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('admin.discounts.toggle-status', $discount->id) }}" method="POST" class="inline">
                                        @csrf
                                        @if($discount->is_active)
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500/50 transition-colors">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> غیرفعال
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($discount->expires_at)
                                        {{ show_date($discount->expires_at, 'Y/m/d') }}
                                        @if($discount->expires_at->isPast())
                                            <span class="text-red-500 text-xs">(منقضی)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('admin.discounts.show', $discount) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="جزئیات">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if(auth()->user()->canAccess('admin.discounts.edit'))
                                            <a href="{{ route('admin.discounts.edit', $discount) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                            </a>
                                        @endif
                                        @if(auth()->user()->canAccess('admin.discounts.edit'))
                                            <form action="{{ route('admin.discounts.destroy', $discount) }}" method="POST" class="inline" onsubmit="return confirm('آیا از حذف این کد تخفیف مطمئن هستید؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500" title="حذف">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 text-sm">
                                    هیچ کد تخفیفی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($discounts->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $discounts->links() }}
                </div>
            @endif
        </div>
    </div>


@endsection
