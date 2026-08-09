<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->take(5)->get();

        return view('site.dashboard.index', compact('orders'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('site.dashboard.orders', compact('orders'));
    }

    public function wishlist()
    {
        return view('site.dashboard.wishlist');
    }

    public function addresses()
    {
        return view('site.dashboard.addresses');
    }
}
