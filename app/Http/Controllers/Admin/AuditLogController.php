<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * لیست کلی لاگ‌ها
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('event', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->paginate(30);

        return view('admin.audit-logs.index', compact('logs'));
    }

    /**
     * جزئیات یک لاگ
     * اگر با ?from=order&order_id=X آمده باشد (از تاریخچه سفارش)، دکمه بازگشت به همان تاریخچه هم نمایش داده می‌شود.
     */
    public function show(Request $request, int $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        $backOrderId = null;
        if ($request->query('from') === 'order' && ctype_digit((string) $request->query('order_id'))) {
            $orderId = (int) $request->query('order_id');
            if (Order::whereKey($orderId)->exists()) {
                $backOrderId = $orderId;
            }
        }

        return view('admin.audit-logs.show', compact('log', 'backOrderId'));
    }

    /**
     * تاریخچه تغییرات یک سفارش
     */
    public function orderHistory(int $orderId)
    {
        $order = Order::findOrFail($orderId);
        $logs = AuditLog::forOrder($orderId)->with('user')->latest()->paginate(30);

        return view('admin.audit-logs.order-history', compact('order', 'logs'));
    }

    /**
     * تاریخچه تغییرات یک محصول
     */
    public function productHistory(int $productId)
    {
        $product = Product::findOrFail($productId);
        $logs = AuditLog::forProduct($productId)->with('user')->latest()->paginate(30);

        return view('admin.audit-logs.product-history', compact('product', 'logs'));
    }
}
