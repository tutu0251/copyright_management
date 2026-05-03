<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    public const TYPE_EXCLUSIVE        = 'exclusive';
    public const TYPE_NON_EXCLUSIVE    = 'non_exclusive';
    public const TYPE_COMMERCIAL       = 'commercial';
    public const TYPE_PERSONAL         = 'personal';
    public const TYPE_EDUCATIONAL      = 'educational';
    public const TYPE_INTERNAL_USE     = 'internal_use';

    /**
     * @var list<string>
     */
    public const LICENSE_TYPES = [
        self::TYPE_EXCLUSIVE,
        self::TYPE_NON_EXCLUSIVE,
        self::TYPE_COMMERCIAL,
        self::TYPE_PERSONAL,
        self::TYPE_EDUCATIONAL,
        self::TYPE_INTERNAL_USE,
    ];

    public const PAYMENT_UNPAID   = 'unpaid';
    public const PAYMENT_PAID     = 'paid';
    public const PAYMENT_PARTIAL  = 'partial';
    public const PAYMENT_WAIVED   = 'waived';

    /**
     * @var list<string>
     */
    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID,
        self::PAYMENT_PAID,
        self::PAYMENT_PARTIAL,
        self::PAYMENT_WAIVED,
    ];

    public const STATUS_DRAFT           = 'draft';
    public const STATUS_ACTIVE          = 'active';
    public const STATUS_EXPIRING_SOON   = 'expiring_soon';
    public const STATUS_EXPIRED         = 'expired';
    public const STATUS_CANCELLED       = 'cancelled';

    /**
     * @var list<string>
     */
    public const LICENSE_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRING_SOON,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    protected $table            = 'licenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'work_id',
        'licensee_id',
        'license_type',
        'territory',
        'start_date',
        'end_date',
        'fee_amount',
        'currency',
        'payment_status',
        'license_status',
        'terms',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'work_id'          => 'required|is_natural_no_zero',
        'licensee_id'      => 'required|is_natural_no_zero',
        'license_type'     => 'required|in_list[exclusive,non_exclusive,commercial,personal,educational,internal_use]',
        'territory'        => 'permit_empty|max_length[255]',
        'start_date'       => 'permit_empty|valid_date',
        'end_date'         => 'permit_empty|valid_date',
        'fee_amount'       => 'required|numeric|greater_than_equal_to[0]',
        'currency'         => 'required|exact_length[3]|alpha',
        'payment_status'   => 'required|in_list[unpaid,paid,partial,waived]',
        'license_status'   => 'required|in_list[draft,active,expiring_soon,expired,cancelled]',
        'terms'            => 'permit_empty|max_length[32000]',
    ];

    public static function licenseTypeLabel(string $t): string
    {
        return match ($t) {
            self::TYPE_EXCLUSIVE     => 'Exclusive',
            self::TYPE_NON_EXCLUSIVE => 'Non-exclusive',
            self::TYPE_COMMERCIAL    => 'Commercial',
            self::TYPE_PERSONAL      => 'Personal',
            self::TYPE_EDUCATIONAL   => 'Educational',
            self::TYPE_INTERNAL_USE => 'Internal Use',
            default                  => $t,
        };
    }

    public static function paymentLabel(string $p): string
    {
        return match ($p) {
            self::PAYMENT_PAID    => 'Paid',
            self::PAYMENT_PARTIAL => 'Partial',
            self::PAYMENT_WAIVED  => 'Waived',
            default               => 'Unpaid',
        };
    }

    public static function statusLabel(string $s): string
    {
        return match ($s) {
            self::STATUS_ACTIVE        => 'Active',
            self::STATUS_EXPIRING_SOON => 'Expiring Soon',
            self::STATUS_EXPIRED       => 'Expired',
            self::STATUS_CANCELLED     => 'Cancelled',
            default                    => 'Draft',
        };
    }

    /**
     * Effective status for UI (end date drives expired / expiring soon when applicable).
     *
     * @param array<string, mixed> $row
     */
    public static function effectiveStatus(array $row): string
    {
        $ls = (string) ($row['license_status'] ?? self::STATUS_DRAFT);
        if ($ls === self::STATUS_DRAFT || $ls === self::STATUS_CANCELLED) {
            return $ls;
        }

        $end = $row['end_date'] ?? null;
        if ($end !== null && $end !== '') {
            $endDay = strtotime((string) $end);
            if ($endDay !== false && $endDay < strtotime('today')) {
                return self::STATUS_EXPIRED;
            }
            if ($endDay !== false && $endDay <= strtotime('+30 days')) {
                return self::STATUS_EXPIRING_SOON;
            }
        }

        if ($ls === self::STATUS_EXPIRED) {
            return self::STATUS_ACTIVE;
        }

        return $ls;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithRelations(int $id): ?array
    {
        $wt = $this->db->prefixTable('works');
        $lt = $this->db->prefixTable('licensees');
        $lic = $this->db->prefixTable('licenses');

        $row = $this->builder()
            ->select("{$lic}.*", false)
            ->select('w.title AS work_title', false)
            ->select('le.name AS licensee_name', false)
            ->join("{$wt} w", "w.id = {$lic}.work_id", 'left')
            ->join("{$lt} le", "le.id = {$lic}.licensee_id", 'left')
            ->where("{$lic}.id", $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forWork(int $workId): array
    {
        $wt  = $this->db->prefixTable('works');
        $lt  = $this->db->prefixTable('licensees');
        $lic = $this->db->prefixTable('licenses');

        return $this->builder()
            ->select("{$lic}.*", false)
            ->select('le.name AS licensee_name', false)
            ->join("{$lt} le", "le.id = {$lic}.licensee_id", 'left')
            ->where("{$lic}.work_id", $workId)
            ->orderBy("{$lic}.end_date", 'ASC')
            ->orderBy("{$lic}.id", 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forLicensee(int $licenseeId): array
    {
        $wt  = $this->db->prefixTable('works');
        $lic = $this->db->prefixTable('licenses');

        return $this->builder()
            ->select("{$lic}.*", false)
            ->select('w.title AS work_title', false)
            ->join("{$wt} w", "w.id = {$lic}.work_id", 'left')
            ->where("{$lic}.licensee_id", $licenseeId)
            ->orderBy("{$lic}.start_date", 'DESC')
            ->orderBy("{$lic}.id", 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listIndexRows(?string $q = null): array
    {
        $wt  = $this->db->prefixTable('works');
        $lt  = $this->db->prefixTable('licensees');
        $lic = $this->db->prefixTable('licenses');

        $b = $this->builder()
            ->select("{$lic}.*", false)
            ->select('w.title AS work_title', false)
            ->select('le.name AS licensee_name', false)
            ->join("{$wt} w", "w.id = {$lic}.work_id", 'left')
            ->join("{$lt} le", "le.id = {$lic}.licensee_id", 'left');

        if ($q !== null && $q !== '') {
            $b->groupStart()
                ->like('w.title', $q)
                ->orLike('le.name', $q)
                ->orLike("{$lic}.territory", $q)
                ->groupEnd();
        }

        return $b->orderBy("{$lic}.updated_at", 'DESC')
            ->orderBy("{$lic}.id", 'DESC')
            ->limit(300)
            ->get()
            ->getResultArray();
    }

    /**
     * Sum paid license fees grouped by month of license created_at.
     *
     * @return array<string, float> keys 'Y-m' => amount
     */
    public function sumPaidFeesByLicenseCreatedMonth(int $monthsBack, ?string $workType = null): array
    {
        $monthsBack = max(1, min(24, $monthsBack));
        $start      = date('Y-m-01 00:00:00', strtotime('-' . ($monthsBack - 1) . ' months'));
        $lic        = $this->db->prefixTable('licenses');
        $wt         = $this->db->prefixTable('works');

        $b = $this->builder()
            ->select("DATE_FORMAT({$lic}.created_at, '%Y-%m') AS ym", false)
            ->select("COALESCE(SUM({$lic}.fee_amount), 0) AS total_fee", false)
            ->join("{$wt} w", "w.id = {$lic}.work_id", 'inner')
            ->where("{$lic}.deleted_at", null)
            ->where('w.deleted_at', null)
            ->where("{$lic}.payment_status", self::PAYMENT_PAID)
            ->where("{$lic}.created_at >=", $start);

        if ($workType !== null && $workType !== '') {
            $b->where('w.work_type', $workType);
        }

        $rows = $b->groupBy('ym')->orderBy('ym', 'ASC')->get()->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $ym = (string) ($r['ym'] ?? '');
            if ($ym !== '') {
                $map[$ym] = (float) ($r['total_fee'] ?? 0);
            }
        }

        return $map;
    }

    /**
     * Per calendar month: licenses in force on last day of month vs ended before that day.
     *
     * @return array{active: list<int>, expired: list<int>}
     */
    public function monthlyActiveVsExpiredEndOfMonth(int $monthsBack, ?string $workType = null): array
    {
        $monthsBack = max(1, min(24, $monthsBack));
        $lic        = $this->db->prefixTable('licenses');
        $wt         = $this->db->prefixTable('works');

        $workFilter = '';
        $bind       = [];
        if ($workType !== null && $workType !== '') {
            $workFilter = " AND EXISTS (SELECT 1 FROM `{$wt}` w2 WHERE w2.id = lic.work_id AND w2.deleted_at IS NULL AND w2.work_type = ?)";
            $bind[]     = $workType;
        }

        $active  = [];
        $expired = [];
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $first = date('Y-m-01', strtotime('-' . $i . ' months'));
            $last  = date('Y-m-t', strtotime($first));

            $sqlActive = "SELECT COUNT(*) AS c FROM `{$lic}` lic
                WHERE lic.deleted_at IS NULL
                AND lic.license_status NOT IN ('draft','cancelled')
                AND (lic.start_date IS NULL OR lic.start_date <= ?)
                AND (lic.end_date IS NULL OR lic.end_date >= ?)
                {$workFilter}";
            $bindA     = array_merge([$last, $last], $bind);
            $rowA      = $this->db->query($sqlActive, $bindA)->getRowArray();
            $active[]  = (int) ($rowA['c'] ?? 0);

            $sqlExp = "SELECT COUNT(*) AS c FROM `{$lic}` lic
                WHERE lic.deleted_at IS NULL
                AND lic.license_status NOT IN ('draft','cancelled')
                AND lic.end_date IS NOT NULL AND lic.end_date >= ? AND lic.end_date <= ?
                {$workFilter}";
            $bindE     = array_merge([$first, $last], $bind);
            $rowE      = $this->db->query($sqlExp, $bindE)->getRowArray();
            $expired[] = (int) ($rowE['c'] ?? 0);
        }

        return ['active' => $active, 'expired' => $expired];
    }

    /**
     * Top works by number of licenses (optional work type and license created window).
     *
     * @return list<array{work_id: int, title: string, license_count: int}>
     */
    public function topLicensedWorks(int $limit, ?string $workType = null, ?string $licenseCreatedSince = null): array
    {
        $limit = max(1, min(50, $limit));
        $wt    = $this->db->prefixTable('works');
        $lic   = $this->db->prefixTable('licenses');

        $sql  = "SELECT w.id AS work_id, w.title, COUNT(lic.id) AS license_count
            FROM `{$lic}` lic
            INNER JOIN `{$wt}` w ON w.id = lic.work_id AND w.deleted_at IS NULL
            WHERE lic.deleted_at IS NULL";
        $bind = [];
        if ($workType !== null && $workType !== '') {
            $sql .= ' AND w.work_type = ?';
            $bind[] = $workType;
        }
        if ($licenseCreatedSince !== null && $licenseCreatedSince !== '') {
            $sql .= ' AND lic.created_at >= ?';
            $bind[] = $licenseCreatedSince;
        }
        $sql .= ' GROUP BY w.id, w.title ORDER BY license_count DESC, w.id DESC LIMIT ' . $limit;

        $rows = $this->db->query($sql, $bind)->getResultArray();
        $out  = [];
        foreach ($rows as $r) {
            $out[] = [
                'work_id'       => (int) ($r['work_id'] ?? 0),
                'title'         => (string) ($r['title'] ?? ''),
                'license_count' => (int) ($r['license_count'] ?? 0),
            ];
        }

        return $out;
    }
}
