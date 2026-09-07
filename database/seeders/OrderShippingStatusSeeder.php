<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderShippingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['title' => 'در حال پرداخت',   'color' => '#ffc107', 'default' => 0, 'for_send' => 0, 'sending_sms' => 0],
            ['title' => 'پرداخت شده',       'color' => '#28a745', 'default' => 1, 'for_send' => 0, 'sending_sms' => 1],
            ['title' => 'در حال آماده‌سازی', 'color' => '#17a2b8', 'default' => 0, 'for_send' => 0, 'sending_sms' => 1],
            ['title' => 'آماده ارسال',      'color' => '#007bff', 'default' => 0, 'for_send' => 1, 'sending_sms' => 1],
            ['title' => 'ارسال شده',        'color' => '#6610f2', 'default' => 0, 'for_send' => 0, 'sending_sms' => 1],
            ['title' => 'تحویل شده',        'color' => '#28a745', 'default' => 0, 'for_send' => 0, 'sending_sms' => 1],
            ['title' => 'مرجوع شده',        'color' => '#dc3545', 'default' => 0, 'for_send' => 0, 'sending_sms' => 1],
            ['title' => 'لغو شده',          'color' => '#6c757d', 'default' => 0, 'for_send' => 0, 'sending_sms' => 1],
        ];

        foreach ($statuses as $status) {
            DB::table('order_shipping_statuses')->updateOrCreate(
                ['title' => $status['title']],
                $status,
            );
        }
    }
}
