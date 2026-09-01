<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            [
                'title' => 'تخفیف خرید اول',
                'code' => 'WELCOME15',
                'amount' => 15,
                'type' => 'percent',
                'count' => 100,
                'basket_minimum_price' => 200000,
                'first_purchase' => 1,
                'with_discount' => 0,
                'max_usage_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(7),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'کد تخفیف ۵۰ هزاری',
                'code' => 'SAVE50K',
                'amount' => 50000,
                'type' => 'cash',
                'count' => 50,
                'basket_minimum_price' => 300000,
                'first_purchase' => 0,
                'with_discount' => 1,
                'max_usage_per_user' => 2,
                'is_active' => true,
                'starts_at' => now()->subDays(3),
                'expires_at' => now()->addDays(60),
            ],
            [
                'title' => 'تخفیف ویژه دسته‌بندی',
                'code' => 'CAT10',
                'amount' => 10,
                'type' => 'percent',
                'count' => 200,
                'basket_minimum_price' => 0,
                'first_purchase' => 0,
                'with_discount' => 0,
                'pay_type' => 'category',
                'max_usage_per_user' => 5,
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'expires_at' => now()->addDays(90),
            ],
            [
                'title' => 'تخفیف غیرفعال',
                'code' => 'INACTIVE10',
                'amount' => 10,
                'type' => 'percent',
                'count' => 10,
                'basket_minimum_price' => 0,
                'first_purchase' => 0,
                'with_discount' => 0,
                'max_usage_per_user' => 1,
                'is_active' => false,
                'starts_at' => null,
                'expires_at' => null,
            ],
            [
                'title' => 'تخفیف منقضی شده',
                'code' => 'EXPIRED20',
                'amount' => 20,
                'type' => 'percent',
                'count' => 100,
                'basket_minimum_price' => 100000,
                'first_purchase' => 0,
                'with_discount' => 0,
                'max_usage_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subDays(30),
                'expires_at' => now()->subDays(1),
            ],
            [
                'title' => 'تخفیف مخصوص کاربر',
                'code' => 'VIP50',
                'amount' => 50000,
                'type' => 'cash',
                'count' => 5,
                'basket_minimum_price' => 500000,
                'first_purchase' => 0,
                'with_discount' => 0,
                'user_id' => null, // در سیدر بعد از کاربران ساخته می‌شود
                'max_usage_per_user' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'تخفیف بدون تاریخ انقضا',
                'code' => 'OPENEND',
                'amount' => 5,
                'type' => 'percent',
                'count' => 1000,
                'basket_minimum_price' => 0,
                'first_purchase' => 0,
                'with_discount' => 1,
                'max_usage_per_user' => 10,
                'is_active' => true,
                'starts_at' => null,
                'expires_at' => null,
            ],
            [
                'title' => 'تخفیف بدون حداقل خرید',
                'code' => 'NOMIN',
                'amount' => 15000,
                'type' => 'cash',
                'count' => 30,
                'basket_minimum_price' => 0,
                'first_purchase' => 0,
                'with_discount' => 0,
                'max_usage_per_user' => 3,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(14),
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(['code' => $discount['code']], $discount);
        }

        // اتصال اولین دسته‌بندی به کد CAT10
        $catDiscount = Discount::where('code', 'CAT10')->first();
        $firstCategory = ProductCategory::first();
        if ($catDiscount && $firstCategory) {
            $catDiscount->discountable()->syncWithoutDetaching([$firstCategory->id]);
        }

        $this->command?->info('۸ کد تخفیف نمونه ساخته شد.');
    }
}
