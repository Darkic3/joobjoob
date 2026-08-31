<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->unsignedSmallInteger('estimated_days_min')->default(1)->after('description')->comment('حداقل زمان ارسال (روز کاری)');
            $table->unsignedSmallInteger('estimated_days_max')->default(3)->after('estimated_days_min')->comment('حداکثر زمان ارسال (روز کاری)');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn(['estimated_days_min', 'estimated_days_max']);
        });
    }
};
