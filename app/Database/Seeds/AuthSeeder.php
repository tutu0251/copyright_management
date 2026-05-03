<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

/**
 * Seeds baseline roles and an administrator account for local development.
 *
 * Default credentials after seeding:
 * - Email: admin@example.com
 * - Password: Admin123!
 *
 * Change the password immediately in any shared or production environment.
 */
class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            ['slug' => 'admin', 'name' => 'Administrator', 'description' => 'Full system access'],
            ['slug' => 'editor', 'name' => 'Editor', 'description' => 'Manage works and licensing records'],
            ['slug' => 'viewer', 'name' => 'Viewer', 'description' => 'Read-only access'],
        ];

        foreach ($roles as $row) {
            $exists = $this->db->table('roles')->where('slug', $row['slug'])->countAllResults();
            if ($exists > 0) {
                continue;
            }
            $this->db->table('roles')->insert([
                'slug'        => $row['slug'],
                'name'        => $row['name'],
                'description' => $row['description'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $adminRoleId = (int) $this->db->table('roles')->select('id')->where('slug', 'admin')->get()->getRow('id');
        if ($adminRoleId === 0) {
            return;
        }

        $userRow = $this->db->table('users')->where('email', 'admin@example.com')->get()->getRowArray();
        if ($userRow !== null) {
            $userId = (int) $userRow['id'];
        } else {
            $userModel = new UserModel();
            $this->db->table('users')->insert([
                'email'          => 'admin@example.com',
                'password_hash'  => $userModel->hashPassword('Admin123!'),
                'display_name'   => 'System Admin',
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $userId = (int) $this->db->insertID();
        }

        if ($userId === 0) {
            return;
        }

        $hasAdminRole = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $adminRoleId)
            ->countAllResults();
        if ($hasAdminRole === 0) {
            $this->db->table('user_roles')->insert([
                'user_id'     => $userId,
                'role_id'     => $adminRoleId,
                'assigned_at' => $now,
            ]);
        }
    }
}
