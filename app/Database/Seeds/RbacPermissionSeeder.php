<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Registers permission catalog, role-permission links, and optional manager role.
 * Safe to run multiple times (idempotent by slug / role slug).
 */
class RbacPermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->db->tableExists('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $definitions = [
            ['slug' => 'works.view', 'name' => 'View works', 'description' => 'Browse and open work records'],
            ['slug' => 'works.create', 'name' => 'Create works', 'description' => 'Register new works'],
            ['slug' => 'works.update', 'name' => 'Update works', 'description' => 'Edit work metadata and files'],
            ['slug' => 'works.delete', 'name' => 'Delete works', 'description' => 'Remove work records'],

            ['slug' => 'owners.view', 'name' => 'View owners', 'description' => 'Browse owner directory'],
            ['slug' => 'owners.create', 'name' => 'Create owners', 'description' => 'Add owner records'],
            ['slug' => 'owners.update', 'name' => 'Update owners', 'description' => 'Edit owners and work links'],
            ['slug' => 'owners.delete', 'name' => 'Delete owners', 'description' => 'Remove owner records'],

            ['slug' => 'licensees.view', 'name' => 'View licensees', 'description' => 'Browse licensee directory'],
            ['slug' => 'licensees.create', 'name' => 'Create licensees', 'description' => 'Add licensee records'],
            ['slug' => 'licensees.update', 'name' => 'Update licensees', 'description' => 'Edit licensee records'],
            ['slug' => 'licensees.delete', 'name' => 'Delete licensees', 'description' => 'Remove licensee records'],

            ['slug' => 'licenses.view', 'name' => 'View licenses', 'description' => 'Browse license agreements'],
            ['slug' => 'licenses.create', 'name' => 'Create licenses', 'description' => 'Record new licenses'],
            ['slug' => 'licenses.update', 'name' => 'Update licenses', 'description' => 'Edit license terms'],
            ['slug' => 'licenses.delete', 'name' => 'Delete licenses', 'description' => 'Remove license records'],

            ['slug' => 'usage_reports.view', 'name' => 'View usage reports', 'description' => 'Open monitoring reports'],
            ['slug' => 'usage_reports.create', 'name' => 'Create usage reports', 'description' => 'Log new usage findings'],
            ['slug' => 'usage_reports.update', 'name' => 'Update usage reports', 'description' => 'Edit reports and disposition'],
            ['slug' => 'usage_reports.delete', 'name' => 'Delete usage reports', 'description' => 'Remove usage reports'],

            ['slug' => 'cases.view', 'name' => 'View cases', 'description' => 'Browse infringement cases'],
            ['slug' => 'cases.create', 'name' => 'Create cases', 'description' => 'Open new cases'],
            ['slug' => 'cases.update', 'name' => 'Update cases', 'description' => 'Edit case details and evidence'],
            ['slug' => 'cases.delete', 'name' => 'Delete cases', 'description' => 'Remove case records'],
            ['slug' => 'cases.status_update', 'name' => 'Change case status', 'description' => 'Move cases through workflow'],

            ['slug' => 'dashboard.view', 'name' => 'View dashboard', 'description' => 'Access analytics dashboard'],
            ['slug' => 'reports.view', 'name' => 'View reports', 'description' => 'Access analytics reports and exports'],
            ['slug' => 'activities.view', 'name' => 'View activity log', 'description' => 'Read audit activity feed'],
            ['slug' => 'settings.manage', 'name' => 'Manage settings', 'description' => 'Configure roles and permissions'],
            ['slug' => 'users.manage', 'name' => 'Manage users', 'description' => 'Create and deactivate user accounts'],
        ];

        foreach ($definitions as $row) {
            $exists = $this->db->table('permissions')->where('slug', $row['slug'])->countAllResults();
            if ($exists > 0) {
                continue;
            }
            $this->db->table('permissions')->insert([
                'slug'        => $row['slug'],
                'name'        => $row['name'],
                'description' => $row['description'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $this->ensureRole('manager', 'Manager', 'Create and update catalog, licensing, and cases');
        $slugToId = [];
        foreach ($this->db->table('permissions')->select('id, slug')->get()->getResultArray() as $r) {
            $slugToId[(string) $r['slug']] = (int) $r['id'];
        }

        $allSlugs = array_keys($slugToId);

        $viewerSlugs = array_values(array_filter($allSlugs, static fn (string $s) => str_ends_with($s, '.view')
            || $s === 'dashboard.view'
            || $s === 'reports.view'
            || $s === 'activities.view'));

        $managerSlugs = array_merge(
            [
                'works.view', 'works.create', 'works.update',
                'owners.view', 'owners.create', 'owners.update',
                'licensees.view', 'licensees.create', 'licensees.update',
                'licenses.view', 'licenses.create', 'licenses.update',
                'usage_reports.view', 'usage_reports.create', 'usage_reports.update',
                'cases.view', 'cases.create', 'cases.update', 'cases.status_update',
                'dashboard.view', 'reports.view', 'activities.view',
            ],
        );

        $this->syncRolePermissions('admin', $allSlugs, $slugToId, $now);
        $this->syncRolePermissions('manager', $managerSlugs, $slugToId, $now);
        $this->syncRolePermissions('editor', $managerSlugs, $slugToId, $now);
        $this->syncRolePermissions('viewer', $viewerSlugs, $slugToId, $now);
    }

    private function ensureRole(string $slug, string $name, string $description): void
    {
        $exists = $this->db->table('roles')->where('slug', $slug)->countAllResults();
        if ($exists > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('roles')->insert([
            'slug'        => $slug,
            'name'        => $name,
            'description' => $description,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    /**
     * @param list<string>       $slugs
     * @param array<string, int> $slugToId
     */
    private function syncRolePermissions(string $roleSlug, array $slugs, array $slugToId, string $now): void
    {
        $roleId = (int) $this->db->table('roles')->select('id')->where('slug', $roleSlug)->get()->getRow('id');
        if ($roleId < 1) {
            return;
        }

        $ids = [];
        foreach ($slugs as $s) {
            if (isset($slugToId[$s])) {
                $ids[] = $slugToId[$s];
            }
        }
        $ids = array_values(array_unique($ids));

        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
        foreach ($ids as $pid) {
            $this->db->table('role_permissions')->insert([
                'role_id'        => $roleId,
                'permission_id'  => $pid,
                'assigned_at'    => $now,
            ]);
        }
    }
}
