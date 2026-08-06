<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 10);
            $table->text('url');
            $table->string('ip', 45)->nullable();
            $table->integer('status_code')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index('created_at'); // برای پاک‌سازی دوره‌ای لاگ‌های قدیمی
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
