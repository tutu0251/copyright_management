<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table           = 'roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields   = [
        'name',
        'slug',
        'description',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'slug' => 'required|max_length[50]|alpha_dash',
    ];

    public function findIdBySlug(string $slug): ?int
    {
        $row = $this->select('id')->where('slug', $slug)->first();

        return $row !== null ? (int) $row['id'] : null;
    }
}
