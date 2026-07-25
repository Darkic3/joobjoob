<?php

namespace App\Services;

class DashboardService
{

    public function dashboardIndexContent(): array
    {
//        $totalUsers = User::count();
//        $totalBlogs = Blog::where('is_active',1)->count();
//        $totalAdvertisements = Advertisement::where('is_active',1)->count();
//        $totalSuppliers = Supplier::where('is_active',1)->count();
//        $totalNews = News::where('is_active',1)->count();
//        $totalRoles = Role::count();

//        TODO:ahora_fix
        return [
            'totalUsers' => 0,
            'totalBlogs' => 0,
            'totalAdvertisements' => 0,
            'totalSuppliers' => 0,
            'totalNews' => 0,
            'totalRoles' => 0
        ];
    }
}
