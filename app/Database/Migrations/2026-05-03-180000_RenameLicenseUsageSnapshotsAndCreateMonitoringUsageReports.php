<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Legacy `usage_reports` stored per-license period metrics. Step 5 repurposes the name
 * for work-level usage monitoring; license rows move to `license_usage_snapshots`.
 */
class RenameLicenseUsageSnapshotsAndCreateMonitoringUsageReports extends Migration
{
    public function up(): void
    {
        $db  = $this->db;
        $pre = $db->getPrefix();

        if ($db->tableExists('usage_reports') && $db->fieldExists('work_id', 'usage_reports')) {
            return;
        }

        if ($db->tableExists('usage_reports') && $db->fieldExists('license_id', 'usage_reports')) {
            if (! $db->tableExists('license_usage_snapshots')) {
                $from = $pre . 'usage_reports';
                $to   = $pre . 'license_usage_snapshots';
                $db->query("RENAME TABLE `{$from}` TO `{$to}`");
            } else {
                $this->forge->dropTable('usage_reports', true);
            }
        }

        if ($db->tableExists('usage_reports')) {
            return;
        }

        $this->forge->addField([
            'id'                      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'                 => ['type' => 'INT', 'unsigned' => true],
            'detected_source'         => ['type' => 'VARCHAR', 'constraint' => 512],
            'detected_type'           => ['type' => 'VARCHAR', 'constraint' => 64],
            'usage_type'              => ['type' => 'VARCHAR', 'constraint' => 32],
            'detection_method'        => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'manual'],
            'detected_at'             => ['type' => 'DATETIME', 'null' => true],
            'reported_by'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'notes'                   => ['type' => 'TEXT', 'null' => true],
            'evidence_path'           => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'evidence_mime_type'      => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'evidence_uploaded_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'evidence_uploaded_at'    => ['type' => 'DATETIME', 'null' => true],
            'infringement_case_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('work_id');
        $this->forge->addKey('usage_type');
        $this->forge->addKey('detected_at');
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reported_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('evidence_uploaded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('infringement_case_id', 'infringement_cases', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('usage_reports');
    }

    public function down(): void
    {
        $db  = $this->db;
        $pre = $db->getPrefix();

        if ($db->tableExists('usage_reports') && $db->fieldExists('work_id', 'usage_reports')) {
            $this->forge->dropTable('usage_reports', true);
        }

        if ($db->tableExists('license_usage_snapshots') && ! $db->tableExists('usage_reports')) {
            $from = $pre . 'license_usage_snapshots';
            $to   = $pre . 'usage_reports';
            $db->query("RENAME TABLE `{$from}` TO `{$to}`");
        }
    }
}
