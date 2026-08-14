<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * محل نمایش نشان اعتماد: سایت (نوار صفحهٔ اصلی)، فوتر یا هر دو.
     */
    public function up(): void
    {
        Schema::table('trust_badges', function (Blueprint $table) {
            $table->string('placement', 16)->default('site')->after('is_active');
            $table->index('placement');
        });
    }

    public function down(): void
    {
        Schema::table('trust_badges', function (Blueprint $table) {
            $table->dropIndex(['placement']);
            $table->dropColumn('placement');
        });
    }
};
