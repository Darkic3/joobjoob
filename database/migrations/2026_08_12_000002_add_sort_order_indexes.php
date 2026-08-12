<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ایندکس روی sort_order برای مرتب‌سازی‌های مکرر (درگ‌اندروپ در ایندکس ادمین
     * و مرتب‌سازی نمایش در سایت) — این دو جدول ترتیب‌محور هستند و هر بار
     * orderBy('sort_order') اجرا می‌شود.
     */
    public function up(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->index('sort_order', 'promo_banners_sort_order_index');
        });

        Schema::table('trust_badges', function (Blueprint $table) {
            $table->index('sort_order', 'trust_badges_sort_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->dropIndex('promo_banners_sort_order_index');
        });

        Schema::table('trust_badges', function (Blueprint $table) {
            $table->dropIndex('trust_badges_sort_order_index');
        });
    }
};
