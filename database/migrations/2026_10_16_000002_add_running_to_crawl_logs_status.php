<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `crawl_logs` MODIFY `status` ENUM('running','success','partial','failed') NOT NULL DEFAULT 'success'");
    }

    public function down(): void
    {
        // Running logs (if any) become failed before shrinking the enum
        DB::table('crawl_logs')->where('status', 'running')->update(['status' => 'failed']);
        DB::statement("ALTER TABLE `crawl_logs` MODIFY `status` ENUM('success','partial','failed') NOT NULL DEFAULT 'success'");
    }
};
