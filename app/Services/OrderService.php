<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class OrderService
{
    protected BankPaymentService $bankPaymentService;
    protected StockService $stockService;

    public function __construct(BankPaymentService $bankPaymentService, StockService $stockService)
    {
        $this->bankPaymentService = $bankPaymentService;
        $this->stockService = $stockService;
    }

    // === search ===

    public static function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'shippingStatus', 'bank', 'shippingMethod', 'user'])
            ->find($id);
    }

    public static function findByField(string $field, mixed $value): ?Order
    {
        return Order::orderBy('id', 'DESC')->where($field, $value)->first();
    }

    // === Central status transition with stock + audit ===

    /**
     * تغییر وضعیت سفارش به صورت مرکزی با مدیریت خودکار موجودی و لاگ.
     *
     * قوانین موجودی:
     *  - از وضعیت آزاد (cancelled/unpaid) به وضعیت رزرو (paying/paid/...) => موجودی دوباره کم می‌شود (re-reserve)
     *  - از وضعیت رزرو به آزاد => موجودی بازگردانده می‌شود (restore)
     *  - بین دو وضعیت هر دو رزرو یا هر دو آزاد => هیچ تغییر موجودی
     *
     * @throws \RuntimeException اگر موجودی کافی نباشد (برای re-reserve)
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $actor = null): void
    {
        $allowed = ['paying','paid','deposit_paid','pending_confirmation','wait_for_verification','unpaid','cancelled'];
        if (! in_array($newStatus, $allowed, true)) {
            throw new \InvalidArgumentException("وضعیت نامعتبر: {$newStatus}");
        }

        if ($order->order_status === $newStatus) {
            return;
        }

        $oldStatus = $order->order_status;
        $oldReserved = Order::isStockReservedStatus($oldStatus);
        $newReserved = Order::isStockReservedStatus($newStatus);
        $actorName = $actor ?? (Auth::check() ? Auth::user()->name : 'سیستم');

        Log::info("Order status transition requested #{$order->id}: {$oldStatus} -> {$newStatus}", [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_reserved' => $oldReserved,
            'new_reserved' => $newReserved,
            'actor' => $actorName,
        ]);

        // re-reserve: آزاد -> رزرو  => باید قبل از تغییر وضعیت موجودی کم شود، در صورت شکست وضعیت تغییر نمی‌کند
        if (! $oldReserved && $newReserved) {
            // این متد در صورت کمبود موجودی RuntimeException پرتاب می‌کند و تراکنش متوقف می‌شود
            $this->stockService->reserveStock($order);
            Log::info("Order #{$order->id}: stock re-reserved on transition {$oldStatus} -> {$newStatus}", [
                'order_id' => $order->id,
                'actor' => $actorName,
            ]);
        }

        // تغییر وضعیت
        $order->update(['order_status' => $newStatus]);

        // ثبت Audit برای تغییر وضعیت
        try {
            AuditService::orderStatusChanged($order, $oldStatus, $newStatus, $actorName);
        } catch (\Throwable $e) {
            Log::error('AuditLog orderStatusChanged failed for order #' . $order->id, ['message' => $e->getMessage()]);
        }

        // restore: رزرو -> آزاد  => بعد از تغییر وضعیت موجودی بازگردانده شود
        if ($oldReserved && ! $newReserved) {
            try {
                $this->stockService->restoreStock($order);
                Log::info("Order #{$order->id}: stock restored on transition {$oldStatus} -> {$newStatus}", [
                    'order_id' => $order->id,
                    'actor' => $actorName,
                ]);
            } catch (\Throwable $e) {
                Log::error('Stock restoration failed for order #' . $order->id . " ({$oldStatus} -> {$newStatus})", [
                    'message' => $e->getMessage(),
                    'actor' => $actorName,
                ]);
            }
        }

        // Notification برای حالات مهم
        try {
            $notifService = app(NotificationService::class);
            if ($newStatus === 'cancelled') {
                $notifService->notifyUser(
                    $order->user,
                    'order.cancelled',
                    'سفارش شما لغو شد',
                    null,
                    route('dashboard.order-detail', $order->id),
                    ['order_id' => $order->id, 'from' => $oldStatus, 'to' => $newStatus]
                );
            } elseif (in_array($newStatus, ['paid', 'deposit_paid'], true) && in_array($oldStatus, ['cancelled','unpaid'], true)) {
                // بازگشت از لغو/پرداخت‌نشده به پرداخت‌شده (re-activate) — اطلاع به کاربر
                $notifService->notifyUser(
                    $order->user,
                    'order.paid',
                    'سفارش شما فعال شد',
                    null,
                    route('dashboard.order-detail', $order->id),
                    ['order_id' => $order->id, 'amount' => $order->payment_price, 'from' => $oldStatus, 'to' => $newStatus]
                );
                $notifService->notifyAdmins(
                    'order.paid_admin',
                    'سفارش لغو شده فعال شد',
                    null,
                    route('admin.orders.show', $order->id),
                    ['order_id' => $order->id, 'user_name' => $order->user?->name, 'from' => $oldStatus, 'to' => $newStatus, 'amount' => $order->payment_price]
                );
            }
        } catch (\Throwable $e) {
            Log::error('Notification failed for order #' . $order->id . " status {$oldStatus} -> {$newStatus}", ['message' => $e->getMessage()]);
        }
    }

    // === create order ===

    public function create(array $cartItems, array $orderData, ?string $userDescription = null): Order
    {
        $defaultStatus = OrderShippingStatus::default()->first();

        $weightSum = 0;
        foreach ($cartItems as $item) {
            $weight = $item['weight'] ?? 0;
            $weightSum += $weight * ($item['qty'] ?? 1);
        }

        // P0-09: Wrap order + items in a DB transaction for atomicity
        $order = DB::transaction(function () use ($cartItems, $orderData, $userDescription, $defaultStatus, $weightSum) {
            $o = Order::create([
                'user_id' => Auth::id(),
                'state_id' => $orderData['state_id'] ?? null,
                'city_id' => $orderData['city_id'] ?? null,
                'address_id' => $orderData['address_id'] ?? null,
                'address' => [
                    'state' => $orderData['state_name'] ?? '',
                    'city' => $orderData['city_name'] ?? '',
                    'address' => $orderData['full_address'] ?? '',
                    'receiptor_mobile' => $orderData['receiptor_mobile'] ?? '',
                    'postal_code' => $orderData['postal_code'] ?? '',
                ],
                'receiptor_full_name' => $orderData['receiptor_full_name'] ?? null,
                'shipping_method_id' => $orderData['shipping_method_id'] ?? null,
                'bank_id' => $orderData['bank_id'] ?? null,
                'order_status' => 'paying',
                'shipping_price' => $orderData['shipping_price'] ?? 0,
                'total_price' => $orderData['total_price'] ?? 0,
                'discount_price' => $orderData['discount_price'] ?? 0,
                'discount_id' => $orderData['discount_id'] ?? null,
                'payment_price' => $orderData['payment_price'] ?? 0,
                'freight_balance' => $orderData['freight_balance'] ?? 0,
                'total_weight' => $weightSum,
                'user_description' => $userDescription,
                'shipping_status_id' => $defaultStatus?->id,
                'receipt_tracking_code' => $orderData['receipt_tracking_code'] ?? null,
                'receipt_payer_card' => $orderData['receipt_payer_card'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $weight = $item['weight'] ?? 0;
                OrderItem::create([
                    'order_id' => $o->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['qty'] ?? 1,
                    'weight' => $weight,
                    'price' => $item['price'] ?? 0,
                    'discounted_price' => $item['price'] ?? 0,
                ]);
            }

            return $o;
        });

        // P0-09: Never forceDelete — mark as cancelled instead to preserve audit trail
        try {
            $this->stockService->reserveStock($order);
        } catch (\RuntimeException $e) {
            Log::warning('Stock reservation failed for order #' . $order->id, ['message' => $e->getMessage()]);
            // موجودی کافی نیست => سفارش به cancelled تغییر کند (بدون بازگردانی چون هنوز رزرو نشده بود)
            // مستقیم update می‌کنیم تا حلقه re-reserve رخ ندهد
            $order->update(['order_status' => 'cancelled']);
            try { AuditService::orderStatusChanged($order, 'paying', 'cancelled', 'سیستم - کمبود موجودی'); } catch (\Throwable $ex) { Log::error('AuditLog failed', ['message' => $ex->getMessage()]); }
            throw $e;
        }

        try { AuditService::orderCreated($order); } catch (\Throwable $e) { Log::error('AuditLog failed for order #' . $order->id, ['message' => $e->getMessage()]); }

        // ── Notification: new order ──
        try {
            $notifService = app(NotificationService::class);
            $paymentPrice = number_format($order->payment_price);
            $notifService->notifyUser(
                $order->user,
                'order.created',
                'سفارش شما ثبت شد',
                null,
                route('dashboard.order-detail', $order->id),
                ['order_id' => $order->id, 'amount' => $order->payment_price]
            );
            $notifService->notifyAdmins(
                'order.new',
                'سفارش جدید ثبت شد',
                null,
                route('admin.orders.show', $order->id),
                ['order_id' => $order->id, 'user_name' => $order->user->name, 'amount' => $order->payment_price]
            );
        } catch (\Throwable $e) { Log::error('Notification failed for order #' . $order->id, ['message' => $e->getMessage()]); }

        return $order->fresh(['items.product', 'shippingStatus', 'bank']);
    }

    // === checkout ===

    public function checkout(Order $order): RedirectResponse
    {
        $price = intval(str_replace(',', '', $order->deposit_price)) !== 0
            ? intval(str_replace(',', '', $order->deposit_price))
            : intval(str_replace(',', '', $order->payment_price));

        if ($order->discount_id !== null && $price <= 0) {
            return $this->handleFreeOrder($order);
        }

        $bank = Bank::find($order->bank_id);
        if (! $bank) {
            return redirect(route('checkout.failed'))->with('error', 'درگاه پرداخت یافت نشد.');
        }

        if ($bank->bank_type === 'card_to_card') {
            // card_to_card: paying (reserved) -> pending_confirmation (reserved) : بدون تغییر موجودی
            $old = $order->order_status;
            $order->update(['order_status' => 'pending_confirmation']);
            try { AuditService::orderStatusChanged($order, $old, 'pending_confirmation', Auth::check() ? Auth::user()->name : 'سیستم'); } catch (\Throwable $e) { Log::error('AuditLog failed', ['message' => $e->getMessage()]); }
            // ثبت استفاده از کد تخفیف برای سفارش کارت‌به‌کارت (مبلغ واقعی سفارش)
            $couponSession = session('coupon');
            if ($order->discount_id && $couponSession) {
                try {
                    $couponService = app(\App\Services\CouponService::class);
                    $couponService->recordUsage(
                        $order->discount_id,
                        $order->user_id,
                        $order->id,
                        (float) $order->discount_price
                    );
                } catch (\Throwable $e) {
                    Log::error('Coupon recordUsage failed for card-to-card order #' . $order->id, ['message' => $e->getMessage()]);
                }
            }
            session()->forget('coupon');
            return redirect(route('checkout.success', $order->id))
                ->with('success', 'سفارش شما ثبت شد و پس از تأیید پرداخت، سفارشتان پردازش می‌شود.');
        }

        return $this->bankPaymentService->handlePayment($bank, $order, $price);
    }

    private function handleFreeOrder(Order $order): RedirectResponse
    {
        // free order: paying (reserved) -> paid (reserved) : بدون تغییر موجودی
        $oldStatus = $order->order_status;
        $order->update(['order_status' => 'paid']);
        try { AuditService::orderStatusChanged($order, $oldStatus, 'paid', 'سیستم - سفارش رایگان'); } catch (\Throwable $e) { Log::error('AuditLog failed', ['message' => $e->getMessage()]); }

        // ثبت استفاده از کد تخفیف برای سفارش رایگان (P1-07)
        // مبلغ واقعی ذخیره‌شده روی سفارش ثبت می‌شود، نه مقدار لحظه‌ای سشن
        $couponSession = session('coupon');
        if ($order->discount_id && $couponSession) {
            try {
                $couponService = app(\App\Services\CouponService::class);
                $couponService->recordUsage(
                    $order->discount_id,
                    $order->user_id,
                    $order->id,
                    (float) $order->discount_price
                );
            } catch (\Throwable $e) {
                Log::error('Coupon recordUsage failed for free order #' . $order->id, ['message' => $e->getMessage()]);
            }
        }
        session()->forget('coupon');

        session()->flash('success', 'سفارش شما با موفقیت ثبت شد.');
        return redirect(route('checkout.success', $order->id));
    }

    // === payment callback ===

    public function callback(string $field, string $value, array $data): RedirectResponse
    {
        $order = self::findByField($field, $value);
        if (! $order) {
            return redirect(route('cart.index'))->with('error', 'فاکتور معتبر نمی باشد');
        }
        if (in_array($order->order_status, ['paid', 'deposit_paid'])) {
            return redirect(route('checkout.success', $order->id));
        }

        $price = intval(str_replace(',', '', $order->deposit_price)) !== 0
            ? intval(str_replace(',', '', $order->deposit_price))
            : intval(str_replace(',', '', $order->payment_price));

        $isSuccess = $this->bankPaymentService->handleVerify($order, $data, $price);
        if ($isSuccess) {
            return $this->handleSuccessOrderPayment($order);
        }

        $this->handleFailedPayment($order);
        return redirect(route('checkout.failed'))->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
    }

    public function handleSuccessOrderPayment(Order $order): RedirectResponse
    {
        if ($order->order_status === 'paid') {
            session()->flash('success', 'سفارش شما با موفقیت ثبت شد.');
            return redirect(route('checkout.success', $order->id));
        }

        $oldStatus = $order->order_status;
        $newStatus = intval($order->deposit_price) != 0 ? 'deposit_paid' : 'paid';

        // این مسیر معمولا paying (reserved) -> paid (reserved) است => بدون تغییر موجودی
        // اما اگر به هر دلیل از unpaid/cancelled بیاید، re-reserve انجام خواهد شد
        try {
            $this->updateOrderStatus($order, $newStatus, 'سیستم - پرداخت موفق');
        } catch (\RuntimeException $e) {
            Log::error('Re-reserve failed on payment success for order #' . $order->id, ['message' => $e->getMessage()]);
            return redirect(route('checkout.failed'))->with('error', 'پرداخت موفق بود اما موجودی محصول کافی نیست. با پشتیبانی تماس بگیرید.');
        }

        // ── ثبت استفاده از کد تخفیف (فقط پس از تایید پرداخت — P1-07) ──
        // مبلغ واقعی ذخیره‌شده روی سفارش ثبت می‌شود، نه مقدار لحظه‌ای سشن
        $couponSession = session('coupon');
        if ($order->discount_id && $couponSession) {
            try {
                $couponService = app(\App\Services\CouponService::class);
                $couponService->recordUsage(
                    $order->discount_id,
                    $order->user_id,
                    $order->id,
                    (float) $order->discount_price
                );
            } catch (\Throwable $e) {
                Log::error('Coupon recordUsage failed for order #' . $order->id, ['message' => $e->getMessage()]);
            }
        }
        session()->forget('coupon');

        // ── Notification: payment success (برای حالت معمول قبلاً در updateOrderStatus برای re-activate ارسال شده، برای paying->paid هم باید ارسال شود) ──
        if ($oldStatus === 'paying') {
            try {
                $notifService = app(NotificationService::class);
                $notifService->notifyUser(
                    $order->user,
                    'order.paid',
                    'پرداخت سفارش تایید شد',
                    null,
                    route('dashboard.order-detail', $order->id),
                    ['order_id' => $order->id, 'amount' => $order->payment_price]
                );
                $notifService->notifyAdmins(
                    'order.paid_admin',
                    'پرداخت سفارش تایید شد',
                    null,
                    route('admin.orders.show', $order->id),
                    ['order_id' => $order->id, 'user_name' => $order->user->name, 'amount' => $order->payment_price]
                );
            } catch (\Throwable $e) { Log::error('Notification failed for order #' . $order->id, ['message' => $e->getMessage()]); }
        }

        session()->flash('success', 'سفارش شما با موفقیت ثبت شد.');
        return redirect(route('checkout.success', $order->id));
    }

    public function handleFailedPayment(Order $order): void
    {
        if ($order->order_status === 'unpaid') {
            return;
        }
        try {
            $this->updateOrderStatus($order, 'unpaid', 'سیستم - پرداخت ناموفق');
        } catch (\Throwable $e) {
            Log::error('handleFailedPayment failed for order #' . $order->id, ['message' => $e->getMessage()]);
        }
    }

    public function cancelOrder(Order $order): void
    {
        if ($order->order_status === 'cancelled') {
            return;
        }
        $this->updateOrderStatus($order, 'cancelled', Auth::check() ? Auth::user()->name : 'سیستم');
    }
}
