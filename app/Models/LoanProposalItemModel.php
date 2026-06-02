<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanProposalItemModel extends Model
{
    protected $table         = 'loan_proposal_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'proposal_id',
        'item_type',
        'equipment_id',
        'lab_id',
        'qty',
        'qty_returned_good',
        'qty_returned_damaged',
        'qty_returned_lost',
        'returned_by_user_id',
        'return_condition',
        'return_note',
        'maintenance_record_id',
        'returned_at',
        'note',
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
