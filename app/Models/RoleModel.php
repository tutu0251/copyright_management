<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
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
    public function rolesForUser(int $userId): array
    {
        return $this->db->table('roles')
            ->select('roles.*')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', $userId)
            ->orderBy('roles.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
