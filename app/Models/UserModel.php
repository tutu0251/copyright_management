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
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * Active user row plus joined roles (slug + display name), ordered by role id.
     *
     * @return array{user: array<string, mixed>, roles: list<array{slug: string, name: string}>}|null
     */
    public function findActiveWithRolesByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)->where('is_active', 1)->first();
        if ($user === null) {
            return null;
        }

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
}
