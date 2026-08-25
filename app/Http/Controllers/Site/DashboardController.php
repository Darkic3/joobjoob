<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\CityService;
use App\Services\NumberHelper;
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
        $order = Order::with(['items.product', 'shippingStatus', 'shippingMethod', 'city', 'state'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('site.dashboard.order-detail', compact('order'));
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

        return redirect()->back()->with('success', 'آدرس با موفقیت اضافه شد');
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

        return redirect()->back()->with('success', 'آدرس با موفقیت ویرایش شد');
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
