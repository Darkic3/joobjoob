@extends('layouts.admin.app')
@section('tab-name', 'تنظیمات رنگ‌بندی سایت')

@section('content')
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-2">تنظیمات رنگ‌بندی (سبک Neumorphism)</h1>
        <p class="text-gray-500 mb-6 text-sm">رنگ‌های زیر را تغییر دهید تا ظاهر سایت به‌روز شود. سایه‌ها به صورت خودکار بر اساس رنگ پس‌زمینه تنظیم می‌شوند.</p>

        <form action="{{ route('admin.theme.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- رنگ پس‌زمینه -->
                <div>
                    <label class="block text-sm font-medium mb-2">رنگ پس‌زمینه اصلی (Background)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="bg" value="{{ $theme->css_variables['--bg'] ?? '#e0e5ec' }}" class="w-16 h-10 rounded cursor-pointer border border-gray-300">
                        <span class="text-sm text-gray-600 font-mono">{{ $theme->css_variables['--bg'] ?? '#e0e5ec' }}</span>
                    </div>
                </div>

                <!-- رنگ اصلی (دکمه‌ها و لینک‌ها) -->
                <div>
                    <label class="block text-sm font-medium mb-2">رنگ اصلی / تاکیدی (Accent)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="accent" value="{{ $theme->css_variables['--accent'] ?? '#6c63ff' }}" class="w-16 h-10 rounded cursor-pointer border border-gray-300">
                        <span class="text-sm text-gray-600 font-mono">{{ $theme->css_variables['--accent'] ?? '#6c63ff' }}</span>
                    </div>
                </div>

                <!-- رنگ متن اصلی -->
                <div>
                    <label class="block text-sm font-medium mb-2">رنگ متن اصلی (Text Primary)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="text_primary" value="{{ $theme->css_variables['--text-primary'] ?? '#2d3748' }}" class="w-16 h-10 rounded cursor-pointer border border-gray-300">
                        <span class="text-sm text-gray-600 font-mono">{{ $theme->css_variables['--text-primary'] ?? '#2d3748' }}</span>
                    </div>
                </div>

                <!-- رنگ هشدار/خطا -->
                <div>
                    <label class="block text-sm font-medium mb-2">رنگ هشدار / خطا (Danger)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="danger" value="{{ $theme->css_variables['--danger'] ?? '#e53e5c' }}" class="w-16 h-10 rounded cursor-pointer border border-gray-300">
                        <span class="text-sm text-gray-600 font-mono">{{ $theme->css_variables['--danger'] ?? '#e53e5c' }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium transition-colors shadow-sm">
                    ذخیره تغییرات
                </button>
                <a href="{{ url('/') }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                    مشاهده تغییرات در سایت ↗
                </a>
            </div>
        </form>
    </div>
@endsection
