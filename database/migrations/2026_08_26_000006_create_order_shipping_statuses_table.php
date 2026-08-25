<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_shipping_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('color')->nullable()->default('#6c757d');
            $table->tinyInteger('default')->default(0)->comment('آیا وضعیت پیش‌فرض است؟');
            $table->tinyInteger('for_send')->default(0)->comment('آیا آماده ارسال است؟');
            $table->tinyInteger('sending_sms')->default(1)->comment('آیا پیامک ارسال شود؟');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipping_statuses');
    }
};
