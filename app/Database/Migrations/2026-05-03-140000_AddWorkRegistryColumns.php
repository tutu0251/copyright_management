<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extends works + work_files for Asset Registry (Step 2).
 */
class AddWorkRegistryColumns extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('works', [
            'work_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Text',
                'after'      => 'slug',
            ],
            'creator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'work_type',
            ],
            'owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'creator',
            ],
            'risk_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Low',
                'after'      => 'copyright_status',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'risk_level',
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ]);

        $this->forge->addColumn('work_files', [
            'stored_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'original_filename',
            ],
            'sha256' => [
                'type'       => 'CHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'size_bytes',
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'sha256',
            ],
        ]);

        $this->db->query(
            'UPDATE `' . $this->db->prefixTable('work_files') . '` SET `stored_filename` = SUBSTRING_INDEX(`storage_path`, "/", -1) WHERE `stored_filename` IS NULL AND `storage_path` IS NOT NULL',
        );

        // MySQLi (project default): enforce referential integrity for uploader audit trail.
        if ($this->db->DBDriver === 'MySQLi') {
            $wf  = $this->db->prefixTable('work_files');
            $usr = $this->db->prefixTable('users');
            $this->db->query(
                "ALTER TABLE `{$wf}` ADD CONSTRAINT `{$wf}_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `{$usr}`(`id`) ON DELETE SET NULL ON UPDATE CASCADE",
            );
        }
    }

    public function down(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $wf = $this->db->prefixTable('work_files');
            $fk = $wf . '_uploaded_by_foreign';
            $this->db->query("ALTER TABLE `{$wf}` DROP FOREIGN KEY `{$fk}`");
        }

        $this->forge->dropColumn('work_files', ['stored_filename', 'sha256', 'uploaded_by']);
        $this->forge->dropColumn('works', ['work_type', 'creator', 'owner', 'risk_level', 'description', 'deleted_at']);
    }
}
