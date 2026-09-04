<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Bank;
use App\Models\Discount;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CityService;
use App\Services\OrderService;
use App\Services\ShippingCalculator;
use App\Services\StockService;
use App\Http\Requests\Site\CardToCardCheckoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cart) {}

    // ==================== مرحله ۱: سبد خرید ====================

    public function index()
    {
        return redirect()->route('cart.index');
    }

    // ==================== مرحله ۲: انتخاب آدرس ====================

    public function address()
    {
        return redirect()->route('cart.index')->with('step', 2);
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
        return redirect()->route('cart.index')->with('step', 3);
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

        $couponService = app(\App\Services\CouponService::class);
        $result = $couponService->apply($code, $this->cart->subtotal());

        return response()->json($result);
    }

    /**
     * حذف کد تخفیف (AJAX)
     */
    public function removeDiscount(): JsonResponse
    {
        $couponService = app(\App\Services\CouponService::class);
        $couponService->remove();

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

    public function create(CardToCardCheckoutRequest $request)
    {
        $bankId = $request->input('bank_id');
        $userDescription = $request->input('user_description');

        // اطلاعات فیش واریزی (کارت به کارت)
        $receiptTrackingCode = $request->input('receipt_tracking_code');
        $receiptPayerCard = $request->input('receipt_payer_card');

        $bank = Bank::find($bankId);
        $isCardToCard = $bank && $bank->bank_type === 'card_to_card';

        $addressId = session('checkout_address_id');
        $shippingMethodId = session('checkout_shipping_method_id');
        $shippingPrice = session('checkout_shipping_price', 0);

        if (! $addressId || ! $shippingMethodId) {
            return redirect()->back()->with('error', 'اطلاعات تکمیل نیست.');
        }

        $address = Address::with(['state', 'city'])->find($addressId);
        $shippingMethod = ShippingMethod::find($shippingMethodId);

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
            'freight_balance' => $shippingMethod->freight_balance ?? 0,
            'receipt_tracking_code' => $isCardToCard ? $receiptTrackingCode : null,
            'receipt_payer_card' => $isCardToCard && $receiptPayerCard ? $receiptPayerCard : null,
            'discount_id' => session('coupon.discount_id'),
        ];

        try {
            $orderService = app(OrderService::class);
            $order = $orderService->create(
                $this->cart->toArray(),
                $orderData,
                $userDescription
            );

            $result = $orderService->checkout($order);

            // ثبت استفاده از کد تخفیف
            $couponSession = session('coupon');
            if ($order->discount_id && $couponSession) {
                $couponService = app(\App\Services\CouponService::class);
                $couponService->recordUsage(
                    $order->discount_id,
                    auth()->id(),
                    $order->id,
                    $couponSession['discount_amount'] ?? $order->discount_price
                );
            }

            // پاکسازی سبد
            $this->cart->clear();
            session()->forget('coupon');
            session()->forget('checkout_address_id');
            session()->forget('checkout_shipping_method_id');
            session()->forget('checkout_shipping_price');

            return $result;
        } catch (\Exception $e) {
            \Log::error('Order creation failed', ['message' => $e->getMessage()]);

            return redirect()->route('checkout.failed')
                ->with('error', 'خطا در ایجاد سفارش، مجدد تلاش نمایید.');
        }
    }

    // ==================== صفحات نتیجه ====================

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return redirect('/')->with('error', 'این فاکتور متعلق به شما نیست');
        }

        // کارت به کارت — ریدایرکت به صفحه اصلی با پیام موفقیت
        if ($order->order_status === 'pending_confirmation') {
            return redirect('/')->with('success', 'سفارش شما با موفقیت ثبت شد و بعد از تایید ادمین ارسال خواهد شد.');
        }

        if ($order->order_status !== 'paid') {
            return redirect('/')->with('error', 'برای اطلاعات بیشتر با پشتیبانی تماس بگیرید');
        }

        // پیام موفقیت پرداخت آنلاین — reflash تا روی صفحه اصلی هم نمایش داده شود
        session()->reflash();

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
        $orderService = app(OrderService::class);

        return $orderService->callback('transaction_info->post->Authority', $input['Authority'] ?? '', $input);
    }

    public function finishSaman(Request $request)
    {
        $input = $request->all();
        $orderService = app(OrderService::class);

        return $orderService->callback('transaction_info->post->token', $input['Token'] ?? '', $input);
    }

    public function finishSadad(Request $request)
    {
        $input = $request->all();
        $orderService = app(OrderService::class);

        return $orderService->callback('id', $input['OrderId'] ?? '', $input);
    }

    public function finishZibal(Request $request)
    {
        $input = $request->all();
        $orderService = app(OrderService::class);

        return $orderService->callback('transaction_info->post->trackId', $input['trackId'] ?? '', $input);
    }

    /**
     * بررسی موجودی آیتم‌های سبد خرید (AJAX) — مرحله ۴ قبل از پرداخت
     */
    public function validateStock(): JsonResponse
    {
        $cartItems = $this->cart->toArray();
        $stockService = app(StockService::class);
        $result = $stockService->validateCartItems($cartItems);

        if (! empty($result['unavailable']) || ! empty($result['insufficient'])) {
            // حذف آیتم‌های ناموجود از سبد
            foreach ($cartItems as $item) {
                foreach ($result['unavailable'] as $unavailable) {
                    if (($item['product_id'] ?? null) == $unavailable['product_id']
                        && ($item['variant_id'] ?? null) == ($unavailable['variant_id'] ?? null)
                    ) {
                        $this->cart->remove((string) $item['rowId']);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'unavailable' => $result['unavailable'],
                'insufficient' => $result['insufficient'],
                'message' => 'موجودی برخی محصولات تغییر کرده است.',
            ]);
        }

        return response()->json(['success' => true]);
    }
}
