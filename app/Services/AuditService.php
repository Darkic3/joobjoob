<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(
        string $event,
        $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null,
        ?string $description = null,
        ?int $userId = null,
    ): AuditLog {
        return AuditLog::create([
            'event' => $event,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'user_id' => $userId ?? Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'meta' => $meta,
            'description' => $description,
        ]);
    }

    public static function orderCreated(Order $order): AuditLog
    {
        $items = $order->items()->with('product', 'productVariant')->get()->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product ? ($item->product->trans('name') ?? $item->product->name) : null,
            'variant_id' => $item->product_variant_id,
            'variant_label' => $item->productVariant ? $item->productVariant->label : null,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'discounted_price' => $item->discounted_price,
            'weight' => $item->weight,
            'row_total' => $item->price * $item->quantity,
        ])->toArray();

        return self::log(
            'order.created', $order, null,
            [
                'order_status' => $order->order_status,
                'total_price' => $order->total_price,
                'discount_price' => $order->discount_price,
                'payment_price' => $order->payment_price,
                'shipping_price' => $order->shipping_price,
            ],
            [
                'items' => $items,
                'items_count' => count($items),
                'total_items_qty' => array_sum(array_column($items, 'quantity')),
                'shipping_method' => $order->shippingMethod ? $order->shippingMethod->title : null,
                'bank' => $order->bank ? $order->bank->title : null,
                'discount_code' => $order->discount ? $order->discount->title : null,
                'address' => $order->address,
                'user_description' => $order->user_description,
            ],
            'سفارش #' . $order->id . ' توسط کاربر #' . $order->user_id . ' ایجاد شد — مبلغ نهایی: ' . number_format($order->payment_price) . ' تومان'
        );
    }

    public static function orderStatusChanged(Order $order, string $oldStatus, string $newStatus, ?string $changedBy = null): AuditLog
    {
        $labels = [
            'paying' => 'در حال پرداخت', 'paid' => 'پرداخت شده',
            'deposit_paid' => 'بیعانه پرداخت شده', 'pending_confirmation' => 'در انتظار تأیید',
            'wait_for_verification' => 'در انتظار تأیید فیش', 'unpaid' => 'پرداخت نشده',
            'cancelled' => 'لغو شده',
        ];
        $oldLabel = isset($labels[$oldStatus]) ? $labels[$oldStatus] : $oldStatus;
        $newLabel = isset($labels[$newStatus]) ? $labels[$newStatus] : $newStatus;
        $actor = $changedBy ?? (Auth::check() ? Auth::user()->name : 'سیستم');

        return self::log(
            $newStatus === 'cancelled' ? 'order.cancelled' : 'order.status_changed',
            $order,
            ['order_status' => $oldStatus],
            ['order_status' => $newStatus],
            ['old_status_label' => $oldLabel, 'new_status_label' => $newLabel, 'changed_by' => $actor, 'payment_price' => $order->payment_price],
            'وضعیت سفارش #' . $order->id . ' از «' . $oldLabel . '» به «' . $newLabel . '» تغییر کرد'
        );
    }

    public static function orderStockReserved(Order $order, array $items): AuditLog
    {
        return self::log('order.stock_reserved', $order, null, null,
            ['items' => $items, 'total_items' => count($items)],
            'موجودی سفارش #' . $order->id . ' رزرو شد — ' . count($items) . ' آیتم'
        );
    }

    public static function orderStockRestored(Order $order, array $items): AuditLog
    {
        return self::log('order.stock_restored', $order, null, null,
            ['items' => $items, 'total_items' => count($items)],
            'موجودی سفارش #' . $order->id . ' بازگردانده شد — ' . count($items) . ' آیتم'
        );
    }

    public static function productPriceChanged(Product $product, int $oldPrice, int $newPrice, ?int $oldDiscount = null, ?int $newDiscount = null): AuditLog
    {
        $pName = $product->trans('name') ?? $product->name;
        return self::log('product.price_changed', $product,
            ['price' => $oldPrice, 'discount_price' => $oldDiscount],
            ['price' => $newPrice, 'discount_price' => $newDiscount],
            ['old_final_price' => ($oldDiscount && $oldDiscount > 0) ? $oldDiscount : $oldPrice, 'new_final_price' => ($newDiscount && $newDiscount > 0) ? $newDiscount : $newPrice],
            'قیمت محصول «' . $pName . '» از ' . number_format($oldPrice) . ' به ' . number_format($newPrice) . ' تومان تغییر کرد'
        );
    }

    public static function productStockChanged(Product $product, int $oldStock, int $newStock, ?string $reason = null, ?array $meta = null): AuditLog
    {
        $diff = $newStock - $oldStock;
        $event = $diff > 0 ? 'product.stock_added' : ($diff < 0 ? 'product.stock_deducted' : 'product.stock_changed');
        $pName = $product->trans('name') ?? $product->name;
        $extra = $reason ? ['reason' => $reason] : [];
        if ($meta) { $extra = array_merge($extra, $meta); }

        return self::log($event, $product, ['stock' => $oldStock], ['stock' => $newStock],
            array_merge(['difference' => $diff], $extra),
            'موجودی محصول «' . $pName . '» از ' . $oldStock . ' به ' . $newStock . ' تغییر کرد' . ($reason ? ' — دلیل: ' . $reason : '')
        );
    }

    public static function productUpdated(Product $product, array $oldValues, array $newValues): AuditLog
    {
        $pName = $product->trans('name') ?? $product->name;
        return self::log('product.updated', $product, $oldValues, $newValues, null, 'محصول «' . $pName . '» ویرایش شد');
    }

    public static function productCreated(Product $product): AuditLog
    {
        $pName = $product->trans('name') ?? $product->name;
        return self::log('product.created', $product, null,
            ['name' => $pName, 'price' => $product->price, 'stock' => $product->stock, 'sku' => $product->sku],
            null, 'محصول «' . $pName . '» با موجودی ' . $product->stock . ' و قیمت ' . number_format($product->price) . ' تومان ایجاد شد'
        );
    }
}
