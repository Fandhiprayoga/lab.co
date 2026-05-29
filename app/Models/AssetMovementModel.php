<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetMovementModel extends Model
{
    protected $table         = 'asset_movements';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'asset_id',
        'movement_type',
        'quantity',
        'from_lab_id',
        'to_lab_id',
        'reference_type',
        'reference_id',
        'movement_date',
        'notes',
        'created_by',
        'created_at',
    ];
}
