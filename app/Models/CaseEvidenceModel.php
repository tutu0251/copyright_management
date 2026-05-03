<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CaseEvidenceModel extends Model
{
    protected $table            = 'infringement_case_evidence';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'infringement_case_id',
        'stored_path',
        'original_name',
        'mime_type',
        'uploaded_by',
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
        'stored_path'          => 'required|max_length[512]',
        'original_name'        => 'permit_empty|max_length[255]',
        'mime_type'            => 'permit_empty|max_length[191]',
        'uploaded_by'          => 'permit_empty|integer',
        'created_at'           => 'permit_empty|max_length[32]',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function forCase(int $caseId): array
    {
        if ($caseId < 1 || ! InfringementCaseModel::schemaReady()) {
            return [];
        }

        return $this->where('infringement_case_id', $caseId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public static function tableReady(): bool
    {
        return db_connect()->tableExists('infringement_case_evidence');
    }
}
