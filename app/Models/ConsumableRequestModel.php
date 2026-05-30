<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsumableRequestModel extends Model
{
    protected $table         = 'consumable_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'request_code',
        'requester_id',
        'lab_id',
        'purpose',
        'scheduled_date',
        'status',
        'submitted_at',
        'approval_by',
        'approval_at',
        'approval_note',
        'disbursed_by',
        'disbursed_at',
        'realized_by',
        'realized_at',
        'canceled_reason',
    ];

    protected $validationRules = [
        'purpose' => 'required',
        'lab_id'  => 'required|is_natural_no_zero',
    ];

    /**
     * Status constants.
     */
    public const STATUS_DRAFT            = 'draft';
    public const STATUS_WAITING_APPROVAL = 'waiting_approval';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_DISBURSED        = 'disbursed';
    public const STATUS_COMPLETED        = 'completed';
    public const STATUS_CANCELED         = 'canceled';
    public const STATUS_PROBLEMATIC      = 'problematic';

    /**
     * Daftar permintaan beserta nama pemohon dan lab (dengan join).
     */
    public function getList(bool $ownOnly = false, ?int $userId = null): array
    {
        $builder = db_connect()->table('consumable_requests r')
            ->select('r.*, u.username AS requester_name, l.name AS lab_name')
            ->join('users u', 'u.id = r.requester_id', 'left')
            ->join('labs l', 'l.id = r.lab_id', 'left')
            ->orderBy('r.created_at', 'DESC');

        if ($ownOnly && $userId !== null) {
            $builder->where('r.requester_id', $userId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Detail satu permintaan dengan join ke users & labs.
     */
    public function getDetail(int $id): ?array
    {
        return db_connect()->table('consumable_requests r')
            ->select('r.*, u.username AS requester_name, l.name AS lab_name, approver.username AS approver_name, disburser.username AS disburser_name, realizer.username AS realizer_name')
            ->join('users u', 'u.id = r.requester_id', 'left')
            ->join('labs l', 'l.id = r.lab_id', 'left')
            ->join('users approver', 'approver.id = r.approval_by', 'left')
            ->join('users disburser', 'disburser.id = r.disbursed_by', 'left')
            ->join('users realizer', 'realizer.id = r.realized_by', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();
    }
}
