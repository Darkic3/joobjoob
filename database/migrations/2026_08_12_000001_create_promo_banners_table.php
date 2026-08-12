<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * بنرهای میانی صفحهٔ اصلی — مدل مستقل (به‌جای تنظیمات) تا ادمین بتواند
     * از ۱ تا چند بنر با سبک و تون دلخواه بسازد.
     */
    public function up(): void
    {
        Schema::create('promo_banners', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->string('url')->nullable();
            $table->string('style')->default('gradient');
            $table->string('tone')->default('accent');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_banners');
    }
};
