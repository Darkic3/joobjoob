<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\Product;
use App\Models\ProductVariant;
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

    // === create order ===

    public function create(array $cartItems, array $orderData, ?string $userDescription = null): Order
    {
        $defaultStatus = OrderShippingStatus::default()->first();

        $weightSum = 0;
        foreach ($cartItems as $item) {
            $weight = $item['weight'] ?? 0;
            $weightSum += $weight * ($item['qty'] ?? 1);
        }

        $order = Order::create([
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
            'payment_receipt' => $orderData['payment_receipt'] ?? null,
        ]);

        foreach ($cartItems as $item) {
            $weight = $item['weight'] ?? 0;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['qty'] ?? 1,
                'weight' => $weight,
                'price' => $item['price'] ?? 0,
                'discounted_price' => $item['price'] ?? 0,
            ]);
        }

        try {
            $this->stockService->reserveStock($order);
        } catch (\RuntimeException $e) {
            Log::warning('Stock reservation failed for order #' . $order->id, ['message' => $e->getMessage()]);
            $order->forceDelete();
            throw $e;
        }

        try { AuditService::orderCreated($order); } catch (\Throwable $e) { Log::error('AuditLog failed for order #' . $order->id, ['message' => $e->getMessage()]); }

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
            $order->update(['order_status' => 'pending_confirmation']);
            return redirect(route('checkout.success', $order->id))
                ->with('success', 'سفارش شما ثبت شد و پس از تأیید پرداخت، سفارشتان پردازش می‌شود.');
        }

        return $this->bankPaymentService->handlePayment($bank, $order, $price);
    }

    private function handleFreeOrder(Order $order): RedirectResponse
    {
        $order->update(['order_status' => 'paid']);
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
        $order->update([
            'order_status' => intval($order->deposit_price) != 0 ? 'deposit_paid' : 'paid',
        ]);
        try { AuditService::orderStatusChanged($order, $oldStatus, $order->order_status, 'سیستم - پرداخت موفق'); } catch (\Throwable $e) { Log::error('AuditLog failed', ['message' => $e->getMessage()]); }

        session()->flash('success', 'سفارش شما با موفقیت ثبت شد.');
        return redirect(route('checkout.success', $order->id));
    }

    public function handleFailedPayment(Order $order): void
    {
        if ($order->order_status === 'unpaid') {
            return;
        }
        $oldStatus = $order->order_status;
        $order->update(['order_status' => 'unpaid']);
        try { AuditService::orderStatusChanged($order, $oldStatus, 'unpaid', 'سیستم - پرداخت ناموفق'); } catch (\Throwable $e) { Log::error('AuditLog failed', ['message' => $e->getMessage()]); }
        try {
            $this->stockService->restoreStock($order);
        } catch (\Exception $e) {
            Log::error('Stock restoration failed for failed payment order #' . $order->id, [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function cancelOrder(Order $order): void
    {
        $previousStatus = $order->order_status;
        $order->update(['order_status' => 'cancelled']);
        try { AuditService::orderStatusChanged($order, $previousStatus, 'cancelled', Auth::check() ? Auth::user()->name : 'سیستم'); } catch (\Throwable $e) { Log::error('AuditLog failed', ['message' => $e->getMessage()]); }

        if (in_array($previousStatus, ['paid', 'deposit_paid', 'pending_confirmation', 'wait_for_verification'])) {
            try {
                $this->stockService->restoreStock($order);
            } catch (\Exception $e) {
                Log::error('Stock restoration failed for cancelled order #' . $order->id, [
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
