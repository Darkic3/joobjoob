@extends('layouts.admin.app')

@section('tab-name', __('messages.notifications'))

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- هدر --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ __('messages.notifications') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.notifications_manage_desc') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="markAllRead()" id="mark-all-btn"
                    class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors">
                {{ __('messages.mark_all_read') }}
            </button>
        </div>
    </div>

    {{-- فیلترها --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.notifications.index') }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ !$filter || $filter === 'all' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            {{ __('messages.all') }}
        </a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'unread' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            {{ __('messages.unread') }}
        </a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'order.new']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'order.new' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            📦 {{ __('messages.orders') }}
        </a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'review.new']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'review.new' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            💬 {{ __('admin.sidebar.reviews') }}
        </a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'contact.new']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'contact.new' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            ✉️ {{ __('admin.sidebar.contact') }}
        </a>
    </div>

    {{-- لیست اعلانات --}}
    @if($notifications->total() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @foreach($notifications as $notification)
                @php $notifLink = $notification->link ? route('admin.notifications.go', $notification->id) : '#'; @endphp
                <a href="{{ $notifLink }}"
                   class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $notification->is_read ? '' : 'bg-primary-50/50 dark:bg-primary-900/10' }}"
                   data-notification-id="{{ $notification->id }}">
                    <x-admin.notification-icon :type="$notification->type" :icon="$notification->icon" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $notification->title }}</p>
                            @if(!$notification->is_read)
                                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                            @endif
                        </div>
                        @if($notification->body)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $notification->body }}</p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ show_date($notification->created_at, 'd F Y - H:i') }}</p>
                    </div>
                    @if($notification->link)
                        <svg class="w-4 h-4 text-gray-400 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- صفحه‌بندی --}}
        <div class="mt-4">
            {{ $notifications->withQueryString()->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 py-16 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 font-medium">{{ __('admin.layout.no_notifications') }}</p>
        </div>
    @endif
</div>

<script>
function markRead(id) {
    fetch('{{ url("/admin/api/notifications") }}/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(() => {
        const el = document.querySelector('[data-notification-id="' + id + '"]');
        if (el) {
            el.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/10');
            const badge = el.querySelector('span.bg-primary-500');
            if (badge) badge.remove();
        }
    }).catch(() => {});
}

function markAllRead() {
    fetch('{{ url("/admin/api/notifications/read-all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(() => {
        document.querySelectorAll('[data-notification-id]').forEach(el => {
            el.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/10');
            const badge = el.querySelector('span.bg-primary-500');
            if (badge) badge.remove();
        });
        const btn = document.getElementById('mark-all-btn');
        if (btn) btn.style.display = 'none';
    }).catch(() => {});
}
</script>
@endsection
