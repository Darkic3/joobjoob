<?php

namespace App\Http\Controllers\Admin;

use App\Services\DashboardService;

class DashboardController
{
    public function __construct(protected DashboardService $dashboardService){}

    public function index()
    {
        $content = $this->dashboardService->dashboardIndexContent();
        return view('admin.dashboard.index', compact('content'));
    }
}
