<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetItemModel extends Model
{
    protected $table         = 'asset_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'asset_id',
        'item_code',
        'serial_number',
        'qr_token',
        'lab_id',
        'location_detail',
        'condition_status',
        'inventory_status',
        'is_loanable',
        'acquisition_date',
        'warranty_until',
        'notes',
        'responsible_user_id',
        'created_by',
        'updated_by',
    ];

    protected $beforeInsert = ['prepareIdentifiers'];

    protected function prepareIdentifiers(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        if (empty($data['data']['public_id'])) {
            $data['data']['public_id'] = $this->uuidV4();
        }

        if (empty($data['data']['qr_token'])) {
            $data['data']['qr_token'] = bin2hex(random_bytes(12));
        }

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