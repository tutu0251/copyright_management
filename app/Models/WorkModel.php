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
}
