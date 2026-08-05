@extends('layouts.admin.app')

@section('tab-name', 'مدیریت نشان‌های اعتماد')

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">مدیریت نشان‌های اعتماد</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست تمام نشان‌های فعال و غیرفعال</p>
            </div>
            <a href="{{ route('admin.trust-badges.create') }}" class="btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>نشان جدید</span>
            </a>
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('admin.trust-badges.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو در عنوان یا توضیحات..."
                           class="input-custom w-full">
                </div>
                <select name="status" class="input-custom w-auto min-w-[120px]">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>فعال</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>غیرفعال</option>
                </select>
                <button type="submit" class="btn-primary px-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="hidden sm:inline">جستجو</span>
                </button>
            </form>
        </div>

        {{-- جدول نشان‌های اعتماد --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16">#</th>
                        <th class="px-6 py-4 w-20 text-center">آیکون</th>
                        <th class="px-6 py-4">عنوان و توضیحات</th>
                        <th class="px-6 py-4 w-24">ترتیب</th>
                        <th class="px-6 py-4 w-28">وضعیت</th>
                        <th class="px-6 py-4 w-32">تاریخ</th>
                        <th class="px-6 py-4 text-left w-24">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($badges as $badge)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono">{{ $loop->iteration }}</td>

                            <td class="px-6 py-4 text-center text-2xl">
                                {{ $badge->icon }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ Str::limit($badge->title, 40) }}
                                </div>
                                @if($badge->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                        {{ $badge->description }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono text-sm">
                                    {{ $badge->sort_order }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $badge->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800' }}">
                                    <span class="w-1.5 h-1.5 {{ $badge->is_active ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full"></span>
                                    {{ $badge->is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                <div>{{ jdate_format('Y/m/d', strtotime($badge->created_at)) }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ jdate_format('H:i', strtotime($badge->created_at)) }}</div>
                            </td>

                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.trust-badges.edit', $badge) }}"
                                       class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.trust-badges.destroy', $badge) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="حذف">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">هنوز نشان اعتمادی ثبت نشده است</p>
                                    <a href="{{ route('admin.trust-badges.create') }}" class="mt-3 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">اولین نشان را ایجاد کنید &larr;</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($badges) && $badges->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    {{ $badges->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
