<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsumableStockAdjustmentModel extends Model
{
    protected $table         = 'consumable_stock_adjustments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'consumable_id',
        'adjustment_type',
        'qty',
        'reason',
        'adjusted_by',
        'adjusted_at',
        'reference_request_id',
    ];

    /**
     * Riwayat penyesuaian untuk satu bahan habis pakai.
     */
    public function getByItem(int $consumableId): array
    {
        return $this->select('consumable_stock_adjustments.*, u.username AS adjusted_by_name')
            ->join('users u', 'u.id = consumable_stock_adjustments.adjusted_by', 'left')
            ->where('consumable_stock_adjustments.consumable_id', $consumableId)
            ->orderBy('consumable_stock_adjustments.adjusted_at', 'DESC')
            ->findAll();
    }
}
