<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class WorkOwnerModel extends Model
{
    public const ROLE_CREATOR          = 'creator';
    public const ROLE_COPYRIGHT_OWNER  = 'copyright_owner';
    public const ROLE_PUBLISHER        = 'publisher';
    public const ROLE_AGENCY           = 'agency';
    public const ROLE_DISTRIBUTOR      = 'distributor';

    /**
     * @var list<string>
     */
    public const ROLES = [
        self::ROLE_CREATOR,
        self::ROLE_COPYRIGHT_OWNER,
        self::ROLE_PUBLISHER,
        self::ROLE_AGENCY,
        self::ROLE_DISTRIBUTOR,
    ];

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ENDED    = 'ended';

    /**
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_ENDED,
    ];

    protected $table            = 'work_owners';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'work_id',
        'owner_id',
        'ownership_percentage',
        'ownership_role',
        'start_date',
        'end_date',
        'status',
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
        'work_id'                => 'required|is_natural_no_zero',
        'owner_id'               => 'required|is_natural_no_zero',
        'ownership_percentage'   => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'ownership_role'         => 'required|in_list[creator,copyright_owner,publisher,agency,distributor]',
        'start_date'             => 'permit_empty|valid_date',
        'end_date'               => 'permit_empty|valid_date',
        'status'                 => 'required|in_list[active,inactive,ended]',
    ];

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_CREATOR         => 'Creator',
            self::ROLE_COPYRIGHT_OWNER => 'Copyright Owner',
            self::ROLE_PUBLISHER       => 'Publisher',
            self::ROLE_AGENCY          => 'Agency',
            self::ROLE_DISTRIBUTOR     => 'Distributor',
            default                    => $role,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ENDED    => 'Ended',
            default               => $status,
        };
    }

    /**
     * Sum of ownership_percentage for active, non-deleted rows (used for the 100% cap).
     */
    public function sumActivePercentageForWork(int $workId, ?int $excludeWorkOwnerId = null): float
    {
        $b = $this->db->table($this->table)
            ->where('work_id', $workId)
            ->where('status', self::STATUS_ACTIVE)
            ->where('deleted_at', null);
        if ($excludeWorkOwnerId !== null) {
            $b->where('id !=', $excludeWorkOwnerId);
        }
        $row = $b->selectSum('ownership_percentage', 'pct')->get()->getRowArray();

        return round((float) ($row['pct'] ?? 0), 2);
    }

    public function hasActiveLink(int $workId, int $ownerId): bool
    {
        return $this->db->table($this->table)
            ->where(['work_id' => $workId, 'owner_id' => $ownerId])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forWorkWithOwners(int $workId): array
    {
        $wo = $this->db->prefixTable('work_owners');
        $ow = $this->db->prefixTable('owners');

        return $this->select("{$wo}.*", false)
            ->select("{$ow}.name AS owner_name, {$ow}.email AS owner_email, {$ow}.owner_type", false)
            ->join($ow, "{$ow}.id = {$wo}.owner_id", 'inner')
            ->where("{$ow}.deleted_at", null)
            ->where("{$wo}.work_id", $workId)
            ->orderBy("{$wo}.id", 'ASC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forOwnerWithWorks(int $ownerId): array
    {
        $wo = $this->db->prefixTable('work_owners');
        $wt = $this->db->prefixTable('works');

        return $this->select("{$wo}.*", false)
            ->select("{$wt}.title AS work_title, {$wt}.copyright_status", false)
            ->join($wt, "{$wt}.id = {$wo}.work_id", 'inner')
            ->where("{$wo}.owner_id", $ownerId)
            ->where("{$wt}.deleted_at", null)
            ->orderBy("{$wt}.updated_at", 'DESC')
            ->findAll();
    }
}
