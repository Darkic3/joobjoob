<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * سرویس علاقه‌مندی‌ها با پشتیبانی دوگانه:
 *   - مهمان: Session (آرایه product_id)
 *   - کاربر لاگین‌شده: دیتابیس (جدول wishlists)
 *
 * ادغام خودکار پس از لاگین از طریق mergeGuestWishlist().
 */
class WishlistService
{
    protected string $sessionKey = 'wishlist';

    // ── Helpers ──

    private function isAuth(): bool
    {
        return Auth::check();
    }

    private function userId(): ?int
    {
        return Auth::id();
    }

    // ── Public API ──

    /**
     * شناسه‌های محصولات در علاقه‌مندی‌ها
     *
     * @return list<int>
     */
    public function ids(): array
    {
        if ($this->isAuth()) {
            return Wishlist::where('user_id', $this->userId())
                ->pluck('product_id')
                ->map('intval')
                ->all();
        }

        return array_values(array_filter(array_map('intval', session($this->sessionKey, []))));
    }

    /**
     * محصولات علاقه‌مندی به صورت Collection
     */
    public function content(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $products = Product::with(['images', 'category', 'specificationValues'])
            ->whereIn('id', $ids)
            ->get();

        // حفظ ترتیب افزوده‌شدن
        return $products->sortBy(fn (Product $product) => array_search($product->id, $ids, true))->values();
    }

    /**
     * آیا محصول در علاقه‌مندی‌هاست؟
     */
    public function has(Product|int $product): bool
    {
        $id = $product instanceof Product ? $product->id : (int) $product;

        if ($this->isAuth()) {
            return Wishlist::where('user_id', $this->userId())
                ->where('product_id', $id)
                ->exists();
        }

        return in_array($id, $this->ids(), true);
    }

    /**
     * افزودن/حذف.toggle — وضعیت جدید را برمی‌گرداند (true = الان در علاقه‌مندی‌هاست)
     */
    public function toggle(Product $product): bool
    {
        if ($this->isAuth()) {
            return $this->toggleDb($product);
        }

        return $this->toggleSession($product);
    }

    /**
     * حذف محصول از علاقه‌مندی‌ها
     */
    public function remove(Product|int $product): void
    {
        $id = $product instanceof Product ? $product->id : (int) $product;

        if ($this->isAuth()) {
            Wishlist::where('user_id', $this->userId())
                ->where('product_id', $id)
                ->delete();

            return;
        }

        $ids = array_values(array_filter($this->ids(), fn (int $existing) => $existing !== $id));
        session([$this->sessionKey => $ids]);
    }

    /**
     * تعداد آیتم‌ها
     */
    public function count(): int
    {
        if ($this->isAuth()) {
            return Wishlist::where('user_id', $this->userId())->count();
        }

        return count($this->ids());
    }

    // ── Merge (for OTP login) ──

    /**
     * ادغام علاقه‌مندی مهمان (Session) با دیتابیس کاربر.
     *
     * فراخوانی: OtpController::verify — بعد از session()->regenerate()
     *
     * @param array $guestIds شناسه‌های محصولات مهمان (az session ghabl az login)
     */
    public function mergeGuestWishlist(array $guestIds): void
    {
        if (! $this->isAuth() || empty($guestIds)) {
            return;
        }

        $userId = $this->userId();
        $now = now();

        // upsert: اگر قبلاً وجود داشت نادیده بگیر، در غیر این صورت ایجاد کن
        $rows = array_map(fn (int $productId) => [
            'user_id'    => $userId,
            'product_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_filter(array_map('intval', $guestIds), fn (int $id) => $id > 0));

        if (! empty($rows)) {
            // insertOrIgnore برای جلوگیری از duplicate (unique constraint)
            DB::table('wishlists')->insertOrIgnore($rows);
        }

        // پاک کردن session مهمان
        session()->forget($this->sessionKey);
    }

    // ── Private: Session mode ──

    private function toggleSession(Product $product): bool
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

    // ── Private: DB mode ──

    private function toggleDb(Product $product): bool
    {
        $userId = $this->userId();
        $productId = $product->id;

        $exists = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            return false;
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return true;
    }
}
