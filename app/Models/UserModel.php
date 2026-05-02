<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'email',
        'password_hash',
        'name',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|max_length[191]',
        'name'  => 'required|max_length[191]',
    ];

    protected $validationMessages = [];

    /**
     * @return list<string>
     */
    public function getRoleSlugsForUser(int $userId): array
    {
        $rows = db_connect()->table('user_roles ur')
            ->select('r.slug')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->orderBy('r.slug', 'ASC')
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }

    public function findActiveByEmail(string $email): ?array
    {
        $row = $this->where('email', $email)->where('is_active', 1)->first();

        return $row === null ? null : $row;
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
