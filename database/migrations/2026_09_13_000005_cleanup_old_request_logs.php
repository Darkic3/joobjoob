<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // حذف لاگ‌های درخواست قدیمی‌تر از ۳۰ روز
        DB::table('request_logs')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    public function down(): void
    {
        // غیرقابل بازگشت — داده‌های حذف‌شده قابل بازیابی نیستند
    }
};
