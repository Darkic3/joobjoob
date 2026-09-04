@props(['type' => null, 'icon' => null])

@php
// نگاشت type به رنگ و SVG — جایگزین ایموجی (📦 ✅ ❌ ...) که در بعضی فونت‌ها
// به صورت دایره قرمز کوچک با ! نمایش داده می‌شد.
$map = [
    'order.new' => [
        'wrap' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
        'svg' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    ],
    'order.created' => [
        'wrap' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300',
        'svg' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    ],
    'order.paid_admin' => [
        'wrap' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300',
        'svg' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'order.paid' => [
        'wrap' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300',
        'svg' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'order.card_to_card' => [
        'wrap' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300',
        'svg' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5Z',
    ],
    'order.shipped' => [
        'wrap' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300',
        'svg' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25m-2.25 0h-2.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0',
    ],
    'order.cancelled' => [
        'wrap' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300',
        'svg' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'review.new' => [
        'wrap' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300',
        'svg' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    ],
    'contact.new' => [
        'wrap' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300',
        'svg' => 'M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    ],
    'discount.welcome' => [
        'wrap' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300',
        'svg' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    ],
    'discount.expiring' => [
        'wrap' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300',
        'svg' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
];

$entry = $map[$type] ?? [
    'wrap' => 'bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300',
    'svg' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
];
@endphp

<span class="inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0 {{ $entry['wrap'] }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $entry['svg'] }}"/>
    </svg>
</span>
