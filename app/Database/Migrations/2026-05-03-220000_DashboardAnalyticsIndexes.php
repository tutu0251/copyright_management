<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Supporting indexes for dashboard aggregations (grouped / range queries).
 */
class DashboardAnalyticsIndexes extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->addIndexIfMissing('works', 'cm_works_created_deleted', 'ADD INDEX cm_works_created_deleted (created_at, deleted_at)');
        $this->addIndexIfMissing('works', 'cm_works_type_deleted', 'ADD INDEX cm_works_type_deleted (work_type, deleted_at)');
        $this->addIndexIfMissing('licenses', 'cm_licenses_work_deleted_created', 'ADD INDEX cm_licenses_work_deleted_created (work_id, deleted_at, created_at)');
        $this->addIndexIfMissing('licenses', 'cm_licenses_licensee_deleted_created', 'ADD INDEX cm_licenses_licensee_deleted_created (licensee_id, deleted_at, created_at)');
        $this->addIndexIfMissing('usage_reports', 'cm_ur_work_deleted_detected', 'ADD INDEX cm_ur_work_deleted_detected (work_id, deleted_at, detected_at)');
        $this->addIndexIfMissing('infringement_cases', 'cm_ic_opened_status', 'ADD INDEX cm_ic_opened_status (opened_at, case_status)');
        $this->addIndexIfMissing('infringement_cases', 'cm_ic_closed_status', 'ADD INDEX cm_ic_closed_status (closed_at, case_status)');
        $this->addIndexIfMissing('audit_logs', 'cm_audit_created', 'ADD INDEX cm_audit_created (created_at)');
    }

    public function down(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $this->dropIndexIfExists('works', 'cm_works_created_deleted');
        $this->dropIndexIfExists('works', 'cm_works_type_deleted');
        $this->dropIndexIfExists('licenses', 'cm_licenses_work_deleted_created');
        $this->dropIndexIfExists('licenses', 'cm_licenses_licensee_deleted_created');
        $this->dropIndexIfExists('usage_reports', 'cm_ur_work_deleted_detected');
        $this->dropIndexIfExists('infringement_cases', 'cm_ic_opened_status');
        $this->dropIndexIfExists('infringement_cases', 'cm_ic_closed_status');
        $this->dropIndexIfExists('audit_logs', 'cm_audit_created');
    }

    private function fullTable(string $table): string
    {
        return $this->db->prefixTable($table);
    }

    private function addIndexIfMissing(string $table, string $indexName, string $indexClauseSql): void
    {
        $full = $this->fullTable($table);
        if ($this->indexExists($full, $indexName)) {
            return;
        }
        $this->db->query("ALTER TABLE `{$full}` {$indexClauseSql}");
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $full = $this->fullTable($table);
        if (! $this->indexExists($full, $indexName)) {
            return;
        }
        $this->db->query("ALTER TABLE `{$full}` DROP INDEX `{$indexName}`");
    }

    private function indexExists(string $prefixedTable, string $indexName): bool
    {
        $dbName = $this->db->getDatabase();
        $row    = $this->db->query(
            'SELECT 1 FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
            LIMIT 1',
            [$dbName, $prefixedTable, $indexName],
        )->getRowArray();

        return $row !== null;
    }
}
