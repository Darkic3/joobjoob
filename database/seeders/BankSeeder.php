<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        Bank::updateOrCreate(
            ['bank_type' => 'zarinPal'],
            [
                'title' => 'زرین‌پال',
                'status' => 1,
                'admin_test' => 1,
                'config' => ['merchant_id' => ''],
            ]
        );

        Bank::updateOrCreate(
            ['bank_type' => 'card_to_card'],
            [
                'title' => 'کارت به کارت',
                'status' => 1,
                'admin_test' => 0,
                'card_number' => '6037-9975-1234-5678',
                'card_holder_name' => 'نام صاحب حساب',
                'card_description' => 'بانک ملت - شعبه ولیعصر',
            ]
        );
    }
}
