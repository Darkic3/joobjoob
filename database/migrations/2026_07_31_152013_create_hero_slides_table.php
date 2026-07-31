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

            // فیلدهای چندزبانه (JSON)
            $table->json('badge')->nullable();
            $table->json('title');
            $table->json('desc')->nullable();
            $table->json('btn_text')->nullable();

            // قیمت و ارز (عدد صحیح به جای JSON برای محاسبات دقیق)
            $table->unsignedBigInteger('price')->default(0);
            $table->string('currency', 10)->default('IRT'); // IRT = تومان

            // روابط چندشکلی (Polymorphic) برای لینک به محصول، دسته‌بندی یا مقاله
            // این دو فیلد جایگزین btn_link قدیمی می‌شوند
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->index(['linkable_type', 'linkable_id']); // ایندکس برای سرعت بالای کوئری

            // تصویر و تنظیمات نمایش
            $table->string('image')->nullable(); // nullable گذاشتم که بدون عکس هم بتوان اسلاید ساخت
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // در دیتابیس هست، ولی در فرم نمایش نمی‌دهیم

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
