<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * سرویس محصولات اخیراً مشاهده‌شده مبتنی بر Session.
 * ساختار: آرایه‌ای از product_id (حداکثر ۲۰ آیتم، جدیدترین اول).
 */
class RecentlyViewedService
{
    protected string $sessionKey = 'recently_viewed';
    protected int $maxItems = 20;

    /**
     * شناسه‌های محصولات اخیراً مشاهده‌شده (جدیدترین اول)
     *
     * @return list<int>
     */
    public function ids(): array
    {
        return array_values(array_filter(array_map('intval', session($this->sessionKey, []))));
    }

    /**
     * محصولات اخیراً مشاهده‌شده به عنوان Collection
     */
    public function content(int $limit = null): Collection
    {
        $ids = $this->ids();
        $limit = $limit ?? $this->maxItems;
        $ids = array_slice($ids, 0, $limit);

        if ($ids === []) {
            return collect();
        }

        $products = Product::with(['images', 'category', 'brand', 'specificationValues'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();

        // حفظ ترتیب مشاهده (جدیدترین اول)
        return $products->sortBy(fn (Product $product) => array_search($product->id, $ids, true))->values();
    }

    /**
     * افزودن محصول به لیست اخیراً مشاهده‌شده
     */
    public function add(Product|int $product): void
    {
        $id = $product instanceof Product ? $product->id : (int) $product;

        $ids = $this->ids();

        // اگر قبلاً وجود دارد، از جای قبلی حذف کن
        if (($key = array_search($id, $ids, true)) !== false) {
            unset($ids[$key]);
        }

        // در اول آرایه اضافه کن (جدیدترین)
        array_unshift($ids, $id);

        // محدود کردن تعداد
        $ids = array_slice(array_values($ids), 0, $this->maxItems);

        session([$this->sessionKey => $ids]);
    }

    /**
     * حذف محصول از لیست
     */
    public function remove(Product|int $product): void
    {
        $id = $product instanceof Product ? $product->id : (int) $product;
        $ids = array_values(array_filter($this->ids(), fn (int $existing) => $existing !== $id));
        session([$this->sessionKey => $ids]);
    }

    /**
     * پاک کردن کل لیست
     */
    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    /**
     * تعداد آیتم‌ها
     */
    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * چک کردن وجود محصول در لیست
     */
    public function has(Product|int $product): bool
    {
        $id = $product instanceof Product ? $product->id : (int) $product;
        return in_array($id, $this->ids(), true);
    }
}