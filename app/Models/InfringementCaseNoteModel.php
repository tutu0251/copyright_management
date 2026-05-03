<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class InfringementCaseNoteModel extends Model
{
    protected $table            = 'infringement_case_notes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'infringement_case_id',
        'user_id',
        'body',
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
        'user_id'              => 'permit_empty|integer',
        'body'                 => 'required|max_length[16000]',
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

        $n = $this->db->prefixTable('infringement_case_notes');
        $u = $this->db->prefixTable('users');

        return $this->builder()
            ->select("{$n}.*", false)
            ->select('u.display_name AS author_name', false)
            ->join("{$u} u", "u.id = {$n}.user_id", 'left')
            ->where("{$n}.infringement_case_id", $caseId)
            ->orderBy("{$n}.created_at", 'ASC')
            ->orderBy("{$n}.id", 'ASC')
            ->get()
            ->getResultArray();
    }

    public static function tableReady(): bool
    {
        return db_connect()->tableExists('infringement_case_notes');
    }
}
