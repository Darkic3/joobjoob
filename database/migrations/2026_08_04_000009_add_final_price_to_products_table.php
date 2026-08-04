<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('final_price')->default(0)->after('discount_price');
            $table->index('final_price');
        });

        // backfill: final_price = discount_price اگر تخفیف دارد وگرنه price
        DB::table('products')->update([
            'final_price' => DB::raw('CASE WHEN discount_price > 0 THEN discount_price ELSE price END'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['final_price']);
            $table->dropColumn('final_price');
        });
    }
};
