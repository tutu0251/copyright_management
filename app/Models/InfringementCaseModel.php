<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class InfringementCaseModel extends Model
{
    public const STATUS_DETECTED      = 'detected';
    public const STATUS_UNDER_REVIEW  = 'under_review';
    public const STATUS_NOTICE_SENT   = 'notice_sent';
    public const STATUS_NEGOTIATION   = 'negotiation';
    public const STATUS_RESOLVED      = 'resolved';
    public const STATUS_REJECTED     = 'rejected';

    public const PRIORITY_LOW      = 'low';
    public const PRIORITY_MEDIUM   = 'medium';
    public const PRIORITY_HIGH     = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    /**
     * Ordered primary workflow (excludes rejected).
     *
     * @var list<string>
     */
    public const WORKFLOW_STATUSES = [
        self::STATUS_DETECTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_NOTICE_SENT,
        self::STATUS_NEGOTIATION,
        self::STATUS_RESOLVED,
    ];

    /**
     * @var list<string>
     */
    public const ALL_STATUSES = [
        self::STATUS_DETECTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_NOTICE_SENT,
        self::STATUS_NEGOTIATION,
        self::STATUS_RESOLVED,
        self::STATUS_REJECTED,
    ];

    /**
     * @var list<string>
     */
    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
        self::PRIORITY_CRITICAL,
    ];

    protected $table            = 'infringement_cases';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'work_id',
        'usage_report_id',
        'case_title',
        'case_status',
        'priority',
        'assigned_to',
        'opened_at',
        'closed_at',
        'description',
        'resolution_notes',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'work_id'            => 'required|is_natural_no_zero',
        'usage_report_id'    => 'permit_empty|integer',
        'case_title'         => 'required|max_length[255]',
        'case_status'        => 'required|in_list[detected,under_review,notice_sent,negotiation,resolved,rejected]',
        'priority'           => 'required|in_list[low,medium,high,critical]',
        'assigned_to'        => 'permit_empty|integer',
        'opened_at'          => 'permit_empty|max_length[32]',
        'closed_at'          => 'permit_empty|max_length[32]',
        'description'        => 'permit_empty|max_length[16000]',
        'resolution_notes'   => 'permit_empty|max_length[16000]',
    ];

    public static function schemaReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('infringement_cases') && $db->fieldExists('case_status', 'infringement_cases');
    }

    public static function statusLabel(string $slug): string
    {
        return match ($slug) {
            self::STATUS_DETECTED     => 'Detected',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_NOTICE_SENT  => 'Notice Sent',
            self::STATUS_NEGOTIATION  => 'Negotiation',
            self::STATUS_RESOLVED     => 'Resolved',
            self::STATUS_REJECTED    => 'Rejected',
            default                   => $slug,
        };
    }

    public static function statusBadgeTone(string $slug): string
    {
        return match ($slug) {
            self::STATUS_RESOLVED     => 'success',
            self::STATUS_REJECTED    => 'neutral',
            self::STATUS_DETECTED     => 'warning',
            self::STATUS_UNDER_REVIEW, self::STATUS_NOTICE_SENT, self::STATUS_NEGOTIATION => 'warning',
            default                   => 'neutral',
        };
    }

    public static function priorityLabel(string $slug): string
    {
        return match ($slug) {
            self::PRIORITY_LOW      => 'Low',
            self::PRIORITY_MEDIUM   => 'Medium',
            self::PRIORITY_HIGH     => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
            default                 => $slug,
        };
    }

    public static function priorityTone(string $slug): string
    {
        return match ($slug) {
            self::PRIORITY_CRITICAL => 'danger',
            self::PRIORITY_HIGH     => 'warning',
            self::PRIORITY_MEDIUM   => 'neutral',
            self::PRIORITY_LOW      => 'neutral',
            default                 => 'neutral',
        };
    }

    public static function isTerminalStatus(string $slug): bool
    {
        return in_array($slug, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true);
    }

    public static function isOpenStatus(string $slug): bool
    {
        return ! self::isTerminalStatus($slug);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listIndexRows(?string $q = null, ?string $status = null, ?string $priority = null, int $limit = 200): array
    {
        if (! self::schemaReady()) {
            return [];
        }

        $ic = $this->db->prefixTable('infringement_cases');
        $w  = $this->db->prefixTable('works');
        $u  = $this->db->prefixTable('users');

        $b = $this->builder()
            ->select("{$ic}.*", false)
            ->select('w.title AS work_title', false)
            ->select('u.display_name AS assignee_name', false)
            ->join("{$w} w", "w.id = {$ic}.work_id", 'inner')
            ->join("{$u} u", "u.id = {$ic}.assigned_to", 'left');

        if ($this->db->fieldExists('deleted_at', 'works')) {
            $b->where('w.deleted_at', null);
        }

        $b->orderBy("{$ic}.opened_at", 'DESC')
            ->orderBy("{$ic}.id", 'DESC')
            ->limit($limit);

        if ($status !== null && $status !== '' && in_array($status, self::ALL_STATUSES, true)) {
            $b->where("{$ic}.case_status", $status);
        }

        if ($priority !== null && $priority !== '' && in_array($priority, self::PRIORITIES, true)) {
            $b->where("{$ic}.priority", $priority);
        }

        if ($q !== null && $q !== '') {
            $b->groupStart()
                ->like("{$ic}.case_title", $q, 'both')
                ->orLike('w.title', $q, 'both')
                ->orLike("{$ic}.description", $q, 'both')
                ->groupEnd();
        }

        return $b->get()->getResultArray();
    }

    /**
     * @return ($id is int ? array<string, mixed>|null : null)
     */
    public function findWithRelations(int $id): ?array
    {
        if (! self::schemaReady()) {
            return null;
        }

        $ic = $this->db->prefixTable('infringement_cases');
        $w  = $this->db->prefixTable('works');
        $u  = $this->db->prefixTable('users');

        $b = $this->builder()
            ->select("{$ic}.*", false)
            ->select('w.title AS work_title', false)
            ->select('u.display_name AS assignee_name', false)
            ->select('u.email AS assignee_email', false)
            ->join("{$w} w", "w.id = {$ic}.work_id", 'inner')
            ->join("{$u} u", "u.id = {$ic}.assigned_to", 'left')
            ->where("{$ic}.id", $id);

        if ($this->db->fieldExists('deleted_at', 'works')) {
            $b->where('w.deleted_at', null);
        }

        $row = $b->get()->getRowArray();

        return $row ?: null;
    }

    public function usageReportAlreadyLinked(int $usageReportId): bool
    {
        if ($usageReportId < 1 || ! self::schemaReady()) {
            return false;
        }

        $row = $this->builder()
            ->select('id')
            ->where('usage_report_id', $usageReportId)
            ->get()
            ->getRowArray();

        return $row !== null;
    }

    /**
     * @return array<string, int>
     */
    public function countsByStatus(): array
    {
        if (! self::schemaReady()) {
            return [];
        }

        $ic = $this->db->prefixTable('infringement_cases');
        $rows = $this->db->query(
            "SELECT case_status AS s, COUNT(*) AS c FROM `{$ic}` GROUP BY case_status",
        )->getResultArray();

        $out = [];
        foreach (self::ALL_STATUSES as $st) {
            $out[$st] = 0;
        }
        foreach ($rows as $r) {
            $slug = (string) ($r['s'] ?? '');
            if ($slug !== '') {
                $out[$slug] = (int) ($r['c'] ?? 0);
            }
        }

        return $out;
    }

    public function countOpen(): int
    {
        if (! self::schemaReady()) {
            return 0;
        }

        return (int) $this->whereNotIn('case_status', [self::STATUS_RESOLVED, self::STATUS_REJECTED])
            ->countAllResults();
    }

    public function countHighPriorityOpen(): int
    {
        if (! self::schemaReady()) {
            return 0;
        }

        return (int) $this->whereNotIn('case_status', [self::STATUS_RESOLVED, self::STATUS_REJECTED])
            ->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL])
            ->countAllResults();
    }

    public function countResolved(): int
    {
        if (! self::schemaReady()) {
            return 0;
        }

        return (int) $this->where('case_status', self::STATUS_RESOLVED)->countAllResults();
    }

    public function syncClosedTimestamp(int $caseId, string $newStatus): void
    {
        if ($caseId < 1) {
            return;
        }

        if (self::isTerminalStatus($newStatus)) {
            $this->update($caseId, ['closed_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->update($caseId, ['closed_at' => null]);
        }
    }
}
