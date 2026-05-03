<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLogModel;
use CodeIgniter\HTTP\IncomingRequest;

class AuditLogService
{
    public const ACTION_CREATE        = 'CREATE';
    public const ACTION_UPDATE       = 'UPDATE';
    public const ACTION_DELETE       = 'DELETE';
    public const ACTION_LOGIN        = 'LOGIN';
    public const ACTION_LOGOUT       = 'LOGOUT';
    public const ACTION_STATUS_CHANGE = 'STATUS_CHANGE';

    public const ENTITY_WORK         = 'work';
    public const ENTITY_OWNER        = 'owner';
    public const ENTITY_LICENSE      = 'license';
    public const ENTITY_USAGE_REPORT = 'usage_report';
    public const ENTITY_CASE         = 'case';
    public const ENTITY_USER         = 'user';

    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_hash',
        'password_confirm',
        'remember_token',
        'csrf_test_name',
    ];

    /**
     * @param array<string, mixed>|string|null $oldData
     * @param array<string, mixed>|string|null $newData
     */
    public function log(
        string $actionType,
        string $entityType,
        ?int $entityId,
        array|string|null $oldData = null,
        array|string|null $newData = null,
        ?int $userId = null,
        ?IncomingRequest $request = null,
    ): void {
        if (! AuditLogModel::schemaReady()) {
            return;
        }

        $req = $request ?? service('request');

        $uid = $userId;
        if ($uid === null) {
            $sess = session();
            $v = $sess->get('auth_user_id');
            $uid = is_numeric($v) ? (int) $v : null;
        }

        $oldNorm = $this->normalizePayload($oldData);
        $newNorm = $this->normalizePayload($newData);

        $ua = (string) $req->getUserAgent();
        if (strlen($ua) > 512) {
            $ua = substr($ua, 0, 512);
        }

        $row = [
            'user_id'      => $uid > 0 ? $uid : null,
            'action_type'  => $actionType,
            'entity_type'  => $entityType,
            'entity_id'    => $entityId !== null && $entityId > 0 ? $entityId : null,
            'old_values'   => $oldNorm === null ? null : json_encode($oldNorm, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
            'new_values'   => $newNorm === null ? null : json_encode($newNorm, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
            'ip_address'   => $req->getIPAddress(),
            'user_agent'   => $ua !== '' ? $ua : null,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        db_connect()->table('audit_logs')->insert($row);
    }

    /**
     * @param array<string, mixed>|string|null $data
     * @return array<string, mixed>|null
     */
    private function normalizePayload(array|string|null $data): ?array
    {
        if ($data === null) {
            return null;
        }
        if (is_string($data)) {
            $trim = trim($data);

            return $trim === '' ? null : ['message' => $this->truncate($trim, 2000)];
        }
        if ($data === []) {
            return null;
        }

        $clean = $this->stripSensitive($data);

        return $this->truncateDeep($clean, 8000);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function stripSensitive(array $data): array
    {
        foreach (self::SENSITIVE_KEYS as $k) {
            unset($data[$k]);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function truncateDeep(array $data, int $maxJsonBytes): array
    {
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        if (strlen($encoded) <= $maxJsonBytes) {
            return $data;
        }

        $out = [];
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $out[$k] = $this->truncate($v, 500);
            } elseif (is_array($v)) {
                $out[$k] = $this->truncateDeep($v, (int) max(500, $maxJsonBytes / 2));
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    private function truncate(string $s, int $max): string
    {
        if (strlen($s) <= $max) {
            return $s;
        }

        return substr($s, 0, $max - 3) . '...';
    }
}
