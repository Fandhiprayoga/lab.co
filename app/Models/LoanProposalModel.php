<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanProposalModel extends Model
{
    protected $table         = 'loan_proposals';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'proposal_code',
        'proposer_id',
        'loan_type',
        'title',
        'objective',
        'start_at',
        'end_at',
        'requires_l2',
        'status',
        'submitted_at',
        'approval_l1_by',
        'approval_l1_note',
        'approval_l1_at',
        'approval_l2_by',
        'approval_l2_note',
        'approval_l2_at',
        'rejected_reason',
        'cancel_reason',
        'canceled_by',
        'canceled_at',
    ];
}
