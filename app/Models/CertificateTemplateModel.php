<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateTemplateModel extends Model
{
    protected $table         = 'certificate_templates';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'name',
        'description',
        'background_path',
        'page_orientation',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $beforeInsert = ['generatePublicId'];

    protected function generatePublicId(array $data): array
    {
        $data['data']['public_id'] = bin2hex(random_bytes(16));
        return $data;
    }

    public function findByPublicId(string $publicId): ?object
    {
        return $this->where('public_id', $publicId)->first();
    }

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'asc')->findAll();
    }
}
