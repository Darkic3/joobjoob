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
        $isGuest = ! auth()->check();

        if ($this->cart->count() === 0) {
            return view('site.cart.index', [
                'items' => $this->cart->content(),
                'cart' => $this->cart,
                'addresses' => collect(),
                'states' => [],
                'banks' => collect(),
                'isGuest' => $isGuest,
            ]);
        }

        // مهمان فقط مرحله ۱ (اقلام سبد) را می‌بیند
        if ($isGuest) {
            return view('site.cart.index', [
                'items' => $this->cart->content(),
                'cart' => $this->cart,
                'addresses' => collect(),
                'states' => [],
                'banks' => collect(),
                'isGuest' => true,
            ]);
        }

        $addresses = AddressService::find()['addresses'] ?? collect();
        $states = StateService::findActive();
        $banks = \App\Models\Bank::active()->orderBy('id', 'DESC')->get();

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
            'isGuest' => false,
        ]);
    }

    public function add(Request $request)
    {
        $product = Product::where('id', $request->route('product'))->orWhere('slug', $request->route('product'))->firstOrFail();

        $request->validate([
            'qty' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $qty = (int) ($request->input('qty') ?? 1);
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;

        // محصول واریانت‌دار بدون انتخاب مشخصه (افزودن از صفحهٔ اصلی/فهرست) → واریانت پیش‌فرض
        if (empty($variantId) && $product->has_variants) {
            $default = $product->default_variant;
            if ($default) {
                $variantId = $default->id;
            }
        }

        $variant = $variantId ? $product->variants()->find($variantId) : null;
        $stock = $variant ? $variant->stock : $product->stock;

        // بررسی موجودی: پیدا کردن آیتم فعلی در سبد بر اساس محصول/وریانت/رنگ/سایز
        $cart = $this->cart->content();
        $color = $request->input('color');
        $size = $request->input('size');
        $existingItem = $cart->first(fn ($item) =>
            $item['product_id'] == $product->id
            && ($item['variant_id'] ?? null) == ($variantId ?? null)
            && ($item['color'] ?? null) === $color
            && ($item['size'] ?? null) === $size
        );
        $currentQty = $existingItem ? (int) $existingItem['qty'] : 0;
        $requestedTotal = $currentQty + $qty;
        $stockWarning = null;

        if ($requestedTotal > $stock) {
            $stockWarning = __('messages.stock_limit_reached', ['count' => $stock]);
            $qty = max(0, $stock - $currentQty);
            if ($qty <= 0) {
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $stockWarning ?? __('messages.added_to_cart'),
                'cart_count' => $this->cart->count(),
                'stock_warning' => $stockWarning,
                'stock_warning_sub' => __('messages.stock_limit_sub'),
            ]);
        }

        return back()->with($stockWarning ? 'error' : 'success', $stockWarning ?? __('messages.added_to_cart'));
    }

    public function update(Request $request, string $rowId)
    {
        $request->validate(['qty' => 'required|integer|min:1']);

        $item = $this->cart->content()->get($rowId);

        // موجودی زندهٔ همان واریانت/محصول — نه اسنپ‌شات قدیمی ردیف سبد
        $maxQty = app(\App\Services\StockService::class)
            ->getAvailableStock((int) $item['product_id'], $item['variant_id'] ?? null);
        $requestedQty = (int) $request->input('qty', 1);
        $stockWarning = null;

        if ($requestedQty > $maxQty) {
            $stockWarning = __('messages.stock_limit_reached', ['count' => $maxQty]);
        }

        // درخواستِ بیش از موجودی رد نمی‌شود (clamp نمی‌کنیم)؛ تعداد فعلی حفظ می‌شود
        $qty = min($requestedQty, $maxQty);

        if ($qty !== $requestedQty) {
            $qty = (int) $item['qty'];
        }

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
