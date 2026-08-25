<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Discount;
use App\Models\Order;
use App\Models\State;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CityService;
use App\Services\NumberHelper;
use App\Services\ShippingCalculator;
use App\Services\StateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cart) {}

    // ==================== مرحله ۱: سبد خرید ====================

    public function index()
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', [
            'items' => $this->cart->content(),
            'cart' => $this->cart,
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'total' => $this->cart->total(),
        ]);
    }

    // ==================== مرحله ۲: انتخاب آدرس ====================

    public function address()
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index');
        }

        $addresses = AddressService::find()['addresses'] ?? collect();
        $states = StateService::findActive();

        return view('checkout.address', [
            'addresses' => $addresses,
            'states' => $states,
            'cart' => $this->cart,
        ]);
    }

    /**
     * دریافت شهرها بر اساس استان (AJAX)
     */
    public function cities(Request $request): JsonResponse
    {
        $cities = CityService::findActiveByState($request->get('state_id'));
        return response()->json(['cities' => $cities]);
    }

    /**
     * دریافت روش‌های ارسال موجود بر اساس آدرس (AJAX)
     */
    public function shipments(Request $request): JsonResponse
    {
        $address = AddressService::findOne($request->get('address_id'));

        // ذخیره آدرس در سشن
        session(['checkout_address_id' => $address->id]);

        $methods = ShippingCalculator::getAvailableMethods(
            $address,
            $this->cart->roundedWeight(),
            $this->cart->total()
        );

        return response()->json(['shipping_methods' => $methods]);
    }

    /**
     * انتخاب روش ارسال (AJAX)
     */
    public function setShipment(Request $request): JsonResponse
    {
        session([
            'checkout_shipping_method_id' => $request->get('shipping_method_id'),
            'checkout_shipping_price' => $request->get('shipping_price', 0),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * محاسبه قیمت نهایی (AJAX)
     */
    public function addressPrice(): JsonResponse
    {
        $addressId = session('checkout_address_id');
        $shippingMethodId = session('checkout_shipping_method_id');
        $shippingPrice = session('checkout_shipping_price', 0);

        $subtotal = $this->cart->subtotal();
        $discount = $this->cart->discount();
        $total = $this->cart->total();
        $priceCart = $total + intval($shippingPrice);

        return response()->json([
            'final_price_sum' => $subtotal,
            'price_discount' => $discount,
            'price_shipping' => $shippingPrice,
            'price_cart' => $priceCart,
        ]);
    }

    // ==================== مرحله ۳: پرداخت ====================

    public function payment()
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index');
        }

        $addressId = session('checkout_address_id');
        $shippingMethodId = session('checkout_shipping_method_id');

        if (! $addressId) {
            return redirect()->route('checkout.address')
                ->with('error', 'لطفا ابتدا آدرس را انتخاب کنید');
        }

        if (! $shippingMethodId) {
            return redirect()->route('checkout.address')
                ->with('error', 'لطفا روش ارسال را انتخاب کنید');
        }

        $banks = \App\Models\Bank::active()->orderBy('id', 'DESC')->get();

        return view('checkout.payment', [
            'items' => $this->cart->content(),
            'cart' => $this->cart,
            'banks' => $banks,
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'shipping_price' => session('checkout_shipping_price', 0),
            'total' => $this->cart->total() + intval(session('checkout_shipping_price', 0)),
        ]);
    }

    /**
     * اعمال کد تخفیف (AJAX)
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $code = $request->get('discount_code');

        if (! $code) {
            return response()->json(['success' => false, 'message' => 'کد تخفیف را وارد نمایید.']);
        }

        $discount = Discount::where('title', $code)
            ->whereNull('deleted_at')
            ->first();

        if (! $discount) {
            return response()->json(['success' => false, 'message' => 'کد تخفیف نامعتبر است.']);
        }

        // TODO: بررسی اعتبار کامل (تعداد استفاده، حداقل قیمت و ...)

        $this->cart->applyCoupon($code);

        return response()->json([
            'success' => true,
            'message' => 'کد تخفیف با موفقیت اعمال شد.',
        ]);
    }

    /**
     * حذف کد تخفیف (AJAX)
     */
    public function removeDiscount(): JsonResponse
    {
        session()->forget('coupon');
        return response()->json(['success' => true]);
    }

    /**
     * دریافت قیمت نهایی (AJAX)
     */
    public function orderPrice(): JsonResponse
    {
        $subtotal = $this->cart->subtotal();
        $discount = $this->cart->discount();
        $shippingPrice = session('checkout_shipping_price', 0);
        $total = $this->cart->total() + intval($shippingPrice);

        return response()->json([
            'final_price_sum' => $subtotal,
            'price_discount' => $discount,
            'price_shipping' => $shippingPrice,
            'price_cart' => $total,
        ]);
    }

    // ==================== مرحله ۴: ایجاد سفارش ====================

    public function create(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
        ]);

        $bankId = $request->get('bank_id');
        $userDescription = $request->get('user_description');

        $addressId = session('checkout_address_id');
        $shippingMethodId = session('checkout_shipping_method_id');
        $shippingPrice = session('checkout_shipping_price', 0);

        if (! $addressId || ! $shippingMethodId) {
            return redirect()->back()->with('error', 'اطلاعات تکمیل نیست.');
        }

        $address = \App\Models\Address::with(['state', 'city'])->find($addressId);

        $orderData = [
            'address_id' => $address->id,
            'state_id' => $address->state_id,
            'city_id' => $address->city_id,
            'state_name' => $address->state?->name ?? '',
            'city_name' => $address->city?->name ?? '',
            'full_address' => $address->address,
            'receiptor_full_name' => $address->receiptor_full_name,
            'receiptor_mobile' => $address->receiptor_mobile,
            'postal_code' => $address->postal_code,
            'shipping_method_id' => $shippingMethodId,
            'bank_id' => $bankId,
            'shipping_price' => $shippingPrice,
            'total_price' => $this->cart->subtotal(),
            'discount_price' => $this->cart->discount(),
            'payment_price' => $this->cart->total() + intval($shippingPrice),
            'freight_balance' => 0,
        ];

        try {
            $orderService = app(\App\Services\OrderService::class);
            $order = $orderService->create(
                $this->cart->toArray(),
                $orderData,
                $userDescription
            );

            $result = $orderService->checkout($order);

            // پاکسازی سبد
            $this->cart->clear();
            session()->forget('coupon');
            session()->forget('checkout_address_id');
            session()->forget('checkout_shipping_method_id');
            session()->forget('checkout_shipping_price');

            return $result;
        } catch (\Exception $e) {
            \Log::error('Order creation failed', ['message' => $e->getMessage()]);
            return redirect(route('checkout.failed'))
                ->with('error', 'خطا در ایجاد سفارش، مجدد تلاش نمایید.');
        }
    }

    // ==================== صفحات نتیجه ====================

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return redirect('/')->with('error', 'این فاکتور متعلق به شما نیست');
        }

        if ($order->order_status !== 'paid') {
            return redirect('/')->with('info', 'برای اطلاعات بیشتر با پشتیبانی تماس بگیرید');
        }

        return view('checkout.success', compact('order'));
    }

    public function failed()
    {
        return view('checkout.failed');
    }

    // ==================== Callback درگاه‌های پرداخت ====================

    public function finishZarinPal(Request $request)
    {
        $input = $request->all();
        $orderService = app(\App\Services\OrderService::class);
        return $orderService->callback('transaction_info->post->Authority', $input['Authority'] ?? '', $input);
    }

    public function finishSaman(Request $request)
    {
        $input = $request->all();
        $orderService = app(\App\Services\OrderService::class);
        return $orderService->callback('transaction_info->post->token', $input['Token'] ?? '', $input);
    }

    public function finishSadad(Request $request)
    {
        $input = $request->all();
        $orderService = app(\App\Services\OrderService::class);
        return $orderService->callback('id', $input['OrderId'] ?? '', $input);
    }

    public function finishZibal(Request $request)
    {
        $input = $request->all();
        $orderService = app(\App\Services\OrderService::class);
        return $orderService->callback('transaction_info->post->trackId', $input['trackId'] ?? '', $input);
    }
}
