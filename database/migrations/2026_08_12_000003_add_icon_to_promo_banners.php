<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * آیکون هر بنر (کلید از مجموعهٔ آیکون‌های از پیش‌طراحی‌شده) — پیش‌فرض بلندگو.
     */
    public function up(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->string('icon')->default('megaphone')->after('tone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
