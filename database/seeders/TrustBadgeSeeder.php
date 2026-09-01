<?php

namespace Database\Seeders;

use App\Models\TrustBadge;
use Illuminate\Database\Seeder;

class TrustBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['icon' => '📦', 'title' => ['fa' => 'ارسال سریع', 'en' => 'Fast Shipping'], 'description' => ['fa' => 'تحویل ۱ تا ۳ روز کاری', 'en' => 'Delivery in 1-3 business days'], 'sort_order' => 1, 'is_active' => true],
            ['icon' => '🔒', 'title' => ['fa' => 'پرداخت امن', 'en' => 'Secure Payment'], 'description' => ['fa' => 'درگاه پرداخت معتبر بانکی', 'en' => 'Verified bank payment gateway'], 'sort_order' => 2, 'is_active' => true],
            ['icon' => '✅', 'title' => ['fa' => 'ضمانت اصالت', 'en' => 'Authenticity Guarantee'], 'description' => ['fa' => 'تضمین اصالت کالا', 'en' => 'Product authenticity guaranteed'], 'sort_order' => 3, 'is_active' => true],
            ['icon' => '🔄', 'title' => ['fa' => 'مرجوع آسان', 'en' => 'Easy Returns'], 'description' => ['fa' => 'مرجوع ظرف ۷ روز', 'en' => 'Return within 7 days'], 'sort_order' => 4, 'is_active' => true],
            ['icon' => '💬', 'title' => ['fa' => 'پشتیبانی ۲۴ ساعته', 'en' => '24/7 Support'], 'description' => ['fa' => 'پاسخگویی در تمام ساعات', 'en' => 'Available around the clock'], 'sort_order' => 5, 'is_active' => true],
        ];

        foreach ($badges as $badge) {
            TrustBadge::updateOrCreate(['title->fa' => $badge['title']['fa']], $badge);
        }

        $this->command?->info('۵ نشان اعتماد نمونه ساخته شد.');
    }
}
