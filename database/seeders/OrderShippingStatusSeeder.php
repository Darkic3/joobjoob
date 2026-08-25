<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderShippingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'title' => 'در حال پرداخت',
                'color' => '#ffc107',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'پرداخت شده',
                'color' => '#28a745',
                'default' => 1,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'در حال آماده‌سازی',
                'color' => '#17a2b8',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'آماده ارسال',
                'color' => '#007bff',
                'default' => 0,
                'for_send' => 1,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'ارسال شده',
                'color' => '#6610f2',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'تحویل شده',
                'color' => '#28a745',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'مرجوع شده',
                'color' => '#dc3545',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'لغو شده',
                'color' => '#6c757d',
                'default' => 0,
                'for_send' => 0,
                'sending_sms' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('order_shipping_statuses')->insert($statuses);
    }
}
