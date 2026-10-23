<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'فروشگاه جوب‌جوب', 'type' => 'text', 'group' => 'general', 'label' => 'نام سایت', 'sort' => 1],
            ['key' => 'site_description', 'value' => 'فروشگاه آنلاین پوشاک مردانه', 'type' => 'textarea', 'group' => 'general', 'label' => 'توضیحات سایت', 'sort' => 2],
            ['key' => 'site_phone', 'value' => '02112345678', 'type' => 'text', 'group' => 'contact', 'label' => 'تلفن', 'sort' => 3],
            ['key' => 'site_email', 'value' => 'info@joobjoob.test', 'type' => 'text', 'group' => 'contact', 'label' => 'ایمیل', 'sort' => 4],
            ['key' => 'site_address', 'value' => 'تهران، خیابان ولیعصر، پلاک ۱۲۳', 'type' => 'text', 'group' => 'contact', 'label' => 'آدرس', 'sort' => 5],
            ['key' => 'newsletter_placeholder', 'value' => 'ایمیل خود را وارد کنید', 'type' => 'text', 'group' => 'general', 'label' => 'متن جایگزین خبرنامه', 'sort' => 6],
            ['key' => 'free_shipping_above', 'value' => '500000', 'type' => 'text', 'group' => 'shipping', 'label' => 'ارسال رایگان بالای (تومان)', 'sort' => 7],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command?->info('۷ تنظیم نمونه ساخته شد.');
    }
}
