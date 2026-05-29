<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanRequestLogModel extends Model
{
    protected $table         = 'loan_request_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'loan_request_id',
        'action',
        'note',
        'actor_id',
        'created_at',
    ];
}
