<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * اعتبارسنجی و اعمال کد تخفیف
     *
     * @return array{success: bool, message?: string, discount?: Discount, discount_amount?: float}
     */
    public function apply(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));

        // TOCTOU FIX: lockForUpdate + DB::transaction prevents two concurrent
        // requests from both passing hasReachedLimit() and both creating usage.
        return DB::transaction(function () use ($code, $subtotal) {
            // ۱) جستجوی کد تخفیف با قفل سطری (پیشگیری از Race Condition)
            $discount = Discount::where('code', $code)->lockForUpdate()->first();

            if (! $discount) {
                return ['success' => false, 'message' => 'کد تخفیف نامعتبر است.'];
            }

            // ۲) بررسی فعال بودن
            if (! $discount->isCurrentlyValid()) {
                return ['success' => false, 'message' => 'این کد تخفیف منقضی شده یا غیرفعال است.'];
            }

            // ۳) بررسی حداقل قیمت سبد
            if ($discount->basket_minimum_price > 0 && $subtotal < $discount->basket_minimum_price) {
                $min = number_format($discount->basket_minimum_price);

                return ['success' => false, 'message' => "حداقل خرید برای این کد {$min} تومان است."];
            }

            // ۴) بررسی فقط خرید اول
            $user = Auth::user();
            if ($discount->first_purchase && $user) {
                $hasOrders = $user->orders()->where('order_status', 'paid')->exists();
                if ($hasOrders) {
                    return ['success' => false, 'message' => 'این کد فقط برای خرید اول قابل استفاده است.'];
                }
            }

            // ۵) بررسی مخصوص یک کاربر خاص
            if ($discount->user_id && (! $user || $user->id !== $discount->user_id)) {
                return ['success' => false, 'message' => 'این کد تخفیف مخصوص شما نیست.'];
            }

            // ۶) بررسی سقف استفاده (کلی + کاربر) — زیر قفل $discount
            if ($discount->hasReachedLimit($user?->id)) {
                return ['success' => false, 'message' => 'ظرفیت استفاده از این کد تمام شده است.'];
            }

            // ۷) محاسبه مبلغ تخفیف
            $discountAmount = $discount->calculateDiscount($subtotal);

            if ($discountAmount <= 0) {
                return ['success' => false, 'message' => 'مبلغ تخفیف قابل محاسبه نیست.'];
            }

            // ۸) ذخیره در session
            session([
                'coupon' => [
                    'code' => $discount->code,
                    'discount_id' => $discount->id,
                    'type' => $discount->type,
                    'value' => (float) $discount->amount,
                    'discount_amount' => $discountAmount,
                    'title' => $discount->title,
                    'with_discount' => $discount->appliesToDiscountedItems(),
                    'pay_type' => $discount->pay_type,
                ],
            ]);

            $formatted = number_format($discountAmount);

            return [
                'success' => true,
                'message' => "کد تخفیف «{$discount->title}» با موفقیت اعمال شد. ({$formatted} تومان)",
                'discount' => $discount,
                'discount_amount' => $discountAmount,
            ];
        });
    }

    /**
     * حذف کد تخفیف از session
     */
    public function remove(): void
    {
        session()->forget('coupon');
    }

    /**
     * دریافت اطلاعات کد تخفیف فعلی از session
     */
    public function current(): ?array
    {
        return session('coupon');
    }

    /**
     * محاسبه مبلغ تخفیف فعلی سبد
     */
    public function calculate(float $subtotal): float
    {
        $coupon = $this->current();

        if (! $coupon) {
            return 0;
        }

        if ($coupon['type'] === 'percent') {
            return $subtotal * ($coupon['value'] / 100);
        }

        return min($coupon['value'], $subtotal);
    }

    /**
     * محاسبه تخفیف هوشمند بر اساس آیتم‌های سبد
     *
     * با در نظر گرفتن pay_type و with_discount
     */
    public function calculateForCart(CartService $cart): float
    {
        $coupon = $this->current();

        if (! $coupon) {
            return 0;
        }

        $discount = Discount::find($coupon['discount_id'] ?? null);
        $withDiscount = $discount?->appliesToDiscountedItems() ?? true;

        // اگر تخفیف pay_type ندارد و روی محصولات تخفیف‌دار هم اعمال می‌شود
        // → محاسبه ساده روی کل سبد
        if (empty($coupon['pay_type']) && $withDiscount) {
            return $this->calculate($cart->subtotal());
        }

        if (! $discount) {
            return $this->calculate($cart->subtotal());
        }

        $totalDiscount = 0.0;

        foreach ($cart->content() as $item) {
            $itemPrice = ($item['price'] ?? 0);
            $itemQty = ($item['qty'] ?? 1);
            $itemTotal = $itemPrice * $itemQty;

            // اگر محصول تخفیف‌دار است و coupon روی محصولات تخفیف‌دار اعمال نمی‌شود
            $originalPrice = $item['original_price'] ?? $itemPrice;
            $isDiscountedProduct = $originalPrice > $itemPrice && $originalPrice > 0;

            if ($isDiscountedProduct && ! $discount->appliesToDiscountedItems()) {
                continue;
            }

            // بررسی eligibility بر اساس pay_type
            if (! $discount->isEligibleCartItem((array) $item)) {
                continue;
            }

            $totalDiscount += $discount->calculateDiscount($itemTotal);
        }

        return min($totalDiscount, $cart->subtotal());
    }

    /**
     * ثبت استفاده از کد تخفیف (بعد از ایجاد سفارش موفق)
     */
    public function recordUsage(int $discountId, ?int $userId, ?int $orderId, float $discountAmount): void
    {
        DB::transaction(function () use ($discountId, $userId, $orderId, $discountAmount) {
            DiscountUsage::create([
                'discount_id' => $discountId,
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);
        });
    }
}
