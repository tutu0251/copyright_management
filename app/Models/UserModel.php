<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'password_hash',
        'display_name',
        'is_active',
        'last_login_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));

        return $this->where('email', $email)->first();
    }

    public function findActiveWithRolesByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)->where('is_active', 1)->first();
        if ($user === null) {
            return null;
        }

        return $this->packUserWithRoles($user);
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array{user: array<string, mixed>, roles: list<array{slug: string, name: string}>}
     */
    public function packUserWithRoles(array $user): array
    {
        $roles = $this->db->table('user_roles ur')
            ->select('r.slug, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $user['id'])
            ->orderBy('r.id', 'ASC')
            ->get()
            ->getResultArray();

        /** @var list<array{slug: string, name: string}> $roles */

        return ['user' => $user, 'roles' => $roles];
    }

    public function verifyPassword(string $plain, string $passwordHash): bool
    {
        return password_verify($plain, $passwordHash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listUsersWithRoles(int $limit = 500): array
    {
        $users = $this->orderBy('display_name', 'ASC')->limit($limit)->findAll();
        if ($users === []) {
            return [];
        }
        $ids = array_map(static fn (array $u): int => (int) ($u['id'] ?? 0), $users);
        $ids = array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
        $rolesByUser = [];
        if ($ids !== []) {
            $rows = $this->db->table('user_roles ur')
                ->select('ur.user_id, r.slug, r.name')
                ->join('roles r', 'r.id = ur.role_id')
                ->whereIn('ur.user_id', $ids)
                ->orderBy('r.id', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $uid = (int) ($r['user_id'] ?? 0);
                if ($uid < 1) {
                    continue;
                }
                $rolesByUser[$uid][] = [
                    'slug' => (string) ($r['slug'] ?? ''),
                    'name' => (string) ($r['name'] ?? ''),
                ];
            }
        }
        foreach ($users as &$u) {
            unset($u['password_hash']);
            $uid = (int) ($u['id'] ?? 0);
            $u['roles'] = $rolesByUser[$uid] ?? [];
        }

        return $users;
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array<string, mixed>
     */
    public function stripSensitiveFields(array $user): array
    {
        unset($user['password_hash']);

        return $user;
    }
}
