<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComplianceTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'     => ['type' => 'INT', 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'open'],
            'severity'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'opened_at'   => ['type' => 'DATE', 'null' => true],
            'closed_at'   => ['type' => 'DATE', 'null' => true],
            'summary'     => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('infringement_cases');

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_type'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'meta'         => ['type' => 'JSON', 'null' => true],
            'ip_address'   => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'   => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('infringement_cases', true);
    }
}
