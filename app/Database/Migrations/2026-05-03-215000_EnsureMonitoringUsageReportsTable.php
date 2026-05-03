<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Repairs databases where Step 5 renamed legacy `usage_reports` but the new monitoring
 * table was never created (e.g. interrupted migration or failed CREATE after RENAME).
 */
class EnsureMonitoringUsageReportsTable extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        if ($db->tableExists('usage_reports') && $db->fieldExists('work_id', 'usage_reports')) {
            return;
        }

        if ($db->tableExists('usage_reports')) {
            $this->forge->dropTable('usage_reports', true);
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
        // No-op: do not drop `usage_reports` on rollback (would destroy monitoring data).
    }
}
