<?php

declare(strict_types=1);

if (! function_exists('user_can')) {
    function user_can(string $permissionSlug): bool
    {
        helper('auth');

        return service('permissions')->currentUserCan($permissionSlug);
    }
}

if (! function_exists('user_can_any')) {
    /**
     * @param list<string> $permissionSlugs
     */
    function user_can_any(array $permissionSlugs): bool
    {
        helper('auth');

        return service('permissions')->currentUserCanAny($permissionSlugs);
    }
}

if (! function_exists('user_can_all')) {
    /**
     * @param list<string> $permissionSlugs
     */
    function user_can_all(array $permissionSlugs): bool
    {
        helper('auth');

        return service('permissions')->currentUserCanAll($permissionSlugs);
    }
}
