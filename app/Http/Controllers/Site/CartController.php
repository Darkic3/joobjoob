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

    public function index()
    {
        if ($this->cart->count() === 0) {
            return view('site.cart.index', ['items' => $this->cart->content(), 'cart' => $this->cart, 'addresses' => collect(), 'states' => [], 'banks' => collect()]);
        }

        $addresses = AddressService::find()['addresses'] ?? collect();
        $states = StateService::findActive();
        $banks = \App\Models\Bank::active()->orderBy('id', 'DESC')->get();

        return view('site.cart.index', [
            'items' => $this->cart->content(),
            'cart' => $this->cart,
            'addresses' => $addresses,
            'states' => $states,
            'banks' => $banks,
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $this->cart->add(
            $product,
            (int) ($request->input('qty') ?? 1),
            $request->input('variant_id') ? (int) $request->input('variant_id') : null,
            $request->input('color'),
            $request->input('size')
        );

        // اگر درخواست از نوع AJAX (JSON) بود، پاسخ جیسون بده
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.added_to_cart'),
                'cart_count' => $this->cart->count(),
            ]);
        }

        // در غیر این صورت، رفتار عادی (رفرش صفحه) را حفظ کن
        return back()->with('success', __('messages.added_to_cart'));
    }

    public function update(Request $request, string $rowId)
    {
        $request->validate(['qty' => 'required|integer|min:1']);

        $item = $this->cart->content()->get($rowId);
        $maxQty = $item['stock'] ?? PHP_INT_MAX;
        $qty = min((int) ($request->input('qty') ?? 1), $maxQty);

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
