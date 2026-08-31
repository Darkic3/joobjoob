<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['name' => 'آذربایجان شرقی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'آذربایجان غربی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اردبیل', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'اصفهان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'البرز', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ایلام', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بوشهر', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'چهارمحال و بختیاری', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خراسان جنوبی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خراسان رضوی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خراسان شمالی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خوزستان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'زنجان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سمنان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سیستان و بلوچستان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فارس', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 16, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قزوین', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 17, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قم', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 18, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کردستان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 19, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کرمان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کرمانشاه', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 21, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'کهگیلویه و بویراحمد', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 22, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'گلستان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 23, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'گیلان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'لرستان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مازندران', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 26, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مرکزی', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 27, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'هرمزگان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 28, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'همدان', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 29, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'یزد', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تهران', 'status' => 1, 'cod24_id' => null, 'chapar_id' => 31, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('states')->insert($states);
    }
}
