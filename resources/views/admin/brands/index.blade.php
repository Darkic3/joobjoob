@extends('layouts.admin.app')

@section('tab-name', 'مدیریت برندها')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">مدیریت برندها</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">برندهای محصولات فروشگاه را مدیریت کنید.</p>
            </div>
            @if(auth()->user()->canAccess('admin.brands.create'))
                <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    برند جدید
                </a>
            @endif
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-6">
            <form action="{{ route('admin.brands.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو در نام برند..." class="input-custom">
                </div>
                <select name="status" class="input-custom w-auto min-w-[120px]">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>فعال</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>غیرفعال</option>
                </select>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    جستجو
                </button>
            </form>
        </div>

        {{-- جدول برندها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">برند</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">آدرس</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">محصولات</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">صفحه اول</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">وضعیت</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-11 w-11 rounded-xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-700">
                                        @if($brand->image)
                                            <img src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $brand->image) }}" class="w-full h-full object-cover" alt="{{ $brand->trans('title') }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold">{{ mb_substr($brand->trans('title'), 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $brand->trans('title') }}</div>
                                        @if($brand->trans('description'))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $brand->trans('description') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400" dir="ltr">{{ $brand->url }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ number_format($brand->products_count) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($brand->show_in_first_page)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">نمایش</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($brand->active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> غیرفعال
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10" title="ویرایش">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10" title="حذف">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">هنوز برندی ثبت نشده است</p>
                                    @if(auth()->user()->canAccess('admin.brands.create'))
                                        <a href="{{ route('admin.brands.create') }}" class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">اولین برند را ایجاد کنید</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($brands->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
