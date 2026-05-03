<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CaseStatusLogModel extends Model
{
    protected $table            = 'infringement_case_status_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'infringement_case_id',
        'from_status',
        'to_status',
        'transition_note',
        'changed_by',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = false;

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'infringement_case_id' => 'required|is_natural_no_zero',
        'from_status'          => 'permit_empty|max_length[64]',
        'to_status'            => 'required|in_list[detected,under_review,notice_sent,negotiation,resolved,rejected]',
        'transition_note'      => 'permit_empty|max_length[4000]',
        'changed_by'           => 'permit_empty|integer',
        'created_at'           => 'permit_empty|max_length[32]',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function forCase(int $caseId): array
    {
        if ($caseId < 1 || ! self::tableReady()) {
            return [];
        }

        $t = $this->db->prefixTable('infringement_case_status_logs');
        $u = $this->db->prefixTable('users');

        return $this->builder()
            ->select("{$t}.*", false)
            ->select('u.display_name AS actor_name', false)
            ->join("{$u} u", "u.id = {$t}.changed_by", 'left')
            ->where("{$t}.infringement_case_id", $caseId)
            ->orderBy("{$t}.created_at", 'ASC')
            ->orderBy("{$t}.id", 'ASC')
            ->get()
            ->getResultArray();
    }

    public static function tableReady(): bool
    {
        return db_connect()->tableExists('infringement_case_status_logs');
    }
}
