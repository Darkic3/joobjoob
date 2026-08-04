<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // برند
            $table->foreignId('brand_id')->nullable()->after('product_category_id')
                ->constrained('brands')->nullOnDelete();

            // تایمر تخفیف (بازه‌ای) — الگوی گزارش
            $table->boolean('timer_active')->default(false)->after('deal_ends_at');
            $table->timestamp('start_timer')->nullable()->after('timer_active');
            $table->timestamp('end_timer')->nullable()->after('start_timer');

            // حذف ستون‌های قدیمی رنگ/سایز (به specification منتقل شدند)
            // و ستون brand رشته‌ای قدیمی (به جدول brands منتقل شد)
            $table->dropColumn(['colors', 'sizes', 'brand']);
        });

        // انتقال deal_ends_at قدیمی به end_timer
        DB::table('products')
            ->whereNotNull('deal_ends_at')
            ->update(['end_timer' => DB::raw('deal_ends_at'), 'timer_active' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'timer_active', 'start_timer', 'end_timer']);
            $table->json('colors')->nullable();
            $table->json('sizes')->nullable();
        });
    }
};
