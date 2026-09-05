@extends('layouts.site.app')

@section('title', __('messages.notification_detail'))

@section('content')
    <div class="grid md:grid-cols-4 gap-6">
        {{-- سایدبار --}}
        <div class="md:col-span-1">
            <div class="neu-card p-4 h-fit sticky top-24">
                @include('site.dashboard._sidebar')
            </div>
        </div>

        {{-- محتوای اصلی --}}
        <div class="md:col-span-3 space-y-4">
            <div class="flex items-center justify-between mb-2">
                <a href="{{ route('dashboard.notifications') }}"
                   class="text-sm font-bold transition-opacity hover:opacity-70"
                   style="color: var(--text-secondary);">
                    ← بازگشت به اعلانات
                </a>
            </div>

            {{-- کارت اعلان --}}
            <div class="neu-card relative overflow-hidden">
                <div aria-hidden="true"
                     style="position: absolute; inset-inline-end: -40px; top: -40px; width: 120px; height: 120px; border-radius: 999px;
                            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 15%, transparent), transparent 70%); pointer-events: none;"></div>

                <div class="relative flex items-start gap-4">
                    <span class="text-3xl shrink-0 mt-1">{{ $notification->icon ?? '🔔' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold" style="color: var(--text-primary);">{{ $notification->title }}</h1>
                            @if(!$notification->is_read)
                                <span class="w-2 h-2 rounded-full shrink-0" style="background: var(--accent);"></span>
                            @endif
                        </div>
                        <p class="text-xs mt-1" style="color: var(--text-secondary); opacity: 0.6;">
                            {{ show_date($notification->created_at, 'd F Y - H:i') }}
                        </p>

                        @if($notification->body)
                            <div class="mt-4 p-4 rounded-2xl"
                                 style="background: color-mix(in srgb, var(--text-primary) 4%, transparent);">
                                <p class="text-sm leading-relaxed" style="color: var(--text-primary);">
                                    {{ $notification->body }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 pt-4 border-t border-gray-200"
                             style="border-color: color-mix(in srgb, var(--text-primary) 10%, transparent);">
                            <div class="flex items-center justify-between text-xs" style="color: var(--text-secondary);">
                                <span>نوع: {{ $notification->type }}</span>
                                <span>{{ $notification->is_read ? 'خوانده‌شده' : 'برخی‌نشده' }}</span>
                            </div>

                            @if($notification->link)
                                <div class="mt-4">
                                    <a href="{{ $notification->link }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all hover:opacity-80"
                                       style="background: color-mix(in srgb, var(--accent) 12%, transparent); color: var(--accent);"
                                       target="_blank">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        مشاهده جزئیات
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
