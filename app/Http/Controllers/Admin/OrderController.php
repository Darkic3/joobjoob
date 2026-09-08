<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\ShippingMethod;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'shippingStatus', 'shippingMethod', 'bank']);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }
        if ($request->filled('shipping_status_id')) {
            $query->where('shipping_status_id', $request->shipping_status_id);
        }
        if ($request->filled('shipping_method_id')) {
            $query->where('shipping_method_id', $request->shipping_method_id);
        }
        if ($request->filled('mobile')) {
            $query->whereHas('user', fn ($q) => $q->where('mobile', 'LIKE', "%{$request->mobile}%"));
        }
        if ($request->filled('from_date')) {
            $fromDate = $request->from_date;
            $parts = explode('/', $fromDate);
            if (count($parts) === 3) {
                $fromDate = jalali_to_gregorian($parts[0], $parts[1], $parts[2], '-');
            }
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($request->filled('to_date')) {
            $toDate = $request->to_date;
            $parts = explode('/', $toDate);
            if (count($parts) === 3) {
                $toDate = jalali_to_gregorian($parts[0], $parts[1], $parts[2], '-');
            }
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->orderByDesc('id')->paginate(20);
        $shippingStatuses = OrderShippingStatus::orderByDesc('id')->get();
        $shippingMethods = ShippingMethod::orderByDesc('id')->get();
        $newOrdersCount = Order::whereIn('order_status', [
            'wait_for_verification',
            'pending_confirmation',
        ])->count();

        return view('admin.orders.index', compact('orders', 'shippingStatuses', 'shippingMethods', 'newOrdersCount'));
    }

    public function show(int $id)
    {
        $order = Order::with(['items.product', 'items.productVariant', 'shippingStatus', 'shippingMethod', 'user', 'bank', 'histories.shippingStatus'])
            ->findOrFail($id);
        $shippingStatuses = OrderShippingStatus::orderByDesc('id')->get();
        return view('admin.orders.show', compact('order', 'shippingStatuses'));
    }

    public function changeShippingStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $newStatusId = $request->get('shipping_status_id');
        if ($order->shipping_status_id != $newStatusId) {
            $order->update(['shipping_status_id' => $newStatusId]);

            // ── Notification: shipping status changed ──
            try {
                $shippingStatus = OrderShippingStatus::find($newStatusId);
                if ($shippingStatus && $order->user) {
                    $statusName = $shippingStatus->title ?? 'جدید';
                    app(NotificationService::class)->notifyUser(
                        $order->user,
                        'order.shipped',
                        'وضعیت ارسال سفارش تغییر کرد',
                        null,
                        route('dashboard.order-detail', $order->id),
                        ['order_id' => $order->id, 'status' => $statusName]
                    );
                }
            } catch (\Throwable $e) { Log::error('Shipping notification failed for order #' . $order->id, ['message' => $e->getMessage()]); }
        }
        return redirect()->back()->with('success', 'وضعیت ارسال بروزرسانی شد.');
    }

    public function postCode(Request $request)
    {
        $order = Order::findOrFail($request->get('order_id'));
        $order->update(['post_code' => $request->get('post_code')]);
        return redirect()->back()->with('success', 'کد پیگیری پستی ثبت شد.');
    }

    public function factor(int $id)
    {
        $order = Order::with(['items.product', 'shippingMethod'])->findOrFail($id);
        return view('admin.orders.factor', compact('order'));
    }

    public function bulkFactor(Request $request)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));
        $orders = Order::with(['items.product', 'items.productVariant', 'shippingMethod'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();
        return view('admin.orders.bulk-factor', compact('orders'));
    }

    public function changeOrderStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->get('order_status');

        $allowed = ['paying','paid','deposit_paid','pending_confirmation','wait_for_verification','unpaid','cancelled'];
        if (! in_array($newStatus, $allowed, true)) {
            return redirect()->back()->with('error', 'وضعیت نامعتبر است.');
        }

        if ($order->order_status === $newStatus) {
            return redirect()->back()->with('success', 'وضعیت سفارش بدون تغییر باقی ماند.');
        }

        try {
            $this->orderService->updateOrderStatus($order, $newStatus, auth()->user()->name ?? 'ادمین');
        } catch (\RuntimeException $e) {
            Log::warning('Admin changeOrderStatus re-reserve failed', [
                'order_id' => $order->id,
                'from' => $order->order_status,
                'to' => $newStatus,
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin changeOrderStatus failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'وضعیت سفارش بروزرسانی شد.');
    }
}
