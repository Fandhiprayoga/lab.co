<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekApplicationDocumentModel extends Model
{
    protected $table         = 'oprek_application_documents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $createdField  = 'created_at';

    protected $allowedFields = [
        'application_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_verified',
        'verification_note',
        'verified_by',
        'verified_at',
    ];

    public function getByApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function getByApplicationAndType(int $applicationId, string $type): ?object
    {
        return $this->where('application_id', $applicationId)
            ->where('document_type', $type)
            ->first();
    }

    public function countUnverified(int $applicationId): int
    {
        return $this->where('application_id', $applicationId)
            ->where('is_verified', 0)
            ->whereIn('document_type', ['cv', 'ktm', 'khs', 'commitment_letter'])
            ->countAllResults();
    }
}
