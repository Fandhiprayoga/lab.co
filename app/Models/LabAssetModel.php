<?php

namespace App\Models;

use CodeIgniter\Model;

class LabAssetModel extends Model
{
    protected $table         = 'lab_assets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'lab_id',
        'asset_type',
        'category',
        'location',
        'specifications',
        'photo',
        'max_loan_hours',
        'stock_total',
        'stock_available',
        'is_active',
        'is_loanable',
        'condition_status',
        'created_by',
        // Inventory fields (Phase 2)
        'asset_code',
        'serial_number',
        'brand',
        'model',
        'unit_id',
        'acquisition_date',
        'acquisition_source',
        'purchase_price',
        'supplier',
        'funding_source',
        'warranty_until',
        'inventory_status',
        'responsible_user_id',
        'minimum_stock',
        'notes',
        'updated_by',
    ];
}
