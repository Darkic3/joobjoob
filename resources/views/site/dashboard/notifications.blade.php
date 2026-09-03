@extends('layouts.site.app')

@section('title', __('messages.notifications'))

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
            <h2 class="text-lg font-bold" style="color: var(--text-primary);">{{ __('messages.notifications') }}</h2>
            @if($notifications->total() > 0)
                <button onclick="markAllRead()" id="mark-all-btn"
                        class="text-xs font-bold transition-opacity hover:opacity-70"
                        style="color: var(--accent);">
                    {{ __('messages.mark_all_read') }}
                </button>
            @endif
        </div>

        @if($notifications->total() > 0)
            <div class="space-y-3" id="notifications-list">
                @foreach($notifications as $notification)
                    @php $notifLink = $notification->link ?: '#'; @endphp
                    <a href="{{ $notifLink }}"
                       class="flex items-start gap-3 p-4 rounded-2xl transition-all duration-200 hover:translate-y-[-1px] {{ $notification->is_read ? '' : 'ring-1' }}"
                       style="background: var(--bg-elevated); {{ $notification->is_read ? '' : 'ring-color: color-mix(in srgb, var(--accent) 20%, transparent);' }}"
                       data-notification-id="{{ $notification->id }}"
                       onclick="markRead({{ $notification->id }});">
                        <span class="text-2xl shrink-0 mt-0.5">{{ $notification->icon ?? '🔔' }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $notification->title }}</p>
                                @if(!$notification->is_read)
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background: var(--accent);"></span>
                                @endif
                            </div>
                            @if($notification->body)
                                <p class="text-xs mt-1 leading-relaxed" style="color: var(--text-secondary);">{{ $notification->body }}</p>
                            @endif
                            <p class="text-[10px] mt-2" style="color: var(--text-secondary); opacity: 0.6;">{{ show_date($notification->created_at, 'd F Y - H:i') }}</p>
                        </div>
                        @if($notification->link)
                            <svg class="w-4 h-4 shrink-0 mt-1" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- صفحه‌بندی --}}
            @if($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="neu-card text-center py-16">
                <div class="w-20 h-20 mx-auto grid place-items-center mb-5 rounded-3xl" style="background: color-mix(in srgb, var(--accent) 8%, transparent);">
                    <svg class="w-9 h-9" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </div>
                <p class="text-sm font-bold mb-2" style="color: var(--text-primary);">{{ __('messages.no_notifications') }}</p>
                <p class="text-xs" style="color: var(--text-secondary);">{{ __('messages.notifications_empty_desc') }}</p>
            </div>
        @endif
    </div>
</div>

<script>
function markRead(id) {
    fetch('{{ url("/api/notifications") }}/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(() => {
        const el = document.querySelector('[data-notification-id="' + id + '"]');
        if (el) el.classList.remove('ring-1');
        const badge = el?.querySelector('span[style*="accent"]');
        if (badge) badge.remove();
    }).catch(() => {});
}

function markAllRead() {
    fetch('{{ url("/api/notifications/read-all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(() => {
        document.querySelectorAll('#notifications-list .ring-1').forEach(el => el.classList.remove('ring-1'));
        document.querySelectorAll('#notifications-list span[style*="accent"][class*="rounded-full"][class*="w-2"]').forEach(el => el.remove());
        const btn = document.getElementById('mark-all-btn');
        if (btn) btn.style.display = 'none';
    }).catch(() => {});
}
</script>
@endsection
