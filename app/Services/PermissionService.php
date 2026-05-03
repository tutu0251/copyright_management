<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PermissionModel;

class PermissionService
{
    /** @var array<int, list<string>> */
    private static array $slugCache = [];

    public static function schemaReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('permissions') && $db->tableExists('role_permissions');
    }

    /**
     * Clears in-request cache (e.g. after role permission changes in admin UI).
     */
    public static function clearCache(): void
    {
        self::$slugCache = [];
    }

    /**
     * Effective permission slugs for a user (union across all assigned roles).
     *
     * @return list<string>
     */
    public function permissionSlugsForUser(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }
        if (! self::schemaReady()) {
            return [];
        }
        if (isset(self::$slugCache[$userId])) {
            return self::$slugCache[$userId];
        }

        $slugs = model(PermissionModel::class)->distinctSlugsForUser($userId);
        self::$slugCache[$userId] = $slugs;

        return $slugs;
    }

    public function currentUserCan(string $slug): bool
    {
        if (! self::schemaReady()) {
            return auth_logged_in();
        }
        $user = auth_user();
        if ($user === null) {
            return false;
        }

        return in_array($slug, $this->permissionSlugsForUser((int) $user['id']), true);
    }

    /**
     * @param list<string> $slugs
     */
    public function currentUserCanAny(array $slugs): bool
    {
        foreach ($slugs as $s) {
            if ($this->currentUserCan((string) $s)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $slugs
     */
    public function currentUserCanAll(array $slugs): bool
    {
        foreach ($slugs as $s) {
            if (! $this->currentUserCan((string) $s)) {
                return false;
            }
        }

        return true;
    }
}
