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
            ['id' => 'dashboard', 'label' => 'Dashboard', 'path' => 'dashboard', 'perm' => 'dashboard.view'],
            ['id' => 'assets', 'label' => 'Assets', 'path' => 'works', 'perm' => 'works.view'],
            ['id' => 'owners', 'label' => 'Owners', 'path' => 'owners', 'perm' => 'owners.view'],
            ['id' => 'licensees', 'label' => 'Licensees', 'path' => 'licensees', 'perm' => 'licensees.view'],
            ['id' => 'licenses', 'label' => 'Licenses', 'path' => 'licenses', 'perm' => 'licenses.view'],
            ['id' => 'usage_reports', 'label' => 'Usage reports', 'path' => 'usage-reports', 'perm' => 'usage_reports.view'],
            ['id' => 'cases', 'label' => 'Cases', 'path' => 'cases', 'perm' => 'cases.view'],
            ['id' => 'activities', 'label' => 'Activity', 'path' => 'activities', 'perm' => 'activities.view'],
            ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports', 'perm' => null],
            ['id' => 'settings_roles', 'label' => 'Roles & permissions', 'path' => 'settings/roles', 'perm' => 'settings.manage'],
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
