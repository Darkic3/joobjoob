<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * هیرو مینیمال: ستون‌های نمایشی اسلایدر (قیمت/امتیاز/بازدیدکننده/اعتماد) حذف شدند —
     * سایت تنها badge/title/desc/btn_text/لینک/تصویر را نمایش می‌دهد.
     */
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn(['price', 'old_price', 'currency', 'rating', 'viewers', 'trusts']);
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->after('btn_text');
            $table->unsignedBigInteger('old_price')->nullable()->after('price');
            $table->string('currency', 10)->default('IRT')->after('old_price');
            $table->string('rating')->nullable()->after('currency');
            $table->unsignedInteger('viewers')->default(0)->after('rating');
            $table->json('trusts')->nullable()->after('viewers');
        });
    }
};
