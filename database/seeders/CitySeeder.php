<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // شهرهای اصلی هر استان — لیست کامل در دیتابیس وارد شود
        $cities = [
            // تهران
            ['name' => 'تهران', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3101, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ری', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3102, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شمیرانات', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3103, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اسلامشهر', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3104, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قدس', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3105, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ورامین', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3106, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'پاکدشت', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3107, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'رباط کریم', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3108, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شریف‌آباد', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3109, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فیروزکوه', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3110, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'دماوند', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3111, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'پیشوا', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3112, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شهریار', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3113, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ملارد', 'status' => 1, 'state_id' => 31, 'cod24_id' => null, 'chapar_id' => 3114, 'created_at' => now(), 'updated_at' => now()],

            // اصفهان
            ['name' => 'اصفهان', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 401, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کاشان', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 402, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خمینی‌شهر', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 403, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نائین', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 404, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اردستان', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 405, 'created_at' => now(), 'updated_at' => now()],
            ['name' => ' شهرضا', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 406, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نجف‌آباد', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 407, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'گلپایگان', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 408, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فلاورجان', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 409, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سمیرم', 'status' => 1, 'state_id' => 4, 'cod24_id' => null, 'chapar_id' => 410, 'created_at' => now(), 'updated_at' => now()],

            // خراسان رضوی
            ['name' => 'مشهد', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1001, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نیشابور', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1002, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سبزوار', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1003, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تربت حیدریه', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1004, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کاشمر', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1005, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'چناران', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1006, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تربت جام', 'status' => 1, 'state_id' => 10, 'cod24_id' => null, 'chapar_id' => 1007, 'created_at' => now(), 'updated_at' => now()],

            // فارس
            ['name' => 'شیراز', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1601, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'جهرم', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1602, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کازرون', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1603, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مرودشت', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1604, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'لار', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1605, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فسا', 'status' => 1, 'state_id' => 16, 'cod24_id' => null, 'chapar_id' => 1606, 'created_at' => now(), 'updated_at' => now()],

            // خوزستان
            ['name' => 'اهواز', 'status' => 1, 'state_id' => 12, 'cod24_id' => null, 'chapar_id' => 1201, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'آبادان', 'status' => 1, 'state_id' => 12, 'cod24_id' => null, 'chapar_id' => 1202, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ماهشهر', 'status' => 1, 'state_id' => 12, 'cod24_id' => null, 'chapar_id' => 1203, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'دزفول', 'status' => 1, 'state_id' => 12, 'cod24_id' => null, 'chapar_id' => 1204, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بندر امام خمینی', 'status' => 1, 'state_id' => 12, 'cod24_id' => null, 'chapar_id' => 1205, 'created_at' => now(), 'updated_at' => now()],

            // آذربایجان شرقی
            ['name' => 'تبریز', 'status' => 1, 'state_id' => 1, 'cod24_id' => null, 'chapar_id' => 101, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مراغه', 'status' => 1, 'state_id' => 1, 'cod24_id' => null, 'chapar_id' => 102, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'میانه', 'status' => 1, 'state_id' => 1, 'cod24_id' => null, 'chapar_id' => 103, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اهر', 'status' => 1, 'state_id' => 1, 'cod24_id' => null, 'chapar_id' => 104, 'created_at' => now(), 'updated_at' => now()],

            // آذربایجان غربی
            ['name' => 'ارومیه', 'status' => 1, 'state_id' => 2, 'cod24_id' => null, 'chapar_id' => 201, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خوی', 'status' => 1, 'state_id' => 2, 'cod24_id' => null, 'chapar_id' => 202, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ماکو', 'status' => 1, 'state_id' => 2, 'cod24_id' => null, 'chapar_id' => 203, 'created_at' => now(), 'updated_at' => now()],

            // البرز
            ['name' => 'کرج', 'status' => 1, 'state_id' => 5, 'cod24_id' => null, 'chapar_id' => 501, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'هشتگرد', 'status' => 1, 'state_id' => 5, 'cod24_id' => null, 'chapar_id' => 502, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نظرآباد', 'status' => 1, 'state_id' => 5, 'cod24_id' => null, 'chapar_id' => 503, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فردیس', 'status' => 1, 'state_id' => 5, 'cod24_id' => null, 'chapar_id' => 504, 'created_at' => now(), 'updated_at' => now()],

            // گیلان
            ['name' => 'رشت', 'status' => 1, 'state_id' => 24, 'cod24_id' => null, 'chapar_id' => 2401, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بندرانزلی', 'status' => 1, 'state_id' => 24, 'cod24_id' => null, 'chapar_id' => 2402, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'لاهیجان', 'status' => 1, 'state_id' => 24, 'cod24_id' => null, 'chapar_id' => 2403, 'created_at' => now(), 'updated_at' => now()],

            // مازندران
            ['name' => 'ساری', 'status' => 1, 'state_id' => 26, 'cod24_id' => null, 'chapar_id' => 2601, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'آمل', 'status' => 1, 'state_id' => 26, 'cod24_id' => null, 'chapar_id' => 2602, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بابل', 'status' => 1, 'state_id' => 26, 'cod24_id' => null, 'chapar_id' => 2603, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'چالوس', 'status' => 1, 'state_id' => 26, 'cod24_id' => null, 'chapar_id' => 2604, 'created_at' => now(), 'updated_at' => now()],

            // کرمان
            ['name' => 'کرمان', 'status' => 1, 'state_id' => 20, 'cod24_id' => null, 'chapar_id' => 2001, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سیرجان', 'status' => 1, 'state_id' => 20, 'cod24_id' => null, 'chapar_id' => 2002, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'رفسنجان', 'status' => 1, 'state_id' => 20, 'cod24_id' => null, 'chapar_id' => 2003, 'created_at' => now(), 'updated_at' => now()],

            // سیستان و بلوچستان
            ['name' => 'زاهدان', 'status' => 1, 'state_id' => 15, 'cod24_id' => null, 'chapar_id' => 1501, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'چابهار', 'status' => 1, 'state_id' => 15, 'cod24_id' => null, 'chapar_id' => 1502, 'created_at' => now(), 'updated_at' => now()],

            // کرمانشاه
            ['name' => 'کرمانشاه', 'status' => 1, 'state_id' => 21, 'cod24_id' => null, 'chapar_id' => 2101, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اسلام‌آباد غرب', 'status' => 1, 'state_id' => 21, 'cod24_id' => null, 'chapar_id' => 2102, 'created_at' => now(), 'updated_at' => now()],

            // همدان
            ['name' => 'همدان', 'status' => 1, 'state_id' => 29, 'cod24_id' => null, 'chapar_id' => 2901, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ملایر', 'status' => 1, 'state_id' => 29, 'cod24_id' => null, 'chapar_id' => 2902, 'created_at' => now(), 'updated_at' => now()],

            // یزد
            ['name' => 'یزد', 'status' => 1, 'state_id' => 30, 'cod24_id' => null, 'chapar_id' => 3001, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اردکان', 'status' => 1, 'state_id' => 30, 'cod24_id' => null, 'chapar_id' => 3002, 'created_at' => now(), 'updated_at' => now()],

            // قم
            ['name' => 'قم', 'status' => 1, 'state_id' => 18, 'cod24_id' => null, 'chapar_id' => 1801, 'created_at' => now(), 'updated_at' => now()],

            // بوشهر
            ['name' => 'بوشهر', 'status' => 1, 'state_id' => 7, 'cod24_id' => null, 'chapar_id' => 701, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بندر کنگان', 'status' => 1, 'state_id' => 7, 'cod24_id' => null, 'chapar_id' => 702, 'created_at' => now(), 'updated_at' => now()],

            // اردبیل
            ['name' => 'اردبیل', 'status' => 1, 'state_id' => 3, 'cod24_id' => null, 'chapar_id' => 301, 'created_at' => now(), 'updated_at' => now()],

            // زنجان
            ['name' => 'زنجان', 'status' => 1, 'state_id' => 13, 'cod24_id' => null, 'chapar_id' => 1301, 'created_at' => now(), 'updated_at' => now()],

            // سمنان
            ['name' => 'سمنان', 'status' => 1, 'state_id' => 14, 'cod24_id' => null, 'chapar_id' => 1401, 'created_at' => now(), 'updated_at' => now()],

            // قزوین
            ['name' => 'قزوین', 'status' => 1, 'state_id' => 17, 'cod24_id' => null, 'chapar_id' => 1701, 'created_at' => now(), 'updated_at' => now()],

            // لرستان
            ['name' => 'خرم‌آباد', 'status' => 1, 'state_id' => 25, 'cod24_id' => null, 'chapar_id' => 2501, 'created_at' => now(), 'updated_at' => now()],

            // گلستان
            ['name' => 'گرگان', 'status' => 1, 'state_id' => 23, 'cod24_id' => null, 'chapar_id' => 2301, 'created_at' => now(), 'updated_at' => now()],

            // هرمزگان
            ['name' => 'بندرعباس', 'status' => 1, 'state_id' => 28, 'cod24_id' => null, 'chapar_id' => 2801, 'created_at' => now(), 'updated_at' => now()],

            // چهارمحال و بختیاری
            ['name' => 'شهرکرد', 'status' => 1, 'state_id' => 8, 'cod24_id' => null, 'chapar_id' => 801, 'created_at' => now(), 'updated_at' => now()],

            // کردستان
            ['name' => 'سنندج', 'status' => 1, 'state_id' => 19, 'cod24_id' => null, 'chapar_id' => 1901, 'created_at' => now(), 'updated_at' => now()],

            // ایلام
            ['name' => 'ایلام', 'status' => 1, 'state_id' => 6, 'cod24_id' => null, 'chapar_id' => 601, 'created_at' => now(), 'updated_at' => now()],

            // خراسان شمالی
            ['name' => 'بجنورد', 'status' => 1, 'state_id' => 11, 'cod24_id' => null, 'chapar_id' => 1101, 'created_at' => now(), 'updated_at' => now()],

            // خراسان جنوبی
            ['name' => 'بیرجند', 'status' => 1, 'state_id' => 9, 'cod24_id' => null, 'chapar_id' => 901, 'created_at' => now(), 'updated_at' => now()],

            // کهگیلویه و بویراحمد
            ['name' => 'یاسوج', 'status' => 1, 'state_id' => 22, 'cod24_id' => null, 'chapar_id' => 2201, 'created_at' => now(), 'updated_at' => now()],

            // مرکزی
            ['name' => 'اراک', 'status' => 1, 'state_id' => 27, 'cod24_id' => null, 'chapar_id' => 2701, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('cities')->insert($cities);
    }
}
