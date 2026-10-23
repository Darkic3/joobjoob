<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $tehran = \App\Models\City::where('name', 'تهران')->first();

        $methods = [
            [
                'title' => 'پست پیشتاز',
                'price' => 45000,
                'price_ceiling' => 500000,
                'status' => 1,
                'description' => 'ارسال از طریق پست پیشتاز — تحویل ۲ تا ۴ روز کاری',
                'freight_balance' => 0,
                'type' => 'fixed_price',
                'sender_name' => 'فروشگاه جوب‌جوب',
                'sender_company' => 'جوب‌جوب',
                'sender_city_id' => $tehran?->id,
                'sender_phone' => '02112345678',
                'sender_mobile' => '09121234567',
                'sender_email' => 'shipping@joobjoob.test',
                'sender_address' => 'تهران، خیابان ولیعصر، پلاک ۱۲۳',
                'sender_postal_code' => '1234567890',
                'estimated_days_min' => 2, 'estimated_days_max' => 4,
                'show_estimated_days' => true,
            ],
            [
                'title' => 'تیپاکس',
                'price' => 35000,
                'price_ceiling' => 800000,
                'status' => 1,
                'description' => 'ارسال سریع با تیپاکس — تحویل ۱ تا ۲ روز کاری',
                'freight_balance' => 0,
                'type' => 'fixed_price',
                'sender_name' => 'فروشگاه جوب‌جوب',
                'sender_company' => 'جوب‌جوب',
                'sender_city_id' => $tehran?->id,
                'sender_mobile' => '09121234567',
                'estimated_days_min' => 1, 'estimated_days_max' => 2,
                'show_estimated_days' => true,
            ],
            [
                'title' => 'اسنپ باکس',
                'price' => 25000,
                'price_ceiling' => 0,
                'status' => 1,
                'description' => 'ارسال فوری در تهران با اسنپ باکس — همان روز',
                'freight_balance' => 0,
                'type' => 'fixed_price',
                'sender_city_id' => $tehran?->id,
                'sender_mobile' => '09121234567',
                'estimated_days_min' => 1, 'estimated_days_max' => 1,
                'show_estimated_days' => true,
            ],
            [
                'title' => 'پس کرایه (پست)',
                'price' => 0,
                'price_ceiling' => 0,
                'status' => 1,
                'description' => 'هزینه ارسال هنگام تحویل پرداخت می‌شود',
                'freight_balance' => 1,
                'type' => 'fixed_price',
                'sender_mobile' => '09121234567',
                'estimated_days_min' => 3, 'estimated_days_max' => 5,
                'show_estimated_days' => true,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::updateOrCreate(
                ['title' => $method['title']],
                $method
            );
        }

        $this->command?->info('۴ روش ارسال نمونه ساخته شد.');
    }
}
