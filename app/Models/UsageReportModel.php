<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UsageReportModel extends Model
{
    public const DETECTED_WEBSITE       = 'website';
    public const DETECTED_SOCIAL        = 'social_media';
    public const DETECTED_VIDEO         = 'video_platform';
    public const DETECTED_MARKETPLACE   = 'marketplace';
    public const DETECTED_INTERNAL      = 'internal';

    public const USAGE_AUTHORIZED       = 'authorized';
    public const USAGE_SUSPECTED        = 'suspected';
    public const USAGE_INFRINGEMENT     = 'infringement';

    public const METHOD_MANUAL          = 'manual';
    public const METHOD_AI              = 'ai';

    /**
     * @var list<string>
     */
    public const DETECTED_TYPES = [
        self::DETECTED_WEBSITE,
        self::DETECTED_SOCIAL,
        self::DETECTED_VIDEO,
        self::DETECTED_MARKETPLACE,
        self::DETECTED_INTERNAL,
    ];

    /**
     * @var list<string>
     */
    public const USAGE_TYPES = [
        self::USAGE_AUTHORIZED,
        self::USAGE_SUSPECTED,
        self::USAGE_INFRINGEMENT,
    ];

    /**
     * @var list<string>
     */
    public const DETECTION_METHODS = [
        self::METHOD_MANUAL,
        self::METHOD_AI,
    ];

    protected $table            = 'usage_reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'work_id',
        'detected_source',
        'detected_type',
        'usage_type',
        'detection_method',
        'detected_at',
        'reported_by',
        'notes',
        'evidence_path',
        'evidence_mime_type',
        'evidence_uploaded_by',
        'evidence_uploaded_at',
        'infringement_case_id',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';
    protected $deletedField       = 'deleted_at';

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'work_id'           => 'required|is_natural_no_zero',
        'detected_source'   => 'required|max_length[512]',
        'detected_type'     => 'required|in_list[website,social_media,video_platform,marketplace,internal]',
        'usage_type'        => 'required|in_list[authorized,suspected,infringement]',
        'detection_method'  => 'permit_empty|in_list[manual,ai]',
        'detected_at'       => 'permit_empty|max_length[32]',
        'notes'             => 'permit_empty|max_length[16000]',
        'evidence_path'     => 'permit_empty|max_length[512]',
        'evidence_mime_type' => 'permit_empty|max_length[191]',
        'evidence_uploaded_by' => 'permit_empty|integer',
        'evidence_uploaded_at' => 'permit_empty|max_length[32]',
        'infringement_case_id' => 'permit_empty|integer',
    ];

    public static function detectedTypeLabel(string $slug): string
    {
        return match ($slug) {
            self::DETECTED_WEBSITE     => 'Website',
            self::DETECTED_SOCIAL      => 'Social Media',
            self::DETECTED_VIDEO       => 'Video Platform',
            self::DETECTED_MARKETPLACE => 'Marketplace',
            self::DETECTED_INTERNAL    => 'Internal',
            default                    => $slug,
        };
    }

    public static function usageTypeLabel(string $slug): string
    {
        return match ($slug) {
            self::USAGE_AUTHORIZED   => 'Authorized',
            self::USAGE_SUSPECTED    => 'Suspected',
            self::USAGE_INFRINGEMENT => 'Infringement',
            default                  => $slug,
        };
    }

    public static function usageTypeBadgeTone(string $slug): string
    {
        return match ($slug) {
            self::USAGE_AUTHORIZED   => 'success',
            self::USAGE_SUSPECTED    => 'warning',
            self::USAGE_INFRINGEMENT => 'danger',
            default                  => 'neutral',
        };
    }

    public static function detectionMethodLabel(string $slug): string
    {
        return match ($slug) {
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_AI     => 'AI (coming soon)',
            default             => $slug,
        };
    }

    /**
     * True when `usage_reports` has the Step 5 monitoring columns (not the legacy license-period table).
     */
    public static function monitoringSchemaReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('usage_reports') && $db->fieldExists('work_id', 'usage_reports');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listIndexRows(?string $q = null, ?string $usageType = null, int $limit = 200): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $b = $this->builder()
            ->select("{$ur}.*", false)
            ->select('w.title AS work_title', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null)
            ->orderBy("{$ur}.detected_at", 'DESC')
            ->orderBy("{$ur}.id", 'DESC')
            ->limit($limit);

        if ($usageType !== null && $usageType !== '' && in_array($usageType, self::USAGE_TYPES, true)) {
            $b->where("{$ur}.usage_type", $usageType);
        }

        if ($q !== null && $q !== '') {
            $b->groupStart()
                ->like('w.title', $q, 'both')
                ->orLike("{$ur}.detected_source", $q, 'both')
                ->orLike("{$ur}.notes", $q, 'both')
                ->groupEnd();
        }

        return $b->get()->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forWork(int $workId, int $limit = 100): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $ur = $this->db->prefixTable('usage_reports');

        return $this->builder()
            ->where("{$ur}.work_id", $workId)
            ->where("{$ur}.deleted_at", null)
            ->orderBy("{$ur}.detected_at", 'DESC')
            ->orderBy("{$ur}.id", 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return ($id is int ? array<string, mixed>|null : null)
     */
    public function findWithWork(int $id): ?array
    {
        if (! self::monitoringSchemaReady()) {
            return null;
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $row = $this->builder()
            ->select("{$ur}.*", false)
            ->select('w.title AS work_title', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.id", $id)
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public static function isValidDetectedSource(string $raw): bool
    {
        $s = trim($raw);
        if ($s === '' || strlen($s) > 512) {
            return false;
        }
        if (preg_match('#^https?://#i', $s) === 1) {
            return filter_var($s, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRecentDetections(int $days = 7, int $limit = 12): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $since = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        return $this->builder()
            ->select("{$ur}.id, {$ur}.detected_source, {$ur}.detected_at, {$ur}.usage_type", false)
            ->select('w.title AS work_title', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null)
            ->where("{$ur}.detected_at >=", $since)
            ->orderBy("{$ur}.detected_at", 'DESC')
            ->orderBy("{$ur}.id", 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Works with the most usage reports (optionally since detected_at and by work type).
     *
     * @return list<array{work_id: int, title: string, report_count: int}>
     */
    public function topWorksByReportCount(int $limit, ?string $detectedSince = null, ?string $workType = null): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $limit = max(1, min(50, $limit));
        $ur    = $this->db->prefixTable('usage_reports');
        $wt    = $this->db->prefixTable('works');

        $sql  = "SELECT w.id AS work_id, w.title, COUNT(ur.id) AS report_count
            FROM `{$ur}` ur
            INNER JOIN `{$wt}` w ON w.id = ur.work_id AND w.deleted_at IS NULL
            WHERE ur.deleted_at IS NULL";
        $bind = [];
        if ($detectedSince !== null && $detectedSince !== '') {
            $sql .= ' AND ur.detected_at >= ?';
            $bind[] = $detectedSince;
        }
        if ($workType !== null && $workType !== '') {
            $sql .= ' AND w.work_type = ?';
            $bind[] = $workType;
        }
        $sql .= ' GROUP BY w.id, w.title ORDER BY report_count DESC, w.id DESC LIMIT ' . $limit;

        $rows = $this->db->query($sql, $bind)->getResultArray();
        $out  = [];
        foreach ($rows as $r) {
            $out[] = [
                'work_id'      => (int) ($r['work_id'] ?? 0),
                'title'        => (string) ($r['title'] ?? ''),
                'report_count' => (int) ($r['report_count'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, int> usage_type => count
     */
    public function countsByUsageTypeBetween(?string $start, ?string $end, ?string $workType = null): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $b = $this->builder()
            ->select("{$ur}.usage_type AS ut", false)
            ->select('COUNT(*) AS c', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null);

        if ($workType !== null && $workType !== '') {
            $b->where('w.work_type', $workType);
        }
        if ($start !== null && $start !== '') {
            $b->where("{$ur}.detected_at >=", $start);
        }
        if ($end !== null && $end !== '') {
            $endBound = strlen((string) $end) === 10 ? $end . ' 23:59:59' : $end;
            $b->where("{$ur}.detected_at <=", $endBound);
        }

        $rows = $b->groupBy("{$ur}.usage_type")->get()->getResultArray();
        $out  = [];
        foreach (self::USAGE_TYPES as $u) {
            $out[$u] = 0;
        }
        foreach ($rows as $r) {
            $k = (string) ($r['ut'] ?? '');
            if ($k !== '') {
                $out[$k] = (int) ($r['c'] ?? 0);
            }
        }

        return $out;
    }

    /**
     * @return array<string, int> detected_type => count
     */
    public function countsByDetectedTypeBetween(?string $start, ?string $end, ?string $workType = null): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $b = $this->builder()
            ->select("{$ur}.detected_type AS dt", false)
            ->select('COUNT(*) AS c', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null);

        if ($workType !== null && $workType !== '') {
            $b->where('w.work_type', $workType);
        }
        if ($start !== null && $start !== '') {
            $b->where("{$ur}.detected_at >=", $start);
        }
        if ($end !== null && $end !== '') {
            $endBound = strlen((string) $end) === 10 ? $end . ' 23:59:59' : $end;
            $b->where("{$ur}.detected_at <=", $endBound);
        }

        $rows = $b->groupBy("{$ur}.detected_type")->get()->getResultArray();
        $out  = [];
        foreach (self::DETECTED_TYPES as $d) {
            $out[$d] = 0;
        }
        foreach ($rows as $r) {
            $k = (string) ($r['dt'] ?? '');
            if ($k !== '') {
                $out[$k] = (int) ($r['c'] ?? 0);
            }
        }

        return $out;
    }

    /**
     * Detections per calendar day (DATE(detected_at)), zero-filled between bounds.
     *
     * @return list<array{d: string, c: int}>
     */
    public function countDetectionsByDayBetween(string $startDate, string $endDate, ?string $workType = null): array
    {
        if (! self::monitoringSchemaReady()) {
            return [];
        }

        $startTs = strtotime($startDate . ' 00:00:00');
        $endTs   = strtotime($endDate . ' 23:59:59');
        if ($startTs === false || $endTs === false || $startTs > $endTs) {
            return [];
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $b = $this->builder()
            ->select("DATE({$ur}.detected_at) AS d", false)
            ->select('COUNT(*) AS c', false)
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null)
            ->where("DATE({$ur}.detected_at) >=", date('Y-m-d', $startTs))
            ->where("DATE({$ur}.detected_at) <=", date('Y-m-d', $endTs));

        if ($workType !== null && $workType !== '') {
            $b->where('w.work_type', $workType);
        }

        $rows = $b->groupBy("DATE({$ur}.detected_at)", false)->orderBy('d', 'ASC')->get()->getResultArray();
        $map  = [];
        foreach ($rows as $r) {
            $d = (string) ($r['d'] ?? '');
            if ($d !== '') {
                $map[$d] = (int) ($r['c'] ?? 0);
            }
        }

        $out = [];
        for ($t = $startTs; $t <= $endTs; $t += 86400) {
            $day = date('Y-m-d', $t);
            $out[] = ['d' => $day, 'c' => $map[$day] ?? 0];
        }

        return $out;
    }

    public function countDetectionsBetween(?string $start, ?string $end, ?string $workType = null): int
    {
        if (! self::monitoringSchemaReady()) {
            return 0;
        }

        $ur = $this->db->prefixTable('usage_reports');
        $wt = $this->db->prefixTable('works');

        $b = $this->builder()
            ->join("{$wt} w", "w.id = {$ur}.work_id", 'inner')
            ->where("{$ur}.deleted_at", null)
            ->where('w.deleted_at', null);

        if ($workType !== null && $workType !== '') {
            $b->where('w.work_type', $workType);
        }
        if ($start !== null && $start !== '') {
            $b->where("{$ur}.detected_at >=", $start);
        }
        if ($end !== null && $end !== '') {
            $endBound = strlen((string) $end) === 10 ? $end . ' 23:59:59' : $end;
            $b->where("{$ur}.detected_at <=", $endBound);
        }

        return (int) $b->countAllResults();
    }
}
