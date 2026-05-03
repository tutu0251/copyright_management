<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastLoginAtToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }
        if ($this->db->fieldExists('last_login_at', 'users')) {
            return;
        }
        $this->forge->addColumn('users', [
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    public function down(): void
    {
        if ($this->db->fieldExists('last_login_at', 'users')) {
            $this->forge->dropColumn('users', 'last_login_at');
        }
    }
}
