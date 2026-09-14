<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index();                  // error, warning, critical
            $table->string('exception_class')->nullable();          // RuntimeException, etc.
            $table->text('message');
            $table->text('file')->nullable();                       // فایل خطا
            $table->integer('line')->nullable();                    // خط خطا
            $table->json('trace')->nullable();                      // stack trace (first 2000 chars)
            $table->json('context')->nullable();                    // request data, user info
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('is_resolved')->default(false);
            $table->text('resolved_note')->nullable();
            $table->timestamps();

            // Composite indexes for dashboard queries
            $table->index(['level', 'created_at']);
            $table->index(['is_resolved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
