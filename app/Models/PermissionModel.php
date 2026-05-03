<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'name',
        'description',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * @return list<array<string, mixed>>
     */
    public function listAllOrdered(): array
    {
        return $this->orderBy('slug', 'ASC')->findAll();
    }

    /**
     * @return list<string>
     */
    public function distinctSlugsForUser(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        $rows = $this->db->table('permissions p')
            ->select('p.slug')
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->join('user_roles ur', 'ur.role_id = rp.role_id')
            ->where('ur.user_id', $userId)
            ->groupBy('p.slug')
            ->orderBy('p.slug', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $s = (string) ($r['slug'] ?? '');
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return $out;
    }

    /**
     * @param list<string> $slugs
     * @return array<string, int> slug => id
     */
    public function idsBySlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }
        $rows = $this->whereIn('slug', $slugs)->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[(string) $row['slug']] = (int) $row['id'];
        }

        return $map;
    }

    /**
     * @return list<int>
     */
    public function permissionIdsForRole(int $roleId): array
    {
        if ($roleId < 1) {
            return [];
        }

        $rows = $this->db->table('role_permissions')
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row['permission_id'] ?? 0);
        }

        return array_values(array_filter($ids, static fn (int $id) => $id > 0));
    }

    /**
     * @param list<int> $permissionIds
     */
    public function replaceRolePermissions(int $roleId, array $permissionIds): void
    {
        $db = $this->db;
        $db->transStart();
        $db->table('role_permissions')->where('role_id', $roleId)->delete();
        $now = date('Y-m-d H:i:s');
        foreach ($permissionIds as $pid) {
            $pid = (int) $pid;
            if ($pid < 1) {
                continue;
            }
            $db->table('role_permissions')->insert([
                'role_id'        => $roleId,
                'permission_id'  => $pid,
                'assigned_at'    => $now,
            ]);
        }
        $db->transComplete();
    }
}
