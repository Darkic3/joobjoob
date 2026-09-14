<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);                          // otp, notification, welcome, bulk
            $table->string('template')->nullable();               // kavenegar template name
            $table->string('mobile', 15)->index();
            $table->string('status', 20);                        // sent, failed, skipped
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('related_id')->nullable(); // order_id, etc.
            $table->string('related_type')->nullable();
            $table->timestamps();

            // Composite indexes for dashboard queries
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
