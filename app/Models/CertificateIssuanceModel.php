<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateIssuanceModel extends Model
{
    protected $table         = 'certificate_issuances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'cert_code',
        'template_id',
        'recipient_user_id',
        'recipient_name',
        'recipient_role',
        'issued_by',
        'issued_at',
        'notes',
        'is_revoked',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
    ];

    protected $beforeInsert = ['generateIdentifiers'];

    protected function generateIdentifiers(array $data): array
    {
        $data['data']['public_id'] = bin2hex(random_bytes(16));
        $data['data']['cert_code'] = 'CERT-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $data;
    }

    public function findByPublicId(string $publicId): ?object
    {
        return $this->where('public_id', $publicId)->first();
    }

    public function findByCertCode(string $certCode): ?object
    {
        return $this->select('certificate_issuances.*, ct.name as template_name, ct.background_path, ct.page_orientation')
            ->join('certificate_templates ct', 'ct.id = certificate_issuances.template_id')
            ->where('cert_code', $certCode)
            ->first();
    }

    public function getByRecipient(int $userId): array
    {
        return $this->select('certificate_issuances.*, ct.name as template_name')
            ->join('certificate_templates ct', 'ct.id = certificate_issuances.template_id')
            ->where('recipient_user_id', $userId)
            ->orderBy('issued_at', 'desc')
            ->findAll();
    }
}
