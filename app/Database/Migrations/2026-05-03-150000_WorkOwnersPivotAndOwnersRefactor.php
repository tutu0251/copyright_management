<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Step 3: Global owners registry + work_owners pivot (replaces per-work owner rows).
 */
class WorkOwnersPivotAndOwnersRefactor extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            // Project targets MySQLi; skip safely on other drivers in tests.
            return;
        }

        $owners   = $this->db->prefixTable('owners');
        $tableBare = ltrim($owners, '`');
        $tableBare = rtrim($tableBare, '`');

        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'                => ['type' => 'INT', 'unsigned' => true],
            'owner_id'               => ['type' => 'INT', 'unsigned' => true],
            'ownership_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'ownership_role'       => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'copyright_owner'],
            'start_date'           => ['type' => 'DATE', 'null' => true],
            'end_date'             => ['type' => 'DATE', 'null' => true],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'active'],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['work_id', 'owner_id']);
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('work_owners');

        // Move legacy owner rows (tied to a single work) into the pivot before dropping work_id.
        $sql = "INSERT INTO `{$this->db->prefixTable('work_owners')}`
            (`work_id`, `owner_id`, `ownership_percentage`, `ownership_role`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`)
            SELECT `work_id`, `id`, 100.00, 'copyright_owner', NULL, NULL, 'active', `created_at`, `updated_at`
            FROM `{$owners}` WHERE `work_id` IS NOT NULL";
        $this->db->query($sql);

        $fkRow = $this->db->query(
            'SELECT kcu.CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE kcu
             INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
             WHERE kcu.TABLE_SCHEMA = DATABASE() AND kcu.TABLE_NAME = ? AND kcu.COLUMN_NAME = ? AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$tableBare, 'work_id'],
        )->getRowArray();

        if ($fkRow !== null && isset($fkRow['CONSTRAINT_NAME'])) {
            $this->db->query("ALTER TABLE `{$owners}` DROP FOREIGN KEY `{$fkRow['CONSTRAINT_NAME']}`");
        }

        if ($this->db->fieldExists('work_id', 'owners')) {
            $this->forge->dropColumn('owners', 'work_id');
        }

        if ($this->db->fieldExists('legal_name', 'owners')) {
            $this->db->query("ALTER TABLE `{$owners}` CHANGE `legal_name` `name` VARCHAR(255) NOT NULL");
        }

        if ($this->db->fieldExists('entity_type', 'owners')) {
            $this->db->query("ALTER TABLE `{$owners}` CHANGE `entity_type` `owner_type` VARCHAR(50) NOT NULL DEFAULT 'individual'");
        }

        $this->forge->addColumn('owners', [
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'email',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'phone',
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'address',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'country',
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        $owners = $this->db->prefixTable('owners');
        $wo     = $this->db->prefixTable('work_owners');

        if ($this->db->fieldExists('deleted_at', 'owners')) {
            $this->forge->dropColumn('owners', ['phone', 'address', 'country', 'notes', 'deleted_at']);
        }

        if ($this->db->fieldExists('name', 'owners')) {
            $this->db->query("ALTER TABLE `{$owners}` CHANGE `name` `legal_name` VARCHAR(255) NOT NULL");
        }

        if ($this->db->fieldExists('owner_type', 'owners')) {
            $this->db->query("ALTER TABLE `{$owners}` CHANGE `owner_type` `entity_type` VARCHAR(50) NOT NULL DEFAULT 'individual'");
        }

        $this->forge->addColumn('owners', [
            'work_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Restore first work link per owner from pivot (best-effort).
        $this->db->query(
            "UPDATE `{$owners}` o
             INNER JOIN (
                SELECT wo.owner_id, MIN(wo.work_id) AS wid
                FROM `{$wo}` wo WHERE wo.deleted_at IS NULL GROUP BY wo.owner_id
             ) x ON x.owner_id = o.id
             SET o.work_id = x.wid",
        );

        $this->forge->dropTable('work_owners', true);

        $this->db->query(
            "ALTER TABLE `{$owners}` ADD CONSTRAINT `{$owners}_work_id_foreign` FOREIGN KEY (`work_id`) REFERENCES `{$this->db->prefixTable('works')}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE",
        );
    }
}
