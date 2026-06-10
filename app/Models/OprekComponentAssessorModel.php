<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekComponentAssessorModel extends Model
{
    protected $table         = 'oprek_component_assessors';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $createdField  = 'created_at';

    protected $allowedFields = [
        'component_id',
        'assessor_user_id',
        'assessor_role',
    ];

    public function getByComponent(int $componentId): array
    {
        return $this->select('oprek_component_assessors.*, u.username')
            ->join('users u', 'u.id = oprek_component_assessors.assessor_user_id')
            ->where('component_id', $componentId)
            ->findAll();
    }

    public function getByCampaign(int $campaignId): array
    {
        return $this->select('oprek_component_assessors.*, osc.component_name, osc.component_key, u.username')
            ->join('oprek_selection_components osc', 'osc.id = oprek_component_assessors.component_id')
            ->join('users u', 'u.id = oprek_component_assessors.assessor_user_id')
            ->where('osc.campaign_id', $campaignId)
            ->orderBy('osc.sort_order', 'ASC')
            ->findAll();
    }

    public function setAssessors(int $componentId, array $assessors): void
    {
        // Delete existing
        $this->where('component_id', $componentId)->delete();

        if (empty($assessors)) {
            return;
        }

        $data = [];
        foreach ($assessors as $a) {
            $data[] = [
                'component_id'     => $componentId,
                'assessor_user_id' => $a['user_id'],
                'assessor_role'    => $a['role'],
            ];
        }
        $this->insertBatch($data);
    }
}
