@extends('layouts.app')

@section('title', 'تنظیمات تم سایت')

@section('content')
<h1 class="text-xl font-bold mb-6">تنظیم تم پیش‌فرض سایت</h1>

<form action="{{ route('admin.theme.update') }}" method="POST">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-4 gap-5 mb-6">
        @foreach($themes as $theme)
            <label class="neu-card cursor-pointer text-center has-[:checked]:ring-2" style="{{ $theme->is_default ? 'outline: 2px solid var(--accent);' : '' }}">
                <input type="radio" name="theme_id" value="{{ $theme->id }}" class="hidden" @checked($theme->is_default)>
                <div class="w-full h-16 rounded-xl mb-3" style="background: {{ $theme->preview_color }};"></div>
                <p class="font-semibold text-sm">{{ $theme->name }}</p>
                @if($theme->is_default)
                    <span class="neu-badge neu-badge-success mt-2 inline-block">پیش‌فرض فعلی</span>
                @endif
            </label>
        @endforeach
    </div>

    <button type="submit" class="neu-btn neu-btn-primary">ذخیره تم پیش‌فرض</button>
</form>
@endsection
