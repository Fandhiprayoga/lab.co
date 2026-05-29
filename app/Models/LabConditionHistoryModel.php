<?php

namespace App\Models;

use CodeIgniter\Model;

class LabConditionHistoryModel extends Model
{
    protected $table         = 'lab_condition_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $updatedField  = '';

    protected $allowedFields = [
        'lab_id',
        'previous_condition',
        'new_condition',
        'reason',
        'changed_by',
    ];
}
