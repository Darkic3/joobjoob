<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->string('card_number')->nullable()->after('bank_type')->comment('شماره کارت مقصد (کارت به کارت)');
            $table->string('card_holder_name')->nullable()->after('card_number')->comment('نام صاحب کارت');
            $table->text('card_description')->nullable()->after('card_holder_name')->comment('توضیحات کارت به کارت (آدرس، شماره تماس و...)');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['card_number', 'card_holder_name', 'card_description']);
        });
    }
};
