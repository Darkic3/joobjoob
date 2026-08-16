<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ستون‌های کد تایید یکبارمصرف (OTP) — کد و زمان انقضای آن.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('confirm_code', 10)->nullable()->after('password');
            $table->timestamp('confirm_code_expires_at')->nullable()->after('confirm_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['confirm_code', 'confirm_code_expires_at']);
        });
    }
};
