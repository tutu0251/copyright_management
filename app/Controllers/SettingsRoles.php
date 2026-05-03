<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\AuditLogModel;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Services\AuditLogService;
use App\Services\PermissionService;

class SettingsRoles extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav'];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'     => 'Roles & permissions',
            'currentPage'   => 'settings_roles',
            'currentUser'   => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Access control',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $roles = model(RoleModel::class)->orderBy('id', 'ASC')->findAll();
        $perm  = model(PermissionModel::class);
        $rows  = [];
        foreach ($roles as $role) {
            $rid = (int) ($role['id'] ?? 0);
            $rows[] = [
                'role'              => $role,
                'permission_count'  => count($perm->permissionIdsForRole($rid)),
            ];
        }

        return $this->layout('settings/roles/index', [
            'pageTitle' => 'Roles & permissions',
            'roles'     => $rows,
        ]);
    }

    public function editPermissions(int $roleId): string
    {
        if ($roleId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $role = model(RoleModel::class)->find($roleId);
        if ($role === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $permModel = model(PermissionModel::class);
        $all       = $permModel->listAllOrdered();
        $selected  = $permModel->permissionIdsForRole($roleId);

        return $this->layout('settings/roles/permissions', [
            'pageTitle'        => 'Edit role permissions',
            'role'             => $role,
            'permissions'      => $all,
            'selectedIds'      => array_fill_keys($selected, true),
        ]);
    }

    public function updatePermissions(int $roleId)
    {
        if ($roleId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $role = model(RoleModel::class)->find($roleId);
        if ($role === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $permModel   = model(PermissionModel::class);
        $oldIds      = $permModel->permissionIdsForRole($roleId);
        $posted      = $this->request->getPost('permission_ids');
        $newIds      = [];
        if (is_array($posted)) {
            foreach ($posted as $v) {
                $newIds[] = (int) $v;
            }
        }
        $newIds      = array_values(array_unique(array_filter($newIds, static fn (int $id) => $id > 0)));
        $allPerms    = $permModel->findAll();
        $idToSlug    = [];
        foreach ($allPerms as $p) {
            $idToSlug[(int) $p['id']] = (string) ($p['slug'] ?? '');
        }

        $oldSet = array_fill_keys($oldIds, true);
        $newSet = array_fill_keys($newIds, true);

        $permModel->replaceRolePermissions($roleId, $newIds);
        PermissionService::clearCache();

        if (AuditLogModel::schemaReady()) {
            $audit = service('auditLog');
            $uid   = (int) (auth_user()['id'] ?? 0);
            foreach ($newIds as $pid) {
                if (! isset($oldSet[$pid])) {
                    $audit->log(
                        AuditLogService::ACTION_PERMISSION_ASSIGNED,
                        AuditLogService::ENTITY_ROLE,
                        $roleId,
                        null,
                        ['permission_id' => $pid, 'permission_slug' => $idToSlug[$pid] ?? ''],
                        $uid,
                        $this->request,
                    );
                }
            }
            foreach ($oldIds as $pid) {
                if (! isset($newSet[$pid])) {
                    $audit->log(
                        AuditLogService::ACTION_PERMISSION_REMOVED,
                        AuditLogService::ENTITY_ROLE,
                        $roleId,
                        ['permission_id' => $pid, 'permission_slug' => $idToSlug[$pid] ?? ''],
                        null,
                        $uid,
                        $this->request,
                    );
                }
            }
            $audit->log(
                AuditLogService::ACTION_ROLE_UPDATED,
                AuditLogService::ENTITY_ROLE,
                $roleId,
                ['permission_ids' => $oldIds],
                ['permission_ids' => $newIds],
                $uid,
                $this->request,
            );
        }

        return redirect()->to(site_url('settings/roles/' . $roleId . '/permissions'))
            ->with('message', 'Permissions for this role have been saved.');
    }
}
