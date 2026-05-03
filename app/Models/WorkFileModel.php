<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class WorkFileModel extends Model
{
    protected $table            = 'work_files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'work_id',
        'original_filename',
        'stored_filename',
        'storage_path',
        'mime_type',
        'size_bytes',
        'sha256',
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
        'work_id'            => 'required|is_natural_no_zero',
        'original_filename'  => 'required|max_length[255]',
        'stored_filename'    => 'required|max_length[255]',
        'storage_path'       => 'required|max_length[512]',
        'mime_type'          => 'permit_empty|max_length[127]',
        'size_bytes'         => 'required|is_natural',
        'sha256'             => 'required|exact_length[64]|regex_match[/^[a-f0-9]{64}$/]',
        'uploaded_by'        => 'permit_empty|is_natural_no_zero',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function forWork(int $workId): array
    {
        return $this->where('work_id', $workId)->orderBy('id', 'ASC')->findAll();
    }
}
