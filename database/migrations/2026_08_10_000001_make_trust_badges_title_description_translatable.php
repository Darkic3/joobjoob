<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تبدیل title/description به متن (برای ذخیرهٔ JSON چندزبانه) و بک‌فیل مقادیر فعلی.
     */
    public function up(): void
    {
        Schema::table('trust_badges', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('description')->nullable()->change();
        });

        // بک‌فیل: مقادیر فعلی (رشتهٔ ساده) → JSON — fa مقدار فعلی، en خالی (در فرانت fallback به fa)
        DB::table('trust_badges')->orderBy('id')->each(function ($badge) {
            DB::table('trust_badges')->where('id', $badge->id)->update([
                'title' => json_encode(['fa' => $badge->title, 'en' => ''], JSON_UNESCAPED_UNICODE),
                'description' => $badge->description
                    ? json_encode(['fa' => $badge->description, 'en' => ''], JSON_UNESCAPED_UNICODE)
                    : null,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // برگرداندن JSON به رشتهٔ ساده (فقط fa)
        DB::table('trust_badges')->orderBy('id')->each(function ($badge) {
            $title = json_decode((string) $badge->title, true);
            $description = json_decode((string) $badge->description, true);

            DB::table('trust_badges')->where('id', $badge->id)->update([
                'title' => is_array($title) ? ($title['fa'] ?? '') : $badge->title,
                'description' => is_array($description)
                    ? ($description['fa'] ?? null)
                    : ($badge->description ?: null),
            ]);
        });

        Schema::table('trust_badges', function (Blueprint $table) {
            $table->string('title')->change();
            $table->string('description')->nullable()->change();
        });
    }
};
