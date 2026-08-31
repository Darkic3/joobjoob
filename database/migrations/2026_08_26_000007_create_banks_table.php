<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->boolean('admin_test')->default(0)->comment('آیا فقط برای تست ادمین است؟');
            $table->string('bank_type')->nullable()->comment('zarinPal|sep|sadad|zibal|...');
            $table->json('config')->nullable()->comment('تنظیمات درگاه (MerchantId, TerminalId, ...)');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
