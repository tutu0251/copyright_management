<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorksAndOwnersTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'              => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'copyright_status'  => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'draft'],
            'registered_at'     => ['type' => 'DATE', 'null' => true],
            'created_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('works');

        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'             => ['type' => 'INT', 'unsigned' => true],
            'original_filename'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'storage_path'        => ['type' => 'VARCHAR', 'constraint' => 512],
            'mime_type'           => ['type' => 'VARCHAR', 'constraint' => 127, 'null' => true],
            'size_bytes'          => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('work_files');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'work_id'      => ['type' => 'INT', 'unsigned' => true],
            'legal_name'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'entity_type'  => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'individual'],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('work_id', 'works', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('owners');
    }

    public function down(): void
    {
        $this->forge->dropTable('owners', true);
        $this->forge->dropTable('work_files', true);
        $this->forge->dropTable('works', true);
    }
}
