<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * Pivot helpers for user_roles (composite key; use this instead of a generic Model).
 */
class UserRoleModel
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    /**
     * @param list<int> $roleIds
     */
    public function replaceRolesForUser(int $userId, array $roleIds): void
    {
        $this->db->table('user_roles')->where('user_id', $userId)->delete();
        $roleIds = array_values(array_unique(array_filter($roleIds, static fn (int $id): bool => $id > 0)));
        if ($roleIds === []) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($roleIds as $rid) {
            $batch[] = [
                'user_id'     => $userId,
                'role_id'     => $rid,
                'assigned_at' => $now,
            ];
        }
        $this->db->table('user_roles')->insertBatch($batch);
    }
}
