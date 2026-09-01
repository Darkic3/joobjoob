<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AuditService;

class StockService
{
    public function reserveStock(Order $order): bool
    {
        $items = $order->items()->get();
        if ($items->isEmpty()) {
            return true;
        }
        return DB::transaction(function () use ($order, $items) {
            foreach ($items as $item) {
                $this->decreaseItemStock($item->product_id, $item->product_variant_id, $item->quantity, $order->id);
            }
            return true;
        });
    }

    public function restoreStock(Order $order): bool
    {
        $items = $order->items()->get();
        if ($items->isEmpty()) {
            return true;
        }
        return DB::transaction(function () use ($order, $items) {
            foreach ($items as $item) {
                $this->increaseItemStock($item->product_id, $item->product_variant_id, $item->quantity, $order->id);
            }
            return true;
        });
    }

    public function validateCartItems(array $cartItems): array
    {
        $unavailable = [];
        $insufficient = [];
        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? null;
            $variantId = $item['variant_id'] ?? null;
            $requestedQty = (int) ($item['qty'] ?? 1);
            if (!$productId) {
                continue;
            }
            $currentStock = $this->getAvailableStock($productId, $variantId);
            if ($currentStock <= 0) {
                $unavailable[] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $item['name'] ?? '',
                    'requested_qty' => $requestedQty,
                    'available_qty' => $currentStock,
                ];
            } elseif ($currentStock < $requestedQty) {
                $insufficient[] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $item['name'] ?? '',
                    'requested_qty' => $requestedQty,
                    'available_qty' => $currentStock,
                ];
            }
        }
        return ['unavailable' => $unavailable, 'insufficient' => $insufficient];
    }

    public function getAvailableStock(int $productId, ?int $variantId = null): int
    {
        if ($variantId) {
            $variant = ProductVariant::withTrashed()->find($variantId);
            return $variant ? max(0, (int) $variant->stock) : 0;
        }
        $product = Product::withTrashed()->find($productId);
        return $product ? max(0, (int) $product->stock) : 0;
    }

    public function enrichCartItemsWithStockStatus($cartItems)
    {
        return $cartItems->map(function ($item) {
            $stock = $this->getAvailableStock($item['product_id'] ?? 0, $item['variant_id'] ?? null);
            $item['is_in_stock'] = $stock > 0;
            $item['current_stock'] = $stock;
            return $item;
        });
    }

    private function decreaseItemStock(int $productId, ?int $variantId, int $quantity, int $orderId): void
    {
        if ($variantId) {
            $this->decreaseVariantStock($productId, $variantId, $quantity, $orderId);
        } else {
            $this->decreaseProductStock($productId, $quantity, $orderId);
        }
    }

    private function decreaseProductStock(int $productId, int $quantity, int $orderId): void
    {
        $product = Product::where('id', $productId)->lockForUpdate()->first();
        if (!$product) {
            Log::warning("StockService: Product #{$productId} not found for order #{$orderId}");
            return;
        }
        if ((int) $product->stock < $quantity) {
            throw new \RuntimeException(
                'موجودی محصول «' . ($product->trans('name') ?: $product->name) . '» کافی نیست. '
                . 'موجودی فعلی: ' . $product->stock . '، تعداد درخواستی: ' . $quantity
            );
        }
        $product->decrement('stock', $quantity);
        InventoryTransaction::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'type' => 'subtract',
            'order_id' => $orderId,
            'description' => "رزرو موجودی — سفارش #{$orderId}",
        ]);
        try { AuditService::productStockChanged($product, $product->stock + $quantity, $product->stock, 'رزرو سفارش #' . $orderId, ['order_id' => $orderId, 'quantity' => $quantity]); } catch (\Throwable $e) { Log::error('AuditLog stock failed', ['message' => $e->getMessage()]); }
    }

    private function decreaseVariantStock(int $productId, int $variantId, int $quantity, int $orderId): void
    {
        $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
        if (!$variant) {
            Log::warning("StockService: Variant #{$variantId} not found for order #{$orderId}");
            return;
        }
        if ((int) $variant->stock < $quantity) {
            $product = Product::withTrashed()->find($productId);
            $label = $variant->label ?: "واریانت #{$variantId}";
            throw new \RuntimeException(
                'موجودی واریانت «' . $label . '» محصول «' . ($product?->trans('name') ?: 'نامشخص') . '» کافی نیست. '
                . 'موجودی فعلی: ' . $variant->stock . '، تعداد درخواستی: ' . $quantity
            );
        }
        $variant->decrement('stock', $quantity);
        $product = Product::find($productId);
        if ($product) {
            $sumStock = $product->variants()->sum('stock');
            $product->update(['stock' => $sumStock]);
        }
        InventoryTransaction::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'type' => 'subtract',
            'order_id' => $orderId,
            'description' => "رزرو موجودی واریانت — سفارش #{$orderId}",
        ]);
        try { AuditService::productStockChanged($product, $product->stock + $quantity, $product->stock, 'رزرو واریانت سفارش #' . $orderId, ['order_id' => $orderId, 'variant_id' => $variantId, 'quantity' => $quantity]); } catch (\Throwable $e) { Log::error('AuditLog variant stock failed', ['message' => $e->getMessage()]); }
    }

    private function increaseItemStock(int $productId, ?int $variantId, int $quantity, int $orderId): void
    {
        if ($variantId) {
            $this->increaseVariantStock($productId, $variantId, $quantity, $orderId);
        } else {
            $this->increaseProductStock($productId, $quantity, $orderId);
        }
    }

    private function increaseProductStock(int $productId, int $quantity, int $orderId): void
    {
        $product = Product::where('id', $productId)->lockForUpdate()->first();
        if (!$product) {
            Log::warning("StockService: Product #{$productId} not found for restoreStock order #{$orderId}");
            return;
        }
        $product->increment('stock', $quantity);
        InventoryTransaction::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'type' => 'add',
            'order_id' => $orderId,
            'description' => "بازگردانی موجودی — سفارش #{$orderId} لغو شده",
        ]);
        try { AuditService::productStockChanged($product, $product->stock - $quantity, $product->stock, 'بازگردانی سفارش #' . $orderId, ['order_id' => $orderId, 'quantity' => $quantity, 'reason' => 'cancel_or_fail']); } catch (\Throwable $e) { Log::error('AuditLog stock failed', ['message' => $e->getMessage()]); }
    }

    private function increaseVariantStock(int $productId, int $variantId, int $quantity, int $orderId): void
    {
        $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();
        if (!$variant) {
            Log::warning("StockService: Variant #{$variantId} not found for restoreStock order #{$orderId}");
            return;
        }
        $variant->increment('stock', $quantity);
        $product = Product::find($productId);
        if ($product) {
            $sumStock = $product->variants()->sum('stock');
            $product->update(['stock' => $sumStock]);
        }
        InventoryTransaction::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'type' => 'add',
            'order_id' => $orderId,
            'description' => "بازگردانی موجودی واریانت — سفارش #{$orderId} لغو شده",
        ]);
        try { AuditService::productStockChanged($product, $product->stock - $quantity, $product->stock, 'بازگردانی واریانت سفارش #' . $orderId, ['order_id' => $orderId, 'variant_id' => $variantId, 'quantity' => $quantity, 'reason' => 'cancel_or_fail']); } catch (\Throwable $e) { Log::error('AuditLog variant stock failed', ['message' => $e->getMessage()]); }
    }
}
