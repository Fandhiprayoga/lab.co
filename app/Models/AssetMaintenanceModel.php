<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetMaintenanceModel extends Model
{
    protected $table         = 'asset_maintenances';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'asset_id',
        'maintenance_type',
        'scheduled_date',
        'performed_date',
        'status',
        'performed_by',
        'cost',
        'description',
        'result_notes',
        'next_maintenance_date',
        'created_by',
    ];
}
