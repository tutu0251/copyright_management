<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

/**
 * Default roles and an administrator account for local development.
 * Default password (change immediately): ChangeMe!123
 */
class CopyrightManagementSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->db;

        if ($db->table('roles')->countAllResults() > 0) {
            return;
        }

        $db->table('roles')->insertBatch([
            [
                'name'        => 'Administrator',
                'slug'        => 'admin',
                'description' => 'Full access',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Editor',
                'slug'        => 'editor',
                'description' => 'Create and edit records',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Viewer',
                'slug'        => 'viewer',
                'description' => 'Read-only access',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ]);

        $adminRow = $db->table('roles')->where('slug', 'admin')->get()->getRowArray();
        $adminRoleId = (int) ($adminRow['id'] ?? 0);
        $userModel   = new UserModel();
        $userModel->skipValidation(true);
        $userId = $userModel->insert([
            'email'         => 'admin@example.local',
            'password_hash' => UserModel::hashPassword('ChangeMe!123'),
            'name'          => 'System Administrator',
            'is_active'     => 1,
        ], true);

        if ($userId !== false) {
            $db->table('user_roles')->insert([
                'user_id' => (int) $userId,
                'role_id' => $adminRoleId,
            ]);
        }
    }
}
