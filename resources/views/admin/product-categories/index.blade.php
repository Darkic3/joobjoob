@extends('layouts.admin.app')

@section('tab-name', 'مدیریت دسته‌بندی محصولات')

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">مدیریت دسته‌بندی محصولات</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">دسته‌بندی‌ها و زیردسته‌های محصولات فروشگاه را مدیریت کنید</p>
            </div>
            @if(auth()->user()->canAccess('admin.product-categories.create'))
                <a href="{{ route('admin.product-categories.create') }}" class="btn-primary flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>دسته‌بندی جدید</span>
                </a>
            @endif
        </div>

        {{-- جستجو و فیلتر --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('admin.product-categories.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو در نام یا اسلاگ..."
                           class="input-custom w-full">
                </div>
                <select name="is_active" class="input-custom w-auto min-w-[140px]">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1" @selected(request('is_active') === '1')>فعال</option>
                    <option value="0" @selected(request('is_active') === '0')>غیرفعال</option>
                </select>
                <button type="submit" class="btn-primary px-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="hidden sm:inline">جستجو</span>
                </button>
                @if(request()->hasAny(['search', 'is_active']))
                    <a href="{{ route('admin.product-categories.index') }}"
                       class="inline-flex items-center px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium transition-all">
                        پاک‌کردن
                    </a>
                @endif
            </form>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">تصویر</th>
                        <th class="px-6 py-4">عنوان</th>
                        <th class="px-6 py-4">دسته والد</th>
                        <th class="px-6 py-4">تعداد محصولات</th>
                        <th class="px-6 py-4">وضعیت</th>
                        <th class="px-6 py-4">ترتیب</th>
                        <th class="px-6 py-4 text-left">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $categories->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                @if($category->image)
                                    <img src="{{ $category->image_url }}"
                                         class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600"
                                         alt="{{ $category->trans('name') }}">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $category->trans('name') ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono" dir="ltr">{{ $category->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $category->parent ? $category->parent->trans('name') : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ number_format($category->products_count) }} محصول
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $category->sort_order }}
                            </td>
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end gap-1">
                                    @if(auth()->user()->canAccess('admin.product-categories.edit'))
                                        <a href="{{ route('admin.product-categories.edit', $category) }}"
                                           class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->canAccess('admin.product-categories.destroy'))
                                        <form action="{{ route('admin.product-categories.destroy', $category) }}" method="POST" class="delete-form"
                                              onsubmit="return confirm('دسته‌بندی «{{ $category->trans('name') }}» حذف شود؟ اگر محصولی داشته باشد حذف نمی‌شود.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="حذف">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">هنوز دسته‌بندی‌ای ثبت نشده است</p>
                                    @if(auth()->user()->canAccess('admin.product-categories.create'))
                                        <a href="{{ route('admin.product-categories.create') }}" class="mt-3 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">اولین دسته‌بندی را ایجاد کنید &larr;</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
