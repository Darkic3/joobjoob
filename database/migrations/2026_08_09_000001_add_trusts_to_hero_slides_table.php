<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            // آیتم‌های اعتماد (مشتریان راضی، تضمین اصالت کالا و…) — آرایه‌ای از
            // [{ icon, text: {fa, en} }] که در فرم ادمین به‌صورت پویا (۰ تا ۵) مدیریت می‌شود.
            $table->json('trusts')->nullable()->after('viewers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn('trusts');
        });
    }
};
