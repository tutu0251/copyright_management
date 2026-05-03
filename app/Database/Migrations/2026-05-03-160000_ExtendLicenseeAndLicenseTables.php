<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Aligns `licensees` and `licenses` with Step 4 fields (types, fees, statuses, soft deletes).
 */
class ExtendLicenseeAndLicenseTables extends Migration
{
    public function up(): void
    {
        $db   = $this->db;
        $pre  = $db->getPrefix();
        $lt   = $pre . 'licensees';
        $lic  = $pre . 'licenses';

        // --- licensees ---
        if ($db->fieldExists('contact_email', 'licensees') && ! $db->fieldExists('email', 'licensees')) {
            $db->query("ALTER TABLE `{$lt}` CHANGE `contact_email` `email` VARCHAR(191) NULL");
        }

        if (! $db->fieldExists('licensee_type', 'licensees')) {
            $this->forge->addColumn('licensees', [
                'licensee_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'individual', 'after' => 'name'],
            ]);
        }

        foreach ([
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'address'    => ['type' => 'TEXT', 'null' => true],
            'country'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ] as $field => $def) {
            if (! $db->fieldExists($field, 'licensees')) {
                $this->forge->addColumn('licensees', [$field => $def]);
            }
        }

        // --- licenses: renames ---
        if ($db->fieldExists('status', 'licenses') && ! $db->fieldExists('license_status', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `status` `license_status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
        }

        if ($db->fieldExists('starts_on', 'licenses') && ! $db->fieldExists('start_date', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `starts_on` `start_date` DATE NULL");
        }

        if ($db->fieldExists('ends_on', 'licenses') && ! $db->fieldExists('end_date', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `ends_on` `end_date` DATE NULL");
        }

        if (! $db->fieldExists('license_type', 'licenses')) {
            $this->forge->addColumn('licenses', [
                'license_type' => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'non_exclusive', 'after' => 'licensee_id'],
            ]);
        }

        foreach ([
            'fee_amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.0],
            'currency'        => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'],
            'payment_status'  => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'unpaid'],
            'terms'           => ['type' => 'TEXT', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ] as $field => $def) {
            if (! $db->fieldExists($field, 'licenses')) {
                $this->forge->addColumn('licenses', [$field => $def]);
            }
        }

        // Normalize legacy status casing if present
        if ($db->fieldExists('license_status', 'licenses')) {
            $db->query("UPDATE `{$lic}` SET `license_status` = LOWER(`license_status`)");
        }
    }

    public function down(): void
    {
        $db  = $this->db;
        $pre = $db->getPrefix();
        $lt  = $pre . 'licensees';
        $lic = $pre . 'licenses';

        if ($db->fieldExists('email', 'licensees') && ! $db->fieldExists('contact_email', 'licensees')) {
            $db->query("ALTER TABLE `{$lt}` CHANGE `email` `contact_email` VARCHAR(191) NULL");
        }

        foreach (['licensee_type', 'phone', 'address', 'country', 'deleted_at'] as $col) {
            if ($db->fieldExists($col, 'licensees')) {
                $this->forge->dropColumn('licensees', $col);
            }
        }

        foreach (['license_type', 'fee_amount', 'currency', 'payment_status', 'terms', 'deleted_at'] as $col) {
            if ($db->fieldExists($col, 'licenses')) {
                $this->forge->dropColumn('licenses', $col);
            }
        }

        if ($db->fieldExists('license_status', 'licenses') && ! $db->fieldExists('status', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `license_status` `status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
        }

        if ($db->fieldExists('start_date', 'licenses') && ! $db->fieldExists('starts_on', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `start_date` `starts_on` DATE NULL");
        }

        if ($db->fieldExists('end_date', 'licenses') && ! $db->fieldExists('ends_on', 'licenses')) {
            $db->query("ALTER TABLE `{$lic}` CHANGE `end_date` `ends_on` DATE NULL");
        }
    }
}
