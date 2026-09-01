<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Bank;
use App\Models\Order;
use App\Models\OrderShippingStatus;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->take(3)->get();
        $products = Product::where('is_active', true)->take(4)->get();
        $zarinpal = Bank::where('bank_type', 'zarinPal')->first();
        $cardToCard = Bank::where('bank_type', 'card_to_card')->first();
        $shipping = ShippingMethod::first();
        $paidStatus = OrderShippingStatus::where('title', 'پرداخت شده')->first();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        $orders = [
            [
                'user_id' => $users[0]->id,
                'bank_id' => $zarinpal?->id,
                'shipping_method_id' => $shipping?->id,
                'shipping_status_id' => $paidStatus?->id,
                'order_status' => 'paid',
                'total_price' => 635000,
                'discount_price' => 0,
                'shipping_price' => 45000,
                'payment_price' => 680000,
                'receiptor_full_name' => 'علی احمدی',
                'created_at' => now()->subDays(5),
            ],
            [
                'user_id' => $users[1]->id,
                'bank_id' => $cardToCard?->id,
                'shipping_method_id' => $shipping?->id,
                'shipping_status_id' => $paidStatus?->id,
                'order_status' => 'pending_confirmation',
                'total_price' => 750000,
                'discount_price' => 50000,
                'shipping_price' => 35000,
                'payment_price' => 735000,
                'receiptor_full_name' => 'سارا کریمی',
                'receipt_tracking_code' => '1234567890',
                'receipt_payer_card' => '6037123456789012',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $users[2]->id,
                'bank_id' => $zarinpal?->id,
                'shipping_method_id' => $shipping?->id,
                'order_status' => 'paid',
                'total_price' => 149000,
                'discount_price' => 0,
                'shipping_price' => 45000,
                'payment_price' => 194000,
                'receiptor_full_name' => 'محمد رضایی',
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }

        $this->command?->info('۳ سفارش نمونه ساخته شد.');
    }
}
