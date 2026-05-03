<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAuditLogsStep7 extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        if (! $db->tableExists('audit_logs')) {
            return;
        }

        $t = '`' . str_replace('`', '``', $db->DBPrefix . 'audit_logs') . '`';

        if ($db->fieldExists('action', 'audit_logs') && ! $db->fieldExists('action_type', 'audit_logs')) {
            if ($db->DBDriver === 'MySQLi') {
                $db->query("ALTER TABLE {$t} CHANGE `action` `action_type` VARCHAR(100) NOT NULL");
            }
        }

        if (! $db->fieldExists('old_values', 'audit_logs')) {
            $this->forge->addColumn('audit_logs', [
                'old_values' => ['type' => 'JSON', 'null' => true],
            ]);
        }

        if (! $db->fieldExists('new_values', 'audit_logs')) {
            $this->forge->addColumn('audit_logs', [
                'new_values' => ['type' => 'JSON', 'null' => true],
            ]);
        }
    }

    public function down(): void
    {
        $db = $this->db;

        if (! $db->tableExists('audit_logs')) {
            return;
        }

        if ($db->fieldExists('new_values', 'audit_logs')) {
            $this->forge->dropColumn('audit_logs', 'new_values');
        }

        if ($db->fieldExists('old_values', 'audit_logs')) {
            $this->forge->dropColumn('audit_logs', 'old_values');
        }

        $t = '`' . str_replace('`', '``', $db->DBPrefix . 'audit_logs') . '`';

        if ($db->fieldExists('action_type', 'audit_logs') && ! $db->fieldExists('action', 'audit_logs')) {
            if ($db->DBDriver === 'MySQLi') {
                $db->query("ALTER TABLE {$t} CHANGE `action_type` `action` VARCHAR(100) NOT NULL");
            }
        }
    }
}
