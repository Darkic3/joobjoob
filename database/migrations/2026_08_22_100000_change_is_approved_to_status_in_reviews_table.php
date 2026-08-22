<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // 0 = در انتظار (پیش‌فرض), 1 = تأیید شده, 2 = رد شده
            $table->tinyInteger('is_approved')->default(0)->comment('0=pending, 1=approved, 2=rejected')->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->change();
        });
    }
};
