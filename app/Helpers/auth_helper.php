<?php

declare(strict_types=1);

if (! function_exists('auth_user')) {
    /**
     * @return array{id: int, email: string, display_name: string}|null
     */
    function auth_user(): ?array
    {
        $session = session();
        $id      = $session->get('auth_user_id');
        if ($id === null || $id === '') {
            $id = $session->get('user_id');
        }
        if ($id === null || $id === '') {
            return null;
        }

        $email = $session->get('auth_email');
        if ($email === null || $email === '') {
            $email = $session->get('email');
        }
        $name = $session->get('auth_display_name');
        if ($name === null || $name === '') {
            $name = $session->get('name');
        }

        return [
            'id'           => (int) $id,
            'email'        => (string) $email,
            'display_name' => (string) $name,
        ];
    }
}

if (! function_exists('auth_logged_in')) {
    function auth_logged_in(): bool
    {
        return auth_user() !== null;
    }
}

if (! function_exists('auth_role_slugs')) {
    /**
     * @return list<string>
     */
    function auth_role_slugs(): array
    {
        $session = session();
        $raw     = $session->get('auth_role_slugs');
        if (! is_array($raw) || $raw === []) {
            $raw = $session->get('role_slugs');
        }
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($v) => (string) $v, $raw), static fn ($v) => $v !== ''));
    }
}

if (! function_exists('auth_has_role')) {
    function auth_has_role(string $slug): bool
    {
        return in_array($slug, auth_role_slugs(), true);
    }
}

if (! function_exists('auth_any_role')) {
    /**
     * @param list<string> $slugs
     */
    function auth_any_role(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if (auth_has_role((string) $slug)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('auth_primary_role_label')) {
    function auth_primary_role_label(): string
    {
        return (string) (session()->get('auth_primary_role_label') ?? 'Member');
    }
}
