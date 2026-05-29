<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanRequestModel extends Model
{
    protected $table         = 'loan_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'request_code',
        'requester_id',
        'asset_id',
        'qty',
        'purpose',
        'supporting_document',
        'pickup_at',
        'return_at',
        'status',
        'requires_l2',
        'approval_l1_by',
        'approval_l1_note',
        'approval_l1_at',
        'approval_l2_by',
        'approval_l2_note',
        'approval_l2_at',
        'checkout_by',
        'checkout_condition',
        'checkout_at',
        'checkin_by',
        'checkin_condition',
        'checkin_at',
        'is_late',
        'issue_flag',
        'issue_note',
        'rejected_reason',
        'cancel_reason',
        'canceled_by',
        'canceled_at',
        'auto_canceled',
    ];
}
