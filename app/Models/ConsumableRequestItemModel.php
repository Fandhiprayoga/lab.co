<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsumableRequestItemModel extends Model
{
    protected $table         = 'consumable_request_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'request_id',
        'consumable_id',
        'qty_requested',
        'qty_approved',
        'qty_actual',
        'notes',
    ];

    /**
     * Item untuk satu permintaan, beserta detail bahan dan satuan.
     */
    public function getByRequest(int $requestId): array
    {
        return $this->select('consumable_request_items.*, consumable_items.name AS item_name, units.symbol AS unit_symbol')
            ->join('consumable_items', 'consumable_items.id = consumable_request_items.consumable_id', 'left')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->where('consumable_request_items.request_id', $requestId)
            ->findAll();
    }
}
