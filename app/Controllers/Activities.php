<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;

class Activities extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav', 'locale'];

    private const PER_PAGE = 50;

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'     => lang('App.nav_activities'),
            'currentPage'   => 'activities',
            'currentUser'   => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => lang('App.crumb_audit_log'),
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        if (! AuditLogModel::schemaReady()) {
            return $this->layout('activities/index', [
                'pageTitle'         => lang('App.nav_activities'),
                'migrationRequired' => true,
                'rows'               => [],
                'pager'              => ['page' => 1, 'perPage' => self::PER_PAGE, 'total' => 0, 'totalPages' => 1],
                'filterEntityType'   => '',
                'filterEntityId'     => '',
            ]);
        }

        $page = max(1, (int) $this->request->getGet('page'));
        $fType = strtolower(trim((string) $this->request->getGet('entity_type')));
        $fId   = (int) $this->request->getGet('entity_id');

        $allowedTypes = [
            'work',
            'owner',
            'license',
            'usage_report',
            'case',
            'user',
        ];
        if (! in_array($fType, $allowedTypes, true)) {
            $fType = '';
        }

        $model = model(AuditLogModel::class);
        $b     = $model->builder()
            ->select('audit_logs.*, users.display_name AS actor_name, users.email AS actor_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left');

        if ($fType !== '' && $fId > 0) {
            $b->where('audit_logs.entity_type', $fType)->where('audit_logs.entity_id', $fId);
        } elseif ($fType !== '') {
            $b->where('audit_logs.entity_type', $fType);
        }

        $total = (int) $b->countAllResults(false);

        $rows = $b->orderBy('audit_logs.created_at', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();

        $viewRows = [];
        foreach ($rows as $r) {
            $viewRows[] = $this->formatRow($r);
        }

        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return $this->layout('activities/index', [
            'pageTitle'         => lang('App.nav_activities'),
            'migrationRequired' => false,
            'rows'               => $viewRows,
            'pager'              => [
                'page'       => $page,
                'perPage'    => self::PER_PAGE,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
            'filterEntityType' => $fType,
            'filterEntityId'   => $fId > 0 ? (string) $fId : '',
        ]);
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, string|int|null>
     */
    private function formatRow(array $r): array
    {
        $eid = $r['entity_id'] ?? null;
        $et  = (string) ($r['entity_type'] ?? '');

        return [
            'id'            => (int) ($r['id'] ?? 0),
            'created_at'    => (string) ($r['created_at'] ?? ''),
            'actor'         => (string) ($r['actor_name'] ?? '') !== ''
                ? (string) $r['actor_name']
                : ((string) ($r['actor_email'] ?? '') !== '' ? (string) $r['actor_email'] : '—'),
            'action_type'   => (string) ($r['action_type'] ?? ''),
            'entity_type'   => $et,
            'entity_id'     => $eid !== null && $eid !== '' ? (int) $eid : null,
            'entity_label'  => $this->entityLabel($et, $eid !== null && $eid !== '' ? (int) $eid : null),
            'ip_address'    => (string) ($r['ip_address'] ?? ''),
        ];
    }

    private function entityLabel(string $entityType, ?int $entityId): string
    {
        if ($entityId === null || $entityId < 1) {
            return $entityType !== '' ? $entityType : '—';
        }

        return match ($entityType) {
            'work'          => 'Work #' . $entityId,
            'owner'         => 'Owner #' . $entityId,
            'license'       => 'License #' . $entityId,
            'usage_report'  => 'Usage report #' . $entityId,
            'case'          => 'Case #' . $entityId,
            'user'          => 'User #' . $entityId,
            default         => $entityType . ' #' . $entityId,
        };
    }
}
