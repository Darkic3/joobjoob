<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crawl_logs', function (Blueprint $table) {
            $table->string('crawler', 32)->default('products')->after('id')->comment('نوع کرالر: products یا categories');
            $table->index(['crawler']);
        });
    }

    public function down(): void
    {
        Schema::table('crawl_logs', function (Blueprint $table) {
            $table->dropIndex(['crawler']);
            $table->dropColumn('crawler');
        });
    }
};
