<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class OrderService
{
    protected BankPaymentService $bankPaymentService;

    public function __construct(BankPaymentService $bankPaymentService)
    {
        $this->bankPaymentService = $bankPaymentService;
    }

    // ==================== جستجو ====================

    public static function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'shippingStatus', 'bank', 'shippingMethod', 'user'])
            ->find($id);
    }

    public static function findByField(string $field, mixed $value): ?Order
    {
        return Order::orderBy('id', 'DESC')->where($field, $value)->first();
    }

    // ==================== ایجاد سفارش ====================

    public function create(array $cartItems, array $orderData, ?string $userDescription = null): Order
    {
        $defaultStatus = OrderShippingStatus::default()->first();

        // محاسبه وزن کل
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
            'payment_price' => $orderData['payment_price'] ?? 0,
            'freight_balance' => $orderData['freight_balance'] ?? 0,
            'total_weight' => $weightSum,
            'user_description' => $userDescription,
            'shipping_status_id' => $defaultStatus?->id,
            'payment_receipt' => $orderData['payment_receipt'] ?? null,
        ]);

        // ایجاد آیتم‌های سفارش
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

        return $order->fresh(['items.product', 'shippingStatus', 'bank']);
    }

    // ==================== checkout ====================

    public function checkout(Order $order): RedirectResponse
    {
        $price = intval(str_replace(',', '', $order->deposit_price)) !== 0
            ? intval(str_replace(',', '', $order->deposit_price))
            : intval(str_replace(',', '', $order->payment_price));

        // سفارش رایگان (تخفیف کامل)
        if ($order->discount_id !== null && $price <= 0) {
            return $this->handleFreeOrder($order);
        }

        $bank = Bank::find($order->bank_id);

        if (! $bank) {
            return redirect(route('checkout.failed'))
                ->with('error', 'درگاه پرداخت یافت نشد.');
        }

        // کارت به کارت — نیاز به تأیید ادمین
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
        self::inventory($order);

        return redirect(route('checkout.success', $order->id));
    }

    // ==================== Callback پرداخت ====================

    public function callback(string $field, string $value, array $data): RedirectResponse
    {
        $order = self::findByField($field, $value);

        if (! $order) {
            return redirect(route('cart.index'))
                ->with('error', 'فاکتور معتبر نمی باشد');
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

        return redirect(route('checkout.failed'))
            ->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
    }

    public function handleSuccessOrderPayment(Order $order): RedirectResponse
    {
        if ($order->order_status === 'paid') {
            return redirect(route('checkout.success', $order->id));
        }

        $order->update([
            'order_status' => intval($order->deposit_price) != 0 ? 'deposit_paid' : 'paid',
        ]);

        self::inventory($order);

        // TODO: ارسال پیامک به کاربر و ادمین

        return redirect(route('checkout.success', $order->id));
    }

    // ==================== مدیریت موجودی ====================

    public static function inventory(Order $order): void
    {
        foreach ($order->items as $item) {
            self::setStockAndPrice($item);

            InventoryTransaction::create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'order_id' => $item->order_id,
                'type' => 'add',
                'description' => "سفارش #{$item->order_id}",
            ]);
        }
    }

    public static function setStockAndPrice(OrderItem $item): void
    {
        $product = Product::find($item->product_id);

        if (! $product) {
            return;
        }

        if ($item->product_variant_id) {
            $variant = ProductVariant::find($item->product_variant_id);

            if ($variant) {
                $variant->update([
                    'stock' => max(0, intval($variant->stock) - intval($item->quantity)),
                ]);

                $sumStock = $product->variants()->sum('stock');

                $minVariant = $product->variants()
                    ->orderBy('final_price', 'ASC')
                    ->where('stock', '<>', 0)
                    ->first();

                if ($minVariant) {
                    $product->update([
                        'price' => $minVariant->price,
                        'discount_price' => $minVariant->discounted_price,
                        'final_price' => $minVariant->final_price,
                        'stock' => $sumStock,
                    ]);
                }
            }
        } else {
            $product->update([
                'stock' => max(0, intval($product->stock) - intval($item->quantity)),
            ]);
        }

        $product->save();
    }
}
