<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class WorkModel extends Model
{
    protected $table            = 'works';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'slug',
        'work_type',
        'creator',
        'owner',
        'copyright_status',
        'risk_level',
        'description',
        'registered_at',
        'created_by',
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
        'title'             => 'required|min_length[2]|max_length[255]',
        'work_type'         => 'required|max_length[50]',
        'creator'           => 'permit_empty|max_length[255]',
        'owner'             => 'permit_empty|max_length[255]',
        'copyright_status'  => 'required|in_list[draft,registered,pending_review,under_audit]|max_length[50]',
        'risk_level'        => 'required|in_list[Low,Medium,High]',
        'description'       => 'permit_empty|max_length[16000]',
        'registered_at'     => 'permit_empty|valid_date',
    ];

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function formatForView(array $row): array
    {
        $id = (int) ($row['id'] ?? 0);
        $reg = $row['registered_at'] ?? null;
        $upd = $row['updated_at'] ?? null;

        return [
            'work_id'             => (string) $id,
            'id'                  => (string) $id,
            'title'               => (string) ($row['title'] ?? ''),
            'type'                => (string) ($row['work_type'] ?? ''),
            'creator'             => (string) ($row['creator'] ?? ''),
            'owner'               => (string) ($row['owner'] ?? ''),
            'copyright_status'    => (string) ($row['copyright_status'] ?? ''),
            'registration_date'   => $reg ? date('M j, Y', strtotime((string) $reg)) : '—',
            'registration_date_iso' => $reg ? (string) $reg : '',
            'last_updated'        => $upd ? date('M j, Y', strtotime((string) $upd)) : '—',
            'risk_level'          => (string) ($row['risk_level'] ?? 'Low'),
            'description'         => (string) ($row['description'] ?? ''),
            'license_count'       => (int) ($row['license_count'] ?? 0),
            'creators'            => $this->splitCreators((string) ($row['creator'] ?? '')),
            'identifiers'         => $id > 0 ? ['Work #' . $id] : [],
            'territory'           => '—',
        ];
    }

    /**
     * @return list<string>
     */
    private function splitCreators(string $creator): array
    {
        $parts = array_map('trim', explode(',', $creator));

        return array_values(array_filter($parts, static fn ($p) => $p !== ''));
    }

    /**
     * Unique slug for routing / uniqueness constraint.
     */
    public function makeUniqueSlug(string $title): string
    {
        $base = url_title($title, '-', true);
        if ($base === '') {
            $base = 'work';
        }

        $slug = $base . '-' . bin2hex(random_bytes(4));
        $exists = $this->withDeleted()->where('slug', $slug)->countAllResults();
        if ($exists > 0) {
            $slug = $base . '-' . bin2hex(random_bytes(8));
        }

        return $slug;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int}
     */
    public function getRegistryPage(int $page, int $perPage, string $search = ''): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $wt = $this->db->prefixTable('works');
        $lt = $this->db->prefixTable('licenses');

        $countBuilder = $this->builder();
        if ($search !== '') {
            $countBuilder->groupStart()
                ->like($wt . '.title', $search)
                ->orLike($wt . '.creator', $search)
                ->orLike($wt . '.owner', $search)
                ->groupEnd();
        }

        $total = (int) $countBuilder->countAllResults();

        $totalPages = (int) max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $rowsBuilder = $this->builder();
        $rowsBuilder->select("{$wt}.*", false);
        $rowsBuilder->select("(SELECT COUNT(*) FROM {$lt} lic WHERE lic.work_id = {$wt}.id AND lic.deleted_at IS NULL) AS license_count", false);
        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like($wt . '.title', $search)
                ->orLike($wt . '.creator', $search)
                ->orLike($wt . '.owner', $search)
                ->groupEnd();
        }
        $rows = $rowsBuilder->orderBy($wt . '.updated_at', 'DESC')
            ->orderBy($wt . '.id', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return ['rows' => $rows, 'total' => $total, 'page' => $page];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithLicenseCount(int $id): ?array
    {
        $wt = $this->db->prefixTable('works');
        $lt = $this->db->prefixTable('licenses');

        $row = $this->builder()
            ->select("{$wt}.*", false)
            ->select("(SELECT COUNT(*) FROM {$lt} lic WHERE lic.work_id = {$wt}.id AND lic.deleted_at IS NULL) AS license_count", false)
            ->where($wt . '.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Distinct work_type values for filters (non-deleted works only).
     *
     * @return list<string>
     */
    public function distinctWorkTypes(): array
    {
        $rows = $this->builder()
            ->select('work_type')
            ->where('deleted_at', null)
            ->where('work_type !=', '')
            ->groupBy('work_type')
            ->orderBy('work_type', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $t = trim((string) ($r['work_type'] ?? ''));
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }

    /**
     * New works per calendar month (by created_at), from first day of (monthsBack-1) ago through now.
     *
     * @return array<string, int> keys 'Y-m' => count
     */
    public function countNewWorksByMonth(int $monthsBack, ?string $workType = null): array
    {
        $monthsBack = max(1, min(24, $monthsBack));
        $start      = date('Y-m-01 00:00:00', strtotime('-' . ($monthsBack - 1) . ' months'));

        $b = $this->builder()
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS ym", false)
            ->select('COUNT(*) AS c', false)
            ->where('deleted_at', null)
            ->where('created_at >=', $start);

        if ($workType !== null && $workType !== '') {
            $b->where('work_type', $workType);
        }

        $rows = $b->groupBy('ym')->orderBy('ym', 'ASC')->get()->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $ym = (string) ($r['ym'] ?? '');
            if ($ym !== '') {
                $map[$ym] = (int) ($r['c'] ?? 0);
            }
        }

        return $map;
    }

    /**
     * Total non-deleted works, optionally filtered by work_type.
     */
    public function countCatalog(?string $workType = null): int
    {
        $b = $this->builder()->where('deleted_at', null);
        if ($workType !== null && $workType !== '') {
            $b->where('work_type', $workType);
        }

        return (int) $b->countAllResults();
    }

    /**
     * Count of works whose created_at falls in [start, end] (inclusive day bounds).
     *
     * @param non-empty-string|null $start 'Y-m-d' or datetime
     * @param non-empty-string|null $end   'Y-m-d' or datetime
     */
    public function countCreatedBetween(?string $start, ?string $end, ?string $workType = null): int
    {
        $b = $this->builder()->where('deleted_at', null);
        if ($workType !== null && $workType !== '') {
            $b->where('work_type', $workType);
        }
        if ($start !== null && $start !== '') {
            $b->where('created_at >=', $start);
        }
        if ($end !== null && $end !== '') {
            $endBound = strlen((string) $end) === 10 ? $end . ' 23:59:59' : $end;
            $b->where('created_at <=', $endBound);
        }

        return (int) $b->countAllResults();
    }

    /**
     * Works grouped by work_type (non-deleted), optional created_at window and type filter.
     *
     * @return list<array{work_type: string, c: int}>
     */
    public function countsByWorkTypeWindow(?string $start, ?string $end, ?string $workTypeOnly = null): array
    {
        $b = $this->builder()
            ->select('work_type')
            ->select('COUNT(*) AS c', false)
            ->where('deleted_at', null)
            ->where('work_type !=', '');

        if ($workTypeOnly !== null && $workTypeOnly !== '') {
            $b->where('work_type', $workTypeOnly);
        }
        if ($start !== null && $start !== '') {
            $b->where('created_at >=', $start);
        }
        if ($end !== null && $end !== '') {
            $endBound = strlen((string) $end) === 10 ? $end . ' 23:59:59' : $end;
            $b->where('created_at <=', $endBound);
        }

        $rows = $b->groupBy('work_type')->orderBy('c', 'DESC')->get()->getResultArray();
        $out  = [];
        foreach ($rows as $r) {
            $out[] = [
                'work_type' => (string) ($r['work_type'] ?? ''),
                'c'         => (int) ($r['c'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * New works per day (created_at) within inclusive date range (Y-m-d).
     *
     * @return list<array{d: string, c: int}> contiguous days with zero fill
     */
    public function countCreatedByDayBetween(string $startDate, string $endDate, ?string $workType = null): array
    {
        $startTs = strtotime($startDate . ' 00:00:00');
        $endTs   = strtotime($endDate . ' 23:59:59');
        if ($startTs === false || $endTs === false || $startTs > $endTs) {
            return [];
        }

        $b = $this->builder()
            ->select("DATE(created_at) AS d", false)
            ->select('COUNT(*) AS c', false)
            ->where('deleted_at', null)
            ->where('DATE(created_at) >=', date('Y-m-d', $startTs))
            ->where('DATE(created_at) <=', date('Y-m-d', $endTs));

        if ($workType !== null && $workType !== '') {
            $b->where('work_type', $workType);
        }

        $rows = $b->groupBy('DATE(created_at)', false)->orderBy('d', 'ASC')->get()->getResultArray();

        $map = [];
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

    /**
     * Top owners by distinct linked work count (work_owners pivot).
     *
     * @return list<array{owner_id: int, name: string, work_count: int}>
     */
    public function topOwnersByLinkedWorks(int $limit, ?string $workCreatedStart, ?string $workCreatedEnd, ?string $workType = null): array
    {
        if (! $this->db->tableExists('work_owners') || ! $this->db->tableExists('owners')) {
            return [];
        }

        $limit = max(1, min(50, $limit));
        $wt    = $this->db->prefixTable('works');
        $wo    = $this->db->prefixTable('work_owners');
        $ow    = $this->db->prefixTable('owners');

        $sql  = "SELECT o.id AS owner_id, o.name, COUNT(DISTINCT w.id) AS work_count
            FROM `{$wo}` wo
            INNER JOIN `{$ow}` o ON o.id = wo.owner_id";
        if ($this->db->fieldExists('deleted_at', 'owners')) {
            $sql .= ' AND o.deleted_at IS NULL';
        }
        $sql .= " INNER JOIN `{$wt}` w ON w.id = wo.work_id AND w.deleted_at IS NULL
            WHERE wo.deleted_at IS NULL";
        $bind = [];
        if ($workType !== null && $workType !== '') {
            $sql .= ' AND w.work_type = ?';
            $bind[] = $workType;
        }
        if ($workCreatedStart !== null && $workCreatedStart !== '') {
            $sql .= ' AND w.created_at >= ?';
            $bind[] = $workCreatedStart;
        }
        if ($workCreatedEnd !== null && $workCreatedEnd !== '') {
            $sql .= ' AND w.created_at <= ?';
            $bind[] = $workCreatedEnd . (strlen($workCreatedEnd) === 10 ? ' 23:59:59' : '');
        }
        $sql .= ' GROUP BY o.id, o.name ORDER BY work_count DESC, o.id ASC LIMIT ' . $limit;

        $rows = $this->db->query($sql, $bind)->getResultArray();
        $out   = [];
        foreach ($rows as $r) {
            $out[] = [
                'owner_id'   => (int) ($r['owner_id'] ?? 0),
                'name'       => (string) ($r['name'] ?? ''),
                'work_count' => (int) ($r['work_count'] ?? 0),
            ];
        }

        return $out;
    }
}
