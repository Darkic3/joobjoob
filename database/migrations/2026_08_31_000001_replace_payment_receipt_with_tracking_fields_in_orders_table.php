<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_receipt');
            $table->string('receipt_tracking_code')->nullable()->after('freight_balance')->comment('کد پیگیری واریزی (کارت به کارت)');
            $table->string('receipt_payer_card')->nullable()->after('receipt_tracking_code')->comment('شماره کارت واریزکننده (کارت به کارت)');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['receipt_tracking_code', 'receipt_payer_card']);
            $table->string('payment_receipt')->nullable()->after('freight_balance')->comment('عکس فیش واریزی (کارت به کارت)');
        });
    }
};
