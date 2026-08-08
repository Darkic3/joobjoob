<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. افزودن ستون جدید بولی is_active
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        // ۲. انتقال داده‌های موجود: فقط وضعیت published فعال محسوب می‌شود
        DB::table('products')->update([
            'is_active' => DB::raw("CASE WHEN status = 'published' THEN 1 ELSE 0 END"),
        ]);

        // ۳. حذف ایندکس و ستون قدیمی status
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_featured']);
            $table->dropColumn('status');
        });

        // ۴. ایندکس جدید برای فیلتر سریع
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('weight');
        });

        // بازگردانی داده‌ها
        DB::table('products')->update([
            'status' => DB::raw("CASE WHEN is_active = 1 THEN 'published' ELSE 'draft' END"),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->index(['status', 'is_featured']);
        });
    }
};
