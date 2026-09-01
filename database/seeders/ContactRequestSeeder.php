<?php

namespace Database\Seeders;

use App\Models\ContactRequest;
use Illuminate\Database\Seeder;

class ContactRequestSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            ['name' => 'علی محمدی', 'email' => 'ali@example.com', 'phone' => '09121234567', 'department' => 'فروش', 'message' => 'آیا امکان خرید عمده وجود دارد؟', 'created_at' => now()->subDays(2)],
            ['name' => 'سارا رضایی', 'email' => 'sara@example.com', 'phone' => '09351234567', 'department' => 'پشتیبانی', 'message' => 'سفارش من هنوز ارسال نشده لطفاً پیگیری کنید.', 'created_at' => now()->subDays(1)],
            ['name' => 'محمد حسینی', 'email' => 'mohsen@example.com', 'phone' => null, 'department' => 'همکاری', 'message' => 'مایل به همکاری در زمینه تأمین محصولات هستم.', 'created_at' => now()->subHours(5), 'read_at' => now()->subHours(3)],
        ];

        foreach ($contacts as $contact) {
            ContactRequest::create($contact);
        }

        $this->command?->info('۳ پیام تماس نمونه ساخته شد.');
    }
}
