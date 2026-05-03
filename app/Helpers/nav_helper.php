<?php

declare(strict_types=1);

if (! function_exists('copyright_nav_items')) {
    /**
     * Primary app navigation for authenticated layouts, filtered by RBAC.
     *
     * @return list<array{id: string, label: string, path: string}>
     */
    function copyright_nav_items(): array
    {
        helper('permission');

        $definitions = [
            ['id' => 'dashboard', 'label' => lang('App.nav_dashboard'), 'path' => 'dashboard', 'perm' => 'dashboard.view'],
            ['id' => 'assets', 'label' => lang('App.nav_assets'), 'path' => 'works', 'perm' => 'works.view'],
            ['id' => 'owners', 'label' => lang('App.nav_owners'), 'path' => 'owners', 'perm' => 'owners.view'],
            ['id' => 'licensees', 'label' => lang('App.nav_licensees'), 'path' => 'licensees', 'perm' => 'licensees.view'],
            ['id' => 'licenses', 'label' => lang('App.nav_licenses'), 'path' => 'licenses', 'perm' => 'licenses.view'],
            ['id' => 'usage_reports', 'label' => lang('App.nav_usage_reports'), 'path' => 'usage-reports', 'perm' => 'usage_reports.view'],
            ['id' => 'cases', 'label' => lang('App.nav_cases'), 'path' => 'cases', 'perm' => 'cases.view'],
            ['id' => 'activities', 'label' => lang('App.nav_activities'), 'path' => 'activities', 'perm' => 'activities.view'],
            ['id' => 'reports', 'label' => lang('App.nav_reports'), 'path' => 'reports', 'perm' => 'reports.view'],
            ['id' => 'users', 'label' => lang('App.nav_users'), 'path' => 'users', 'perm' => 'users.manage'],
            ['id' => 'settings_roles', 'label' => lang('App.nav_settings_roles'), 'path' => 'settings/roles', 'perm' => 'settings.manage'],
        ];

        $out = [];
        foreach ($definitions as $row) {
            $perm = $row['perm'] ?? null;
            unset($row['perm']);
            if ($perm !== null && ! user_can($perm)) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }
}
