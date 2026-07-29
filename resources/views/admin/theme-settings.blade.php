@extends('layouts.admin.app') {{-- یا هر لایوت ادمین که دارید --}}
@section('tab-name', 'تنظیمات تم سایت')
@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">مدیریت تم و رنگ‌بندی سایت</h1>

        <form action="{{ route('admin.theme.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- بخش ۱: انتخاب تم پایه --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-3">انتخاب تم پایه:</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($themes as $theme)
                        <label class="cursor-pointer border-2 rounded-xl p-4 transition-all {{ $theme->is_default ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                            <input type="radio" name="theme_id" value="{{ $theme->id }}" class="hidden" @checked($theme->is_default)>
                            <div class="w-full h-12 rounded-lg mb-3 shadow-sm" style="background: {{ $theme->preview_color }};"></div>
                            <p class="font-semibold text-sm text-center">{{ $theme->name }}</p>
                            @if($theme->is_default)
                                <span class="text-xs text-blue-600 font-bold block text-center mt-1">فعال</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- بخش ۲: شخصی‌سازی رنگ‌ها --}}
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">شخصی‌سازی رنگ‌ها (اختیاری)</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    @php
                        $defaultTheme = $themes->firstWhere('is_default', true) ?? $themes->first();
                        $currentColors = $defaultTheme->css_variables ?? [];
                    @endphp

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">رنگ اصلی (Accent)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="colors[accent]" value="{{ $currentColors['accent'] ?? '#6c63ff' }}" class="w-10 h-10 rounded cursor-pointer border-0 p-0">
                            <span class="text-sm font-mono">{{ $currentColors['accent'] ?? '#6c63ff' }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">رنگ پس‌زمینه (Background)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="colors[bg]" value="{{ $currentColors['bg'] ?? '#e0e5ec' }}" class="w-10 h-10 rounded cursor-pointer border-0 p-0">
                            <span class="text-sm font-mono">{{ $currentColors['bg'] ?? '#e0e5ec' }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">رنگ متن اصلی</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="colors[text-primary]" value="{{ $currentColors['text-primary'] ?? '#2d3748' }}" class="w-10 h-10 rounded cursor-pointer border-0 p-0">
                            <span class="text-sm font-mono">{{ $currentColors['text-primary'] ?? '#2d3748' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
@endsection
