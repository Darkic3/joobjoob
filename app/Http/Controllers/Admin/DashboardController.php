<?php

namespace App\Http\Controllers\Admin;

use App\Services\HeroSlideService;

class DashboardController
{
    public function __construct(protected HeroSlideService $dashboardService){}

    public function index()
    {
        $content = $this->dashboardService->dashboardIndexContent();
        return view('admin.dashboard.index', compact('content'));
    }
}
