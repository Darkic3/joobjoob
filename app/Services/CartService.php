<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * سرویس سبد خرید مبتنی بر Session.
 * ساختار هر ردیف: [rowId, product_id, variant_id, name, price, qty, weight, slug, image, ...]
 */
class CartService
{
    protected string $sessionKey = 'cart';

    public function content(): Collection
    {
        return collect(session($this->sessionKey, []));
    }

    /**
     * افزودن به سبد خرید
     */
    public function add(Product $product, int $qty = 1, ?int $variantId = null, ?string $color = null, ?string $size = null): string
    {
        $cart = $this->content();
        $qty = max(1, $qty);
        $rowId = md5($product->id . ($variantId ?? '') . ($color ?? '') . ($size ?? ''));

        $variant = $variantId ? $product->variants()->find($variantId) : null;

        $price = $variant ? $variant->final_price : $product->final_price;
        $stock = $variant ? $variant->stock : $product->stock;
        $weight = $variant ? ($variant->weight ?? 0) : ($product->weight ?? 0);
        $label = $variant && $variant->specifications->isNotEmpty()
            ? $variant->specifications
                ->sortBy(fn ($s) => $s->parent_id ?? 0)
                ->map(fn ($s) => $s->trans('title'))
                ->implode(' / ')
            : null;

        if ($cart->has($rowId)) {
            $item = $cart[$rowId];
            $item['qty'] += $qty;
            $cart[$rowId] = $item;
        } else {
            $cart[$rowId] = [
                'rowId' => $rowId,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'variant_label' => $label,
                'name' => $product->trans('name') ?? $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'qty' => $qty,
                'stock' => $stock,
                'weight' => $weight,
                'color' => $color,
                'size' => $size,
                'image' => $product->images->first()->url ?? null,
            ];
        }

        session([$this->sessionKey => $cart->toArray()]);

        return $rowId;
    }

    public function update(string $rowId, int $qty): void
    {
        $cart = $this->content();

        if ($cart->has($rowId)) {
            $item = $cart[$rowId];
            $item['qty'] = max(1, $qty);
            $cart->put($rowId, $item);
            session([$this->sessionKey => $cart->toArray()]);
        }
    }

    public function remove(string $rowId): void
    {
        $cart = $this->content();
        $cart->forget($rowId);
        session([$this->sessionKey => $cart->toArray()]);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    public function subtotal(): float
    {
        return (float) $this->content()->sum(fn ($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
    }

    public function discount(): float
    {
        $coupon = session('coupon');

        if (! $coupon) {
            return 0;
        }

        return $coupon['type'] === 'percent'
            ? $this->subtotal() * ($coupon['value'] / 100)
            : min($coupon['value'], $this->subtotal());
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - $this->discount());
    }

    public function count(): int
    {
        return (int) $this->content()->sum('qty');
    }

    /**
     * محاسبه وزن کل سبد (گرم)
     */
    public function totalWeight(): float
    {
        return $this->content()->sum(fn ($item) => ($item['weight'] ?? 0) * $item['qty']);
    }

    /**
     * گرد کردن وزن به بالا (هر ۱۰۰ گرم)
     */
    public function roundedWeight(): float
    {
        $weight = $this->totalWeight();
        return ceil($weight / 100) * 100;
    }

    public function applyCoupon(string $code): bool
    {
        // TODO: از جدول discounts در دیتابیس خوانده شود
        $coupons = [
            'WELCOME10' => ['type' => 'percent', 'value' => 10],
            'SAVE50000' => ['type' => 'fixed', 'value' => 50000],
        ];

        if (isset($coupons[strtoupper($code)])) {
            session(['coupon' => array_merge($coupons[strtoupper($code)], ['code' => strtoupper($code)])]);
            return true;
        }

        return false;
    }

    /**
     * دریافت لیست آیتم‌های سبد به‌صورت آرایه برای ایجاد سفارش
     */
    public function toArray(): array
    {
        return $this->content()->values()->toArray();
    }
}
