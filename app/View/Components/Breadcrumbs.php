<?php

namespace App\View\Components;

use App\Services\Seo\Breadcrumbs as BreadcrumbResolver;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * مسیر راهنمای بصری صفحه — `<x-breadcrumbs />`
 *
 * داده از همان سرویسی می‌آید که اسکیمای `BreadcrumbList` را می‌سازد، تا نسخهٔ
 * بصری و نسخهٔ ساختاریافته هرگز از هم جدا نشوند.
 */
class Breadcrumbs extends Component
{
    public function render(): View
    {
        return view('components.breadcrumbs', [
            'items' => app(BreadcrumbResolver::class)->items(),
        ]);
    }
}
