<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LicenseeModel extends Model
{
    public const TYPE_INDIVIDUAL    = 'individual';
    public const TYPE_COMPANY       = 'company';
    public const TYPE_ORGANIZATION  = 'organization';

    /**
     * @var list<string>
     */
    public const LICENSEE_TYPES = [
        self::TYPE_INDIVIDUAL,
        self::TYPE_COMPANY,
        self::TYPE_ORGANIZATION,
    ];

    protected $table            = 'licensees';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'licensee_type',
        'email',
        'phone',
        'address',
        'country',
        'notes',
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
        'name'           => 'required|min_length[2]|max_length[255]',
        'licensee_type'  => 'required|in_list[individual,company,organization]',
        'email'          => 'permit_empty|valid_email|max_length[191]',
        'phone'          => 'permit_empty|max_length[64]',
        'address'        => 'permit_empty|max_length[2000]',
        'country'        => 'permit_empty|max_length[100]',
        'notes'          => 'permit_empty|max_length[16000]',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listForSelect(): array
    {
        return $this->select('id, name, licensee_type, email')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_COMPANY      => 'Company',
            self::TYPE_ORGANIZATION => 'Organization',
            default                 => 'Individual',
        };
    }
}
