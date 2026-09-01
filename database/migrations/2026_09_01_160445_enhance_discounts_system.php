<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add code, is_active, starts_at, expires_at to discounts
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('code')->unique()->after('title');
            $table->boolean('is_active')->default(true)->after('pay_type');
            $table->timestamp('starts_at')->nullable()->after('is_active');
            $table->timestamp('expires_at')->nullable()->after('starts_at');
        });

        // 2. Create discount_usage tracking table
        Schema::create('discount_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 0)->default(0)->comment('مبلغ واقعی تخفیف اعمال شده');
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['discount_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_usage');

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active', 'starts_at', 'expires_at']);
        });
    }
};
