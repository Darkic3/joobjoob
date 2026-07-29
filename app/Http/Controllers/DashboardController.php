<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->take(5)->get();

        return view('dashboard.index', compact('orders'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    public function wishlist()
    {
        return view('dashboard.wishlist');
    }

    public function addresses()
    {
        return view('dashboard.addresses');
    }
}
