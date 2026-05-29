<?php

namespace App\Models;

use CodeIgniter\Model;

class LabModel extends Model
{
    protected $table         = 'labs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'name',
        'code',
        'description',
        'location',
        'capacity',
        'logo',
        'qr_token',
        'is_active',
        'is_loanable',
        'condition_status',
    ];
}
