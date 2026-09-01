<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            [
                'title' => 'تخفیف خرید اول',
                'amount' => '15',
                'type' => 'percent',
                'count' => 100,
                'basket_minimum_price' => 200000,
                'first_purchase' => 1,
                'with_discount' => 0,
                'max_usage_per_user' => 1,
            ],
            [
                'title' => 'کد تخفیف ۵۰ هزاری',
                'amount' => '50000',
                'type' => 'cash',
                'count' => 50,
                'basket_minimum_price' => 300000,
                'first_purchase' => 0,
                'with_discount' => 1,
                'max_usage_per_user' => 2,
            ],
            [
                'title' => 'تخفیف ویژه اکسسوری',
                'amount' => '10',
                'type' => 'percent',
                'count' => 200,
                'basket_minimum_price' => 0,
                'first_purchase' => 0,
                'with_discount' => 0,
                'pay_type' => 'category',
                'max_usage_per_user' => 5,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(['title' => $discount['title']], $discount);
        }

        $this->command?->info('۳ کد تخفیف نمونه ساخته شد.');
    }
}
