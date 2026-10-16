<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('products_found')->default(0)->comment('تعداد محصولات یافت شده در صفحه لیست');
            $table->unsignedInteger('products_imported')->default(0)->comment('تعداد محصولات با موفقیت import شده');
            $table->unsignedInteger('products_failed')->default(0)->comment('تعداد محصولات با خطا');
            $table->unsignedInteger('pages_crawled')->default(0)->comment('تعداد صفحات categories که کرال شد');
            $table->unsignedInteger('limit')->nullable()->comment('محدودیت تعداد محصول (در صورت تعیین)');
            $table->enum('status', ['success', 'partial', 'failed'])->default('success');
            $table->text('error_message')->nullable()->comment('خطا در صورت وجود');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable()->comment('مدت زمان ثانیه');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('ادمین triggering');
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_logs');
    }
};
