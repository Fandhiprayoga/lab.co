<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanProposalItemModel extends Model
{
    protected $table         = 'loan_proposal_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'proposal_id',
        'item_type',
        'equipment_id',
        'lab_id',
        'qty',
        'note',
    ];
}
