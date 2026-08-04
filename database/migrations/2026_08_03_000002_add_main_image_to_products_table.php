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
            $table->string('main_image')->nullable()->after('slug');
        });

        // انتقال تصویر اصلی فعلی (is_primary) به ستون main_image
        // هر محصول فقط یک تصویر اصلی دارد؛ اگر چندتایی بود کوچک‌ترین sort_order برنده است
        DB::table('product_images')
            ->where('is_primary', true)
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->get()
            ->each(function ($image) {
                DB::table('products')
                    ->where('id', $image->product_id)
                    ->whereNull('main_image') // فقط اگر هنوز ست نشده باشد
                    ->update(['main_image' => $image->path]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('main_image');
        });
    }
};
