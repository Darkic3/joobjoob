<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index()
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', ['items' => $this->cart->content(), 'cart' => $this->cart]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:1000',
            'payment_method' => 'required|in:online,cod',
        ]);

        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total' => $this->cart->total(),
                'discount' => $this->cart->discount(),
                'shipping_address' => $request->address,
                'payment_method' => $request->payment_method,
                'coupon_code' => session('coupon.code'),
            ]);

            foreach ($this->cart->content() as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'color' => $item['color'],
                    'size' => $item['size'],
                ]);
            }

            return $order;
        });

        // TODO: در این نقطه به درگاه پرداخت (زرین‌پال/آیدی‌پی/استرایپ) وصل شوید.
        // اگر پرداخت آنلاین موفق بود، سبد را خالی کنید:
        $this->cart->clear();
        session()->forget('coupon');

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }
}
