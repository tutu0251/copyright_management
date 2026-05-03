<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLicensingTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'contact_email'  => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('licensees');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'     => ['type' => 'INT', 'unsigned' => true],
            'licensee_id' => ['type' => 'INT', 'unsigned' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'draft'],
            'starts_on'   => ['type' => 'DATE', 'null' => true],
            'ends_on'     => ['type' => 'DATE', 'null' => true],
            'territory'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('licensee_id', 'licensees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('licenses');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'license_id'     => ['type' => 'INT', 'unsigned' => true],
            'period_start'   => ['type' => 'DATE'],
            'period_end'     => ['type' => 'DATE'],
            'usage_units'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'revenue_amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'currency'       => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'reported_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('license_id', 'licenses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('usage_reports');
    }

    public function down(): void
    {
        $this->forge->dropTable('usage_reports', true);
        $this->forge->dropTable('licenses', true);
        $this->forge->dropTable('licensees', true);
    }
}
