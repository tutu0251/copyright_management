<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Evolves legacy `infringement_cases` (Step 1 compliance stub) into Step 6 workflow,
 * and adds evidence, status history, and case notes tables.
 */
class Step6InfringementCaseManagement extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        if ($db->tableExists('infringement_cases')) {
            $this->upgradeInfringementCasesTable();
        }

        if (! $db->tableExists('infringement_case_evidence')) {
            $this->forge->addField([
                'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'infringement_case_id'   => ['type' => 'INT', 'unsigned' => true],
                'stored_path'            => ['type' => 'VARCHAR', 'constraint' => 512],
                'original_name'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'mime_type'              => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
                'uploaded_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'             => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('infringement_case_id');
            $this->forge->addForeignKey('infringement_case_id', 'infringement_cases', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('infringement_case_evidence');
        }

        if (! $db->tableExists('infringement_case_status_logs')) {
            $this->forge->addField([
                'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'infringement_case_id'   => ['type' => 'INT', 'unsigned' => true],
                'from_status'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'to_status'              => ['type' => 'VARCHAR', 'constraint' => 64],
                'transition_note'        => ['type' => 'TEXT', 'null' => true],
                'changed_by'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'             => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('infringement_case_id');
            $this->forge->addForeignKey('infringement_case_id', 'infringement_cases', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('changed_by', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('infringement_case_status_logs');
        }

        if (! $db->tableExists('infringement_case_notes')) {
            $this->forge->addField([
                'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'infringement_case_id'   => ['type' => 'INT', 'unsigned' => true],
                'user_id'                => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'body'                   => ['type' => 'TEXT'],
                'created_at'             => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('infringement_case_id');
            $this->forge->addForeignKey('infringement_case_id', 'infringement_cases', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('infringement_case_notes');
        }
    }

    private function upgradeInfringementCasesTable(): void
    {
        $db = $this->db;
        $pre = $db->getPrefix();
        $t   = $pre . 'infringement_cases';

        if ($db->fieldExists('case_title', 'infringement_cases')) {
            $this->ensureCaseForeignKeys();

            return;
        }

        // Legacy columns: title, status, severity, summary
        if ($db->fieldExists('status', 'infringement_cases')) {
            $db->query("UPDATE `{$t}` SET `status` = 'detected' WHERE `status` IN ('open','new','draft','') OR `status` IS NULL");
            $db->query("UPDATE `{$t}` SET `status` = 'resolved' WHERE `status` IN ('closed','done')");
            $db->query("UPDATE `{$t}` SET `status` = 'under_review' WHERE `status` NOT IN (
                'detected','under_review','notice_sent','negotiation','resolved','rejected'
            )");
        }

        if ($db->fieldExists('severity', 'infringement_cases')) {
            $db->query("UPDATE `{$t}` SET `severity` = LOWER(TRIM(`severity`))");
            $db->query("UPDATE `{$t}` SET `severity` = 'medium' WHERE `severity` IS NULL OR `severity` = ''");
            $db->query("UPDATE `{$t}` SET `severity` = 'critical' WHERE `severity` IN ('crit','critical')");
            $db->query("UPDATE `{$t}` SET `severity` = 'medium' WHERE `severity` NOT IN ('low','medium','high','critical')");
        }

        if ($db->fieldExists('title', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` CHANGE `title` `case_title` VARCHAR(255) NOT NULL");
        }

        if ($db->fieldExists('status', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` CHANGE `status` `case_status` VARCHAR(64) NOT NULL DEFAULT 'detected'");
        }

        if ($db->fieldExists('severity', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` CHANGE `severity` `priority` VARCHAR(32) NOT NULL DEFAULT 'medium'");
        }

        if ($db->fieldExists('summary', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` CHANGE `summary` `description` TEXT NULL");
        }

        if (! $db->fieldExists('usage_report_id', 'infringement_cases')) {
            $this->forge->addColumn('infringement_cases', [
                'usage_report_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'work_id'],
            ]);
        }

        if (! $db->fieldExists('assigned_to', 'infringement_cases')) {
            $this->forge->addColumn('infringement_cases', [
                'assigned_to' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ]);
        }

        if (! $db->fieldExists('resolution_notes', 'infringement_cases')) {
            $this->forge->addColumn('infringement_cases', [
                'resolution_notes' => ['type' => 'TEXT', 'null' => true],
            ]);
        }

        if ($db->fieldExists('opened_at', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` MODIFY `opened_at` DATETIME NULL");
        }

        if ($db->fieldExists('closed_at', 'infringement_cases')) {
            $db->query("ALTER TABLE `{$t}` MODIFY `closed_at` DATETIME NULL");
        }

        $this->addUniqueUsageReportIfMissing();
        $this->ensureCaseForeignKeys();
    }

    private function addUniqueUsageReportIfMissing(): void
    {
        $db = $this->db;
        if (! $db->fieldExists('usage_report_id', 'infringement_cases')) {
            return;
        }

        $t = $db->prefixTable('infringement_cases');
        $rows = $db->query("SHOW INDEX FROM `{$t}` WHERE Column_name = 'usage_report_id' AND Non_unique = 0")->getResultArray();
        if ($rows !== []) {
            return;
        }

        $db->query("ALTER TABLE `{$t}` ADD UNIQUE KEY `uq_ic_usage_report` (`usage_report_id`)");
    }

    private function ensureCaseForeignKeys(): void
    {
        $db = $this->db;

        if ($db->fieldExists('usage_report_id', 'infringement_cases')
            && $db->tableExists('usage_reports')
            && $db->fieldExists('work_id', 'usage_reports')) {
            $this->addForeignKeyRawIfMissing(
                'fk_ic_usage_report',
                'infringement_cases',
                'usage_report_id',
                'usage_reports',
                'id',
                'SET NULL',
                'CASCADE',
            );
        }

        if ($db->fieldExists('assigned_to', 'infringement_cases')) {
            $this->addForeignKeyRawIfMissing(
                'fk_ic_assigned_to',
                'infringement_cases',
                'assigned_to',
                'users',
                'id',
                'SET NULL',
                'CASCADE',
            );
        }
    }

    private function foreignKeyExists(string $constraintName): bool
    {
        $db  = $this->db;
        $row = $db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = ? AND CONSTRAINT_NAME = ?',
            ['FOREIGN KEY', $constraintName],
        )->getRowArray();

        return $row !== null;
    }

    private function addForeignKeyRawIfMissing(
        string $constraintName,
        string $table,
        string $column,
        string $foreignTable,
        string $foreignColumn,
        string $onDelete,
        string $onUpdate,
    ): void {
        if ($this->foreignKeyExists($constraintName)) {
            return;
        }

        $db    = $this->db;
        $t     = $db->prefixTable($table);
        $ft    = $db->prefixTable($foreignTable);
        $sql   = "ALTER TABLE `{$t}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `{$ft}` (`{$foreignColumn}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
        $this->db->query($sql);
    }

    public function down(): void
    {
        $this->forge->dropTable('infringement_case_notes', true);
        $this->forge->dropTable('infringement_case_status_logs', true);
        $this->forge->dropTable('infringement_case_evidence', true);

        // Intentionally do not revert column renames on infringement_cases (may contain Step 6 data).
    }
}
