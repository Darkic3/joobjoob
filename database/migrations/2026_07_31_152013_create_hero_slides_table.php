<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();

            // ۱. نوع بنر (تعیین‌کننده رفتار و فیلدهای نمایشی)
            $table->string('hero_type')->default('internal'); // informational, external, internal

            // ۲. فیلدهای چندزبانه (JSON)
            $table->json('badge')->nullable();
            $table->json('title');
            $table->json('desc')->nullable();
            $table->json('btn_text')->nullable();

            // ۳. قیمت، ارز و اطلاعات بازاریابی
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('old_price')->nullable(); // برای نمایش خط‌خوردگی و درصد تخفیف
            $table->string('currency', 10)->default('IRT'); // IRT = تومان
            $table->string('rating')->nullable(); // مثلاً "4.8"
            $table->unsignedInteger('viewers')->default(0); // تعداد بازدیدکننده زنده

            // ۴. روابط چندشکلی (Polymorphic) برای لینک به محصول، دسته‌بندی یا مقاله
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->index(['linkable_type', 'linkable_id']); // ایندکس ترکیبی برای سرعت کوئری

            // ۵. تصویر و تنظیمات نمایش
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
