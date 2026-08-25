<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\ShippingMethod;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * لیست سفارشات با فیلتر
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'shippingStatus', 'shippingMethod', 'bank']);

        // فیلترها
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
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderByDesc('id')->paginate(20);
        $shippingStatuses = OrderShippingStatus::orderByDesc('id')->get();
        $shippingMethods = ShippingMethod::orderByDesc('id')->get();

        return view('admin.orders.index', compact('orders', 'shippingStatuses', 'shippingMethods'));
    }

    /**
     * جزئیات سفارش
     */
    public function show(int $id)
    {
        $order = Order::with(['items.product', 'items.productVariant', 'shippingStatus', 'shippingMethod', 'user', 'bank', 'histories.shippingStatus'])
            ->findOrFail($id);

        $shippingStatuses = OrderShippingStatus::orderByDesc('id')->get();

        return view('admin.orders.show', compact('order', 'shippingStatuses'));
    }

    /**
     * تغییر وضعیت ارسال
     */
    public function changeShippingStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $newStatusId = $request->get('shipping_status_id');

        if ($order->shipping_status_id != $newStatusId) {
            $status = OrderShippingStatus::find($newStatusId);

            // TODO: اگر status->for_send == 1 باشد → ثبت سفارش چاپار/cod24

            $order->update(['shipping_status_id' => $newStatusId]);

            // TODO: ارسال پیامک اگر sending_sms == 1
        }

        return redirect()->back()->with('success', 'وضعیت ارسال بروزرسانی شد.');
    }

    /**
     * ثبت کد پیگیری پستی
     */
    public function postCode(Request $request)
    {
        $order = Order::findOrFail($request->get('order_id'));
        $order->update(['post_code' => $request->get('post_code')]);

        // TODO: ارسال پیامک کد پیگیری

        return redirect()->back()->with('success', 'کد پیگیری پستی ثبت شد.');
    }

    /**
     * فاکتور قابل چاپ
     */
    public function factor(int $id)
    {
        $order = Order::with(['items.product', 'shippingMethod'])->findOrFail($id);

        return view('admin.orders.factor', compact('order'));
    }

    /**
     * تغییر وضعیت سفارش
     */
    public function changeOrderStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['order_status' => $request->get('order_status')]);

        return redirect()->back()->with('success', 'وضعیت سفارش بروزرسانی شد.');
    }
}
