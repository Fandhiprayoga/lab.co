<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanProposalModel extends Model
{
    protected $table         = 'loan_proposals';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'proposal_code',
        'proposer_id',
        'loan_type',
        'title',
        'objective',
        'start_at',
        'end_at',
        'requires_l2',
        'status',
        'submitted_at',
        'approval_l1_by',
        'approval_l1_note',
        'approval_l1_at',
        'approval_l2_by',
        'approval_l2_note',
        'approval_l2_at',
        'checkout_by',
        'checkout_condition',
        'checkout_at',
        'checkin_by',
        'checkin_condition',
        'checkin_at',
        'started_use_by',
        'started_use_at',
        'finished_use_by',
        'finished_use_at',
        'is_late',
        'issue_flag',
        'issue_note',
        'lab_terms_checks',
        'equipment_terms_checks',
        'rejected_reason',
        'cancel_reason',
        'canceled_by',
        'canceled_at',
    ];

    protected $beforeInsert = ['generatePublicId'];

    protected function generatePublicId(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        if (! empty($data['data']['public_id'])) {
            return $data;
        }

        $data['data']['public_id'] = $this->uuidV4();

        return $data;
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
