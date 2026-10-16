<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('sku')->comment('لینک محصول در سایت مبدا (کرالر)');
            $table->unsignedBigInteger('source_price')->nullable()->after('source_url')->comment('قیمت محصول در سایت مبدا');
            $table->timestamp('last_crawled_at')->nullable()->after('source_price')->comment('آخرین بار کرال شدن');
            $table->boolean('is_crawled')->default(false)->after('last_crawled_at')->comment('آیا توسط کرالر import شده');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'source_price', 'last_crawled_at', 'is_crawled']);
        });
    }
};
