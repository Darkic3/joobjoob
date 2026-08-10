<?php

namespace App\Services;

/**
 * داده‌های نمایشی داشبورد ادمین — همهٔ آماده‌سازی این‌جا انجام می‌شود
 * تا ویو فقط رندر باشد (بدون @php و منطق).
 */
class DashboardService
{
    /**
     * @return array{greeting: string, todayDate: string, stats: array, colorMap: array}
     */
    public function dashboardIndexContent(): array
    {
        $hour = now()->hour;
        $greeting = $hour < 12 ? __('admin.dashboard.greeting_morning')
            : ($hour < 17 ? __('admin.dashboard.greeting_noon') : __('admin.dashboard.greeting_evening'));

        // تاریخ: برای فارسی/عربی شمسی (jdate)، سایر زبان‌ها میلادی محلی
        $todayDate = show_date(null, 'j F Y');

        $user = auth()->user();
        $canBlog = $user && $user->canAccess('admin.blogs.index');

        $stats = [
            [
                'label' => __('admin.dashboard.stats_users'),
                'value' => 124,
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'color' => 'blue',
                'trend' => '+12%',
                'route' => null,
            ],
            [
                'label' => __('admin.dashboard.stats_blogs'),
                'value' => 45,
                'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
                'color' => 'emerald',
                'trend' => '+5%',
                'route' => $canBlog ? route('admin.blogs.index') : null,
            ],
            [
                'label' => __('admin.dashboard.stats_comments'),
                'value' => 18,
                'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'color' => 'amber',
                'trend' => '-2%',
                'route' => null,
            ],
            [
                'label' => __('admin.dashboard.stats_visits'),
                'value' => '1,240',
                'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
                'color' => 'violet',
                'trend' => '+8%',
                'route' => null,
            ],
        ];

        $colorMap = [
            'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800'],
            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-800'],
            'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800'],
            'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-800'],
        ];

        return compact('greeting', 'todayDate', 'stats', 'colorMap');
    }
}
