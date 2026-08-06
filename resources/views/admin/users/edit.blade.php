@extends('layouts.admin.app')

@section('tab-name', 'ویرایش کاربر')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">ویرایش کاربر «{{ $user->name }}»</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">نقش‌ها و وضعیت دسترسی این کاربر را مدیریت کنید.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                بازگشت به لیست
            </a>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-6">
            @csrf @method('PUT')

            {{-- مشخصات پایه --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="input-custom" required>
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">شماره موبایل <span class="text-rose-500">*</span></label>
                    <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" dir="ltr"
                           class="input-custom text-start" required>
                    @error('mobile') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">ایمیل</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" dir="ltr"
                           class="input-custom text-start">
                    @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">رمز عبور جدید <span class="text-xs text-gray-400 font-normal">(خالی = بدون تغییر)</span></label>
                    <input type="password" name="password" dir="ltr" autocomplete="new-password"
                           class="input-custom text-start">
                    @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- نقش‌ها --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نقش‌های کاربر</label>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">جمع دسترسی‌های این نقش‌ها برای این کاربر فعال می‌شود.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2.5 cursor-pointer select-none rounded-xl border border-gray-100 dark:border-gray-700 px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                                   @checked(in_array($role->id, old('roles', $user->roles->pluck('id')->all())))>
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $role->name }}</span>
                            @if($role->is_system)
                                <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded-full">سیستمی</span>
                            @endif
                        </label>
                    @endforeach
                </div>
                @error('roles') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- وضعیت‌های حساس — فقط سوپرادمین --}}
            @if(auth()->user()->is_admin)
                <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50/60 dark:bg-amber-500/5 p-5 space-y-4">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <div>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">تنظیمات حساس — فقط مدیر ارشد</p>
                            <p class="text-xs text-amber-700/80 dark:text-amber-400/70 mt-0.5">آخرین مدیر ارشد فعال هرگز نمی‌تواند غیرفعال شود یا مقام خود را از دست بدهد.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="is_admin" value="1" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600" @checked(old('is_admin', $user->is_admin))>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">مدیر ارشد (دسترسی کامل)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600" @checked(old('is_active', $user->is_active))>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">حساب فعال است</span>
                        </label>
                    </div>
                    @error('is_admin') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all">انصراف</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection
