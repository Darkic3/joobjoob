<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * جدول سفارشات هنوز ساخته نشده (سیستم سفارش در راه است).
     */
    private function hasOrdersTable(): bool
    {
        return Schema::hasTable('orders');
    }

    public function index()
    {
        $orders = $this->hasOrdersTable()
            ? auth()->user()->orders()->latest()->take(5)->get()
            : collect();

        return view('site.dashboard.index', compact('orders'));
    }

    public function orders()
    {
        if (! $this->hasOrdersTable()) {
            return view('site.dashboard.orders', ['orders' => new LengthAwarePaginator([], 0, 10)]);
        }

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
