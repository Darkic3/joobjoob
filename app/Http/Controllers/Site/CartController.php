<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\StateService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index(Request $request)
    {
        if ($this->cart->count() === 0) {
            return view('site.cart.index', ['items' => $this->cart->content(), 'cart' => $this->cart, 'addresses' => collect(), 'states' => [], 'banks' => collect()]);
        }

        $addresses = AddressService::find()['addresses'] ?? collect();
        $states = StateService::findActive();
        $banks = \App\Models\Bank::active()->orderBy('id', 'DESC')->get();

        // آدرس و روش ارسال انتخاب‌شده (از سشن)
        $selectedAddress = null;
        if ($addressId = session('checkout_address_id')) {
            $selectedAddress = \App\Models\Address::with(['state', 'city'])->find($addressId);
        }
        $selectedShipping = null;
        if ($shippingMethodId = session('checkout_shipping_method_id')) {
            $selectedShipping = \App\Models\ShippingMethod::find($shippingMethodId);
        }
        $shippingPrice = session('checkout_shipping_price', 0);

        $currentStep = $request->get('step') ? (int) $request->get('step') : null;

        // اعتبارسنجی مراحل — جلوگیری از رد شدن از مراحل
        if ($currentStep !== null) {
            if ($currentStep >= 3 && ! session('checkout_address_id')) {
                $currentStep = 2;
            }
            if ($currentStep >= 3 && ! session('checkout_shipping_method_id')) {
                $currentStep = 2;
            }
            if ($currentStep >= 4 && ! session('checkout_address_id')) {
                $currentStep = 2;
            }
        }

        return view('site.cart.index', [
            'items' => $this->cart->content(),
            'cart' => $this->cart,
            'addresses' => $addresses,
            'states' => $states,
            'banks' => $banks,
            'selectedAddress' => $selectedAddress,
            'selectedShipping' => $selectedShipping,
            'shippingPrice' => $shippingPrice,
            'currentStep' => $currentStep,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $qty = (int) ($request->input('qty') ?? 1);
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;

        // ── بررسی موجودی: مجموع تعداد فعلی در سبد + تعداد درخواستی ──
        $variant = $variantId ? $product->variants()->find($variantId) : null;
        $stock = $variant ? $variant->stock : $product->stock;

        $cart = $this->cart->content();
        $rowId = md5($product->id . ($variantId ?? '') . ($request->input('color') ?? '') . ($request->input('size') ?? ''));
        $currentQty = $cart->has($rowId) ? (int) $cart->get($rowId)['qty'] : 0;
        $requestedTotal = $currentQty + $qty;
        $stockWarning = null;

        if ($requestedTotal > $stock) {
            $stockWarning = __('messages.stock_limit_reached', ['count' => $stock]);
            // فقط تا سقف موجودی اضافه کن
            $qty = max(0, $stock - $currentQty);
            if ($qty <= 0) {
                // قبلاً به سقف موجودی رسیده
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $stockWarning,
                        'cart_count' => $this->cart->count(),
                        'stock_limit' => true,
                        'stock_warning_sub' => __('messages.stock_limit_sub'),
                    ], 422);
                }
                return back()->with('error', $stockWarning);
            }
        }

        $this->cart->add($product, $qty, $variantId, $request->input('color'), $request->input('size'));

        // اگر درخواست از نوع AJAX (JSON) بود، پاسخ جیسون بده
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $stockWarning ?? __('messages.added_to_cart'),
                'cart_count' => $this->cart->count(),
                'stock_warning' => $stockWarning,
                'stock_warning_sub' => __('messages.stock_limit_sub'),
            ]);
        }

        // در غیر این صورت، رفتار عادی (رفرش صفحه) را حفظ کن
        return back()->with($stockWarning ? 'error' : 'success', $stockWarning ?? __('messages.added_to_cart'));
    }

    public function update(Request $request, string $rowId)
    {
        $request->validate(['qty' => 'required|integer|min:1']);

        $item = $this->cart->content()->get($rowId);
        $maxQty = $item['stock'] ?? PHP_INT_MAX;
        $requestedQty = (int) $request->input('qty', 1);
        $stockWarning = null;

        if ($requestedQty > $maxQty) {
            $stockWarning = __('messages.stock_limit_reached', ['count' => $maxQty]);
        }

        $qty = min($requestedQty, $maxQty);

        $this->cart->update($rowId, $qty);

        if ($request->wantsJson()) {
            $updatedItem = $this->cart->content()->get($rowId);
            return response()->json([
                'success' => true,
                'item' => $updatedItem,
                'subtotal' => $this->cart->subtotal(),
                'discount' => $this->cart->discount(),
                'total' => $this->cart->total(),
                'cart_count' => $this->cart->count(),
                'stock_warning' => $stockWarning,
                'stock_warning_sub' => $stockWarning ? __('messages.stock_limit_sub') : null,
            ]);
        }

        return back();
    }

    public function remove(Request $request, string $rowId)
    {
        $this->cart->remove($rowId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'subtotal' => $this->cart->subtotal(),
                'discount' => $this->cart->discount(),
                'total' => $this->cart->total(),
                'cart_count' => $this->cart->count(),
                'is_empty' => $this->cart->content()->isEmpty(),
            ]);
        }

        return back();
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $ok = $this->cart->applyCoupon($request->code);

        return back()->with($ok ? 'success' : 'error', $ok ? __('messages.coupon_applied') : __('messages.coupon_invalid'));
    }
}
