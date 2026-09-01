<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = config('permissions.permissions', []);
        $now = now();

        $rows = [];
        foreach ($groups as $groupName => $group) {
            foreach ($group['access'] as $action => $labelKey) {
                $rows[] = [
                    'name' => "admin.{$groupName}.{$action}",
                    'group' => $groupName,
                    'label_key' => $labelKey,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (empty($rows)) {
            return;
        }

        // INSERT IGNORE to gracefully skip duplicates
        foreach (array_chunk($rows, 50) as $chunk) {
            $bindings = [];
            $valueSets = [];
            foreach ($chunk as $row) {
                $valueSets[] = '(?, ?, ?, ?, ?)';
                $bindings[] = $row['name'];
                $bindings[] = $row['group'];
                $bindings[] = $row['label_key'];
                $bindings[] = $row['created_at'];
                $bindings[] = $row['updated_at'];
            }
            $sql = 'INSERT IGNORE INTO `permissions` (`name`, `group`, `label_key`, `created_at`, `updated_at`) VALUES '
                . implode(', ', $valueSets);
            DB::statement($sql, $bindings);
        }
    }

    public function down(): void
    {
        $newPerms = [
            'admin.shipping-methods.index', 'admin.shipping-methods.create', 'admin.shipping-methods.store',
            'admin.shipping-methods.edit', 'admin.shipping-methods.update', 'admin.shipping-methods.destroy',
            'admin.shipping-methods.toggle-status',
            'admin.banks.index', 'admin.banks.create', 'admin.banks.store',
            'admin.banks.edit', 'admin.banks.update', 'admin.banks.destroy', 'admin.banks.toggle-status',
            'admin.orders.change-shipping-status', 'admin.orders.change-order-status',
            'admin.orders.post-code', 'admin.orders.factor', 'admin.orders.bulk-factor',
            'admin.reviews.approval', 'admin.reviews.featured',
            'admin.audit-logs.order-history', 'admin.audit-logs.product-history',
        ];
        DB::table('permissions')->whereIn('name', $newPerms)->delete();
    }
};
