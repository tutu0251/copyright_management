<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected $useTimestamps      = false;

    public static function schemaReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('audit_logs')
            && $db->fieldExists('action_type', 'audit_logs')
            && $db->fieldExists('old_values', 'audit_logs')
            && $db->fieldExists('new_values', 'audit_logs');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRecentWithUsers(int $limit = 100, int $offset = 0, ?string $createdSince = null): array
    {
        $limit  = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $b = $this->builder()
            ->select('audit_logs.*, users.display_name AS actor_name, users.email AS actor_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->orderBy('audit_logs.id', 'DESC');

        if ($createdSince !== null && $createdSince !== '') {
            $b->where('audit_logs.created_at >=', $createdSince);
        }

        return $b->limit($limit, $offset)->get()->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        if ($entityId < 1 || $entityType === '') {
            return [];
        }
        $limit = max(1, min(200, $limit));

        return $this->builder()
            ->select('audit_logs.*, users.display_name AS actor_name, users.email AS actor_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.entity_type', $entityType)
            ->where('audit_logs.entity_id', $entityId)
            ->orderBy('audit_logs.created_at', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function countSince(string $dateTimeStart): int
    {
        return (int) $this->builder()
            ->where('created_at >=', $dateTimeStart)
            ->countAllResults();
    }

    /**
     * @return list<array{user_id: int|null, action_count: int, display_name: string|null, email: string|null}>
     */
    public function topUsersSince(string $dateTimeStart, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        $rows = $this->builder()
            ->select('audit_logs.user_id, COUNT(audit_logs.id) AS action_count, users.display_name, users.email')
            ->join('users', 'users.id = audit_logs.user_id', 'inner')
            ->where('audit_logs.created_at >=', $dateTimeStart)
            ->where('audit_logs.user_id IS NOT NULL', null, false)
            ->groupBy('audit_logs.user_id, users.display_name, users.email')
            ->orderBy('action_count', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'user_id'       => isset($r['user_id']) ? (int) $r['user_id'] : null,
                'action_count'  => (int) ($r['action_count'] ?? 0),
                'display_name'  => $r['display_name'] ?? null,
                'email'         => $r['email'] ?? null,
            ];
        }

        return $out;
    }
}
