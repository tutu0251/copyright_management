<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreAuthTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('roles');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 191],
            'password_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'display_name'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        $this->forge->addField([
            'user_id'      => ['type' => 'INT', 'unsigned' => true],
            'role_id'      => ['type' => 'INT', 'unsigned' => true],
            'assigned_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['user_id', 'role_id'], true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_roles');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
    }
}
