<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekFinalDecisionModel extends Model
{
    protected $table         = 'oprek_final_decisions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $createdField  = 'created_at';

    protected $allowedFields = [
        'application_id',
        'decision_status',
        'final_score',
        'decided_by',
        'decision_note',
        'decided_at',
    ];

    protected $validationRules = [
        'application_id'  => 'required|integer|is_unique[oprek_final_decisions.application_id,id,{id}]',
        'decision_status' => 'required|in_list[accepted,rejected,waitlist]',
        'decided_by'      => 'required|integer',
    ];

    public function getByApplication(int $applicationId): ?object
    {
        return $this->where('application_id', $applicationId)->first();
    }

    public function getByCampaign(int $campaignId): array
    {
        return $this->select('oprek_final_decisions.*, oa.student_id')
            ->join('oprek_applications oa', 'oa.id = oprek_final_decisions.application_id')
            ->where('oa.campaign_id', $campaignId)
            ->findAll();
    }

    public function countAcceptedByCampaign(int $campaignId): int
    {
        return $this->select('oprek_final_decisions.id')
            ->join('oprek_applications oa', 'oa.id = oprek_final_decisions.application_id')
            ->where('oa.campaign_id', $campaignId)
            ->where('oprek_final_decisions.decision_status', 'accepted')
            ->countAllResults();
    }
}
