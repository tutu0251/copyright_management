<?php

declare(strict_types=1);

if (! function_exists('current_user_id')) {
    /**
     * Active signed-in user id from session, or null.
     */
    function current_user_id(): ?int
    {
        $id = session()->get('auth_user_id');

        return $id !== null ? (int) $id : null;
    }
}

if (! function_exists('user_has_role')) {
    /**
     * True if the signed-in user has the given role slug.
     */
    function user_has_role(string $roleSlug): bool
    {
        $roles = session()->get('auth_role_slugs');

        if (! is_array($roles)) {
            return false;
        }

        return in_array($roleSlug, $roles, true);
    }
}

if (! function_exists('user_has_any_role')) {
    /**
     * True if the signed-in user has any of the given role slugs.
     *
     * @param list<string> $roleSlugs
     */
    function user_has_any_role(array $roleSlugs): bool
    {
        foreach ($roleSlugs as $slug) {
            if (user_has_role($slug)) {
                return true;
            }
        }

        return false;
    }
}
