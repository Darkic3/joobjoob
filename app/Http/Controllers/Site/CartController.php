<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index()
    {
        return view('cart.index', ['items' => $this->cart->content(), 'cart' => $this->cart]);
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'qty' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $this->cart->add(
            $product,
            $request->get('qty', 1),
            $request->get('variant_id') ? (int) $request->get('variant_id') : null,
            $request->get('color'),
            $request->get('size')
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
        $this->cart->update($rowId, $request->qty);

        return back();
    }

    public function remove(string $rowId)
    {
        $this->cart->remove($rowId);

        return back();
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $ok = $this->cart->applyCoupon($request->code);

        return back()->with($ok ? 'success' : 'error', $ok ? __('messages.coupon_applied') : __('messages.coupon_invalid'));
    }
}
