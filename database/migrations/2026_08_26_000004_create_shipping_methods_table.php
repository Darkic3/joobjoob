<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('price')->nullable()->comment('قیمت به تومان');
            $table->double('price_ceiling')->default(0)->comment('سقف قیمت برای ارسال رایگان (0=غیرفعال)');
            $table->tinyInteger('status')->default(0);
            $table->text('description')->nullable();
            $table->tinyInteger('freight_balance')->default(0)->comment('1=پس کرایه');
            $table->string('type')->default('fixed_price')->comment('fixed_price|with_weight|chapar|cod24');
            $table->string('chapar_type')->nullable()->comment('نوع سرویس چاپار');
            $table->json('config')->nullable()->comment('تنظیمات سرویس ارسال (user, pass, ...)');
            // اطلاعات فرستنده
            $table->string('sender_name')->nullable();
            $table->string('sender_company')->nullable();
            $table->foreignId('sender_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('sender_phone')->nullable();
            $table->string('sender_mobile')->nullable();
            $table->string('sender_email')->nullable();
            $table->text('sender_address')->nullable();
            $table->string('sender_postal_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
