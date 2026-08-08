<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * لینک‌های دلخواه (custom/page) آدرس یا کلید صفحه را داخل linkable_id ذخیره می‌کنند
     * (مثل https://example.com یا contact) — ستون قبلاً unsignedBigInteger بود و
     * ذخیرهٔ رشته با خطای SQL شکست می‌خورد. حالا string است (شناسهٔ مدل‌ها هم رشته‌ای ذخیره می‌شود).
     */
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->string('linkable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            // فقط شناسه‌های عددیِ مدل‌ها قابل بازگشت‌اند — مقادیر رشته‌ای (URL/صفحه) دیتا را به هم می‌ریزند.
            $table->unsignedBigInteger('linkable_id')->nullable()->change();
        });
    }
};
