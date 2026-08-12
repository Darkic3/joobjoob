<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * سرویس علاقه‌مندی‌ها مبتنی بر Session (بدون نیاز به لاگین).
 * ساختار هر آیتم: شناسهٔ محصول — به‌همین سادگی تا به‌روزرسانی‌ها سبک و سریع بمانند.
 */
class WishlistService
{
    protected string $sessionKey = 'wishlist';

    /**
     * شناسه‌های محصولات در علاقه‌مندی‌ها (به ترتیب افزوده‌شدن)
     *
     * @return list<int>
     */
    public function ids(): array
    {
        return array_values(array_filter(array_map('intval', session($this->sessionKey, []))));
    }

    public function content(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $products = Product::with(['images', 'category'])
            ->whereIn('id', $ids)
            ->get();

        // حفظ ترتیب افزوده‌شدن
        return $products->sortBy(fn (Product $product) => array_search($product->id, $ids, true))->values();
    }

    public function has(Product|int $product): bool
    {
        $id = $product instanceof Product ? $product->id : (int) $product;

        return in_array($id, $this->ids(), true);
    }

    /**
     * افزودن/حذف و برگرداندن وضعیت جدید (true = الان در علاقه‌مندی‌هاست)
     */
    public function toggle(Product $product): bool
    {
        $ids = $this->ids();

        if (in_array($product->id, $ids, true)) {
            $ids = array_values(array_filter($ids, fn (int $id) => $id !== $product->id));
            $in = false;
        } else {
            $ids[] = $product->id;
            $in = true;
        }

        session([$this->sessionKey => $ids]);

        return $in;
    }

    public function remove(Product|int $product): void
    {
        $id = $product instanceof Product ? $product->id : (int) $product;
        $ids = array_values(array_filter($this->ids(), fn (int $existing) => $existing !== $id));
        session([$this->sessionKey => $ids]);
    }

    public function count(): int
    {
        return count($this->ids());
    }
}
