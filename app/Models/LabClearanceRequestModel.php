<?php

namespace App\Models;

use CodeIgniter\Model;

class LabClearanceRequestModel extends Model
{
    protected $table         = 'lab_clearance_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'request_code',
        'public_id',
        'requester_id',
        'lab_id',
        'purpose',
        'applicant_name',
        'nim_nik',
        'phone',
        'email',
        'prodi',
        'address',
        'thesis_title',
        'note',
        'status',
        'submitted_at',
        'outstanding_snapshot',
        'verified_by',
        'verified_note',
        'verified_at',
        'rejected_reason',
        'cancel_reason',
        'canceled_by',
        'canceled_at',
        'letter_number',
        'letter_file_path',
        'letter_external_url',
        'letter_issued_at',
    ];

    protected $beforeInsert = ['generatePublicId'];

    /**
     * Auto-generate a non-sequential public_id (UUID v4) before insert
     * so URLs never expose the enumerable primary key (mitigates IDOR).
     */
    protected function generatePublicId(array $data): array
    {
        if (empty($data['data']['public_id'])) {
            $data['data']['public_id'] = $this->uuidV4();
        }

        return $data;
    }

    private function uuidV4(): string
    {
        $bytes    = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public function findByPublicId(string $publicId): ?array
    {
        return $this->where('public_id', $publicId)->first();
    }
}
