<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * ایجاد کد تخفیف خوشامدگویی ۱۰٪ برای کاربران جدید.
 *
 * هر کاربری که از طریق OTP ثبت‌نام کند، یک کد تخفیف یکتا با مشخصات زیر دریافت می‌کند:
 *  - نوع: درصدی (۱۰٪)
 *  - حداکثر استفاده کل: ۱ بار
 *  - حداکثر استفاده هر کاربر: ۱ بار
 *  - فقط خرید اول
 *  - اعتبار: ۳۰ روز
 */
class WelcomeDiscountService
{
    private const DISCOUNT_PERCENT = 10;
    private const DISCOUNT_COUNT = 1;
    private const MAX_USAGE_PER_USER = 1;
    private const EXPIRY_DAYS = 30;
    private const CODE_PREFIX = 'WELCOME-';
    private const CODE_LENGTH = 6;

    /**
     * ایجاد کد تخفیف خوشامدگویی برای کاربر جدید.
     */
    public function createForUser(User $user): ?Discount
    {
        $code = $this->generateUniqueCode();

        $discount = Discount::create([
            'title'               => 'تخفیف خوشامدگویی',
            'code'                => $code,
            'type'                => 'percent',
            'amount'              => self::DISCOUNT_PERCENT,
            'count'               => self::DISCOUNT_COUNT,
            'max_usage_per_user'  => self::MAX_USAGE_PER_USER,
            'first_purchase'      => true,
            'with_discount'       => false,
            'is_active'           => true,
            'basket_minimum_price'=> 0,
            'user_id'             => $user->id,
            'expires_at'          => now()->addDays(self::EXPIRY_DAYS),
        ]);

        $discount->users()->attach($user->id);

        return $discount;
    }

    /**
     * تولید کد یکتا با پیشوند WELCOME-.
     */
    private function generateUniqueCode(): string
    {
        do {
            $suffix = strtoupper(Str::random(self::CODE_LENGTH));
            $code = self::CODE_PREFIX . $suffix;
        } while (Discount::where('code', $code)->exists());

        return $code;
    }
}
