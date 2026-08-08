<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();          // 'fa' | 'en' | 'de' …
            $table->json('name');                          // نام ترجمه‌پذیر: ['fa'=>'فارسی','en'=>'Persian',…]
            $table->string('native_name');                 // نام به زبان خودش (fallback نمایش)
            $table->string('dir', 3)->default('ltr');      // 'rtl' | 'ltr'
            $table->boolean('is_active')->default(true);   // غیرفعال → از سوییچر حذف و در میدلور رد می‌شود
            $table->boolean('is_default')->default(false); // فقط یک زبان پیش‌فرض
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
