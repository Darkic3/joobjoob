<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreAddressRequest;
use App\Http\Requests\Site\UpdateReceiptRequest;
use App\Models\Address;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\AuditService;
use App\Services\CityService;
use App\Services\NotificationService;
use App\Services\StateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()->latest()->take(5)->get();
        $newsletterService = app(\App\Services\NewsletterService::class);
        $newsletterEnabled = $newsletterService->isEnabled();
        $newsletterSubscribed = $newsletterEnabled && $newsletterService->isSubscribed(Auth::user()->email);

        return view('site.dashboard.index', compact('orders', 'newsletterEnabled', 'newsletterSubscribed'));
    }

    // ==================== ایمیل حساب کاربری ====================

    /**
     * ثبت/ویرایش ایمیل حساب کاربری.
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190', 'unique:users,email,' . Auth::id()],
        ], [
            'email.required' => __('validation.requests.newsletter.email_required'),
            'email.email' => __('validation.requests.newsletter.email_invalid'),
        ]);

        Auth::user()->update(['email' => mb_strtolower(trim($validated['email']))]);

        return back()->with('success', __('messages.email_saved'));
    }

    /**
     * حذف ایمیل حساب کاربری (+ غیرفعال‌سازی عضویت خبرنامه همان کاربر).
     */
    public function destroyEmail()
    {
        $user = Auth::user();
        $user->update(['email' => null]);

        \App\Models\NewsletterSubscriber::where('user_id', $user->id)->update(['is_active' => false]);

        return back()->with('success', __('messages.email_removed'));
    }

    /**
     * روشن/خاموش کردن عضویت خبرنامه برای ایمیل حساب کاربری (AJAX).
     */
    public function toggleNewsletter()
    {
        $service = app(\App\Services\NewsletterService::class);
        $user = Auth::user();

        if (! $service->isEnabled()) {
            return response()->json(['success' => false, 'message' => __('messages.newsletter_disabled')], 403);
        }

        if (! $user->email) {
            return response()->json(['success' => false, 'message' => __('messages.email_required_first')], 422);
        }

        if ($service->isSubscribed($user->email)) {
            $result = $service->unsubscribe($user->email, $user->id);
        } else {
            $result = $service->subscribe($user->email, $user->id);
        }

        return response()->json([
            'success' => $result['success'],
            'subscribed' => $service->isSubscribed($user->email),
            'message' => $result['message'],
        ]);
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
    public function updateReceipt(UpdateReceiptRequest $request, int $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        // فقط برای سفارش‌های کارت به کارت
        $isCardToCard = in_array($order->bank?->bank_type ?? '', ['card_to_card', 'card-to-card']);
        if (! $isCardToCard) {
            return redirect()->back()->with('error', 'امکان ویرایش اطلاعات پرداخت برای این سفارش وجود ندارد.');
        }

        $oldTrackingCode = $order->receipt_tracking_code;
        $oldPayerCard = $order->receipt_payer_card;

        $order->update([
            'receipt_tracking_code' => $request->validated('receipt_tracking_code'),
            'receipt_payer_card' => $request->validated('receipt_payer_card') ?? null,
        ]);

        $order->refresh();

        // ── Audit: receipt info change history ──
        try {
            $newTrackingCode = $order->receipt_tracking_code;
            $newPayerCard = $order->receipt_payer_card;
            if ($oldTrackingCode !== $newTrackingCode || $oldPayerCard !== $newPayerCard) {
                AuditService::orderReceiptUpdated(
                    $order,
                    $oldTrackingCode,
                    $newTrackingCode,
                    $oldPayerCard,
                    $newPayerCard
                );
            }
        } catch (\Throwable $e) { Log::error('Card-to-card audit failed for order #' . $order->id, ['message' => $e->getMessage()]); }

        // ── Notification: card-to-card receipt submitted ──
        try {
            $notifService = app(NotificationService::class);
            $paymentPrice = number_format($order->payment_price);
            $trackingCode = $request->validated('receipt_tracking_code');
            $notifService->notifyAdmins(
                'order.card_to_card',
                'فیش واریزی جدید دریافت شد',
                null,
                route('admin.orders.show', $order->id),
                ['order_id' => $order->id, 'user_name' => $order->user->name, 'tracking_code' => $trackingCode, 'amount' => $order->payment_price]
            );
        } catch (\Throwable $e) { Log::error('Card-to-card notification failed for order #' . $order->id, ['message' => $e->getMessage()]); }

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
     * ایجاد آدرس جدید.
     *
     * ولیدیشن کامل سمت سرور توسط StoreAddressRequest انجام می‌شود (ارقام فارسی
     * نرمال می‌شوند؛ موبایل/کدپستی با فرمت واقعی چک می‌شوند). خطا → back()->withErrors
     * با حفظ ورودی‌ها (old()) — هرگز 500.
     */
    public function createAddress(StoreAddressRequest $request)
    {
        $data = $request->validated();

        AddressService::create(
            Auth::id(),
            (int) $data['state_id'],
            (int) $data['city_id'],
            $data['address'],
            $data['postal_code'],
            $data['receiptor_full_name'],
            $data['receiptor_mobile']
        );

        $redirect = $request->get('redirect_to') ? url($request->get('redirect_to')) : route('dashboard.addresses');

        return redirect($redirect)->with('success', 'آدرس با موفقیت اضافه شد');
    }

    /**
     * ویرایش آدرس — همان اعتبارسنجی StoreAddressRequest.
     */
    public function updateAddress(StoreAddressRequest $request, int $id)
    {
        // مالکیت آدرس باید متعلق به کاربر فعلی باشد
        \App\Models\Address::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validated();

        AddressService::update(
            $id,
            Auth::id(),
            (int) $data['state_id'],
            (int) $data['city_id'],
            $data['address'],
            $data['postal_code'],
            $data['receiptor_full_name'],
            $data['receiptor_mobile']
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
        $wishlistService = app(\App\Services\WishlistService::class);
        $products = $wishlistService->content();

        return view('site.dashboard.wishlist', compact('products'));
    }
}
