<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\CityService;
use App\Helpers\NumberHelper;
use App\Services\StateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()->latest()->take(5)->get();
        return view('site.dashboard.index', compact('orders'));
    }

    // ==================== سفارشات ====================

    public function orders()
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);
        return view('site.dashboard.orders', compact('orders'));
    }

    public function orderDetail(int $id)
    {
        $order = Order::with(['items.product', 'items.product.images', 'items.productVariant', 'shippingStatus', 'shippingMethod', 'city', 'state', 'bank', 'histories.shippingStatus'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('site.dashboard.order-detail', compact('order'));
    }

    /**
     * ویرایش اطلاعات فیش واریزی (کد پیگیری و شماره کارت) — فقط برای کارت به کارت
     */
    public function updateReceipt(Request $request, int $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // فقط برای سفارش‌های کارت به کارت و فقط در حالت pending_confirmation
        $isCardToCard = in_array($order->bank?->bank_type ?? '', ['card_to_card', 'card-to-card']);
        if (! $isCardToCard || $order->order_status !== 'pending_confirmation') {
            return redirect()->back()->with('error', 'امکان ویرایش اطلاعات فیش برای این سفارش وجود ندارد.');
        }

        $validated = $request->validate([
            'receipt_tracking_code' => 'required|string|max:255',
            'receipt_payer_card' => 'nullable|string|max:255',
        ], [
            'receipt_tracking_code.required' => 'کد پیگیری واریزی الزامی است.',
        ]);

        $order->update([
            'receipt_tracking_code' => $validated['receipt_tracking_code'],
            'receipt_payer_card' => $validated['receipt_payer_card'] ?? null,
        ]);

        return redirect()->route('dashboard.order-detail', $order->id)
            ->with('success', 'اطلاعات فیش واریزی با موفقیت به‌روزرسانی شد.');
    }

    public function orderFactor(int $id)
    {
        $order = Order::with(['items.product', 'shippingMethod', 'city'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('site.dashboard.order-factor', compact('order'));
    }

    // ==================== آدرس‌ها ====================

    public function addresses()
    {
        $addresses = AddressService::find()['addresses'] ?? collect();
        $states = StateService::findActive();

        return view('site.dashboard.addresses', compact('addresses', 'states'));
    }

    /**
     * دریافت شهرها (AJAX)
     */
    public function cities(Request $request)
    {
        $cities = CityService::findActiveByState($request->get('state_id'));
        return response()->json(['cities' => $cities]);
    }

    /**
     * ایجاد آدرس جدید
     */
    public function createAddress(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:1000',
            'receiptor_full_name' => 'required|string|max:255',
            'receiptor_mobile' => 'required|string|max:20',
            'postal_code' => 'required|string|max:20',
        ]);

        AddressService::create(
            Auth::id(),
            $request->state_id,
            $request->city_id,
            $request->address,
            NumberHelper::persian2LatinDigit($request->postal_code),
            $request->receiptor_full_name,
            NumberHelper::persian2LatinDigit($request->receiptor_mobile)
        );

        $redirect = $request->get('redirect_to') ? url($request->get('redirect_to')) : route('dashboard.addresses');
        return redirect($redirect)->with('success', 'آدرس با موفقیت اضافه شد');
    }

    /**
     * ویرایش آدرس
     */
    public function updateAddress(Request $request, int $id)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:1000',
            'receiptor_full_name' => 'required|string|max:255',
            'receiptor_mobile' => 'required|string|max:20',
            'postal_code' => 'required|string|max:20',
        ]);

        AddressService::update(
            $id,
            Auth::id(),
            $request->state_id,
            $request->city_id,
            $request->address,
            NumberHelper::persian2LatinDigit($request->postal_code),
            $request->receiptor_full_name,
            NumberHelper::persian2LatinDigit($request->receiptor_mobile)
        );

        $redirect = $request->get('redirect_to') ? url($request->get('redirect_to')) : route('dashboard.addresses');
        return redirect($redirect)->with('success', 'آدرس با موفقیت ویرایش شد');
    }

    /**
     * حذف آدرس
     */
    public function deleteAddress(int $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $address->delete();

        return redirect()->back()->with('success', 'آدرس حذف شد');
    }

    // ==================== علاقه‌مندی‌ها ====================

    public function wishlist()
    {
        return view('site.dashboard.wishlist');
    }
}
