<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add columns to discounts (only if they don't already exist)
        Schema::table('discounts', function (Blueprint $table) {
            if (! Schema::hasColumn('discounts', 'code')) {
                $table->string('code')->nullable()->after('title');
            }
            if (! Schema::hasColumn('discounts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('pay_type');
            }
            if (! Schema::hasColumn('discounts', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('discounts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('starts_at');
            }
        });

        // 1b. Generate unique codes for existing rows with empty/null code
        \DB::table('discounts')
            ->whereNull('code')
            ->orWhere('code', '')
            ->orderBy('id')
            ->each(function ($row) {
                \DB::table('discounts')->where('id', $row->id)->update([
                    'code' => 'DISC-' . str_pad($row->id, 4, '0', STR_PAD_LEFT),
                ]);
            });

        // 1c. Add unique constraint on code if not already present
        if (! Schema::hasIndex('discounts', 'discounts_code_unique')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->unique('code');
            });
        }

        // 2. Create discount_usage tracking table
        if (! Schema::hasTable('discount_usage')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_usage');

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active', 'starts_at', 'expires_at']);
        });
    }
};
