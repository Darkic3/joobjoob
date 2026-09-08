<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Admin-only types — must stay in sync with NotificationService::ADMIN_TYPES */
    private const ADMIN_TYPES = [
        'order.new',
        'order.paid_admin',
        'order.card_to_card',
        'review.new',
        'contact.new',
    ];

    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('audience', 16)->default('user')->after('type');
            $table->index(['notifiable_id', 'audience', 'is_read'], 'notifications_owner_audience_read_idx');
        });

        // Backfill from type naming convention (single legacy source of truth)
        DB::table('notifications')
            ->whereIn('type', self::ADMIN_TYPES)
            ->update(['audience' => 'admin']);

        DB::table('notifications')
            ->whereNotIn('type', self::ADMIN_TYPES)
            ->where('audience', '!=', 'user')
            ->update(['audience' => 'user']);
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_owner_audience_read_idx');
            $table->dropColumn('audience');
        });
    }
};
