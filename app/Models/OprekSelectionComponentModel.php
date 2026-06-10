<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekSelectionComponentModel extends Model
{
    protected $table         = 'oprek_selection_components';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'campaign_id',
        'component_name',
        'component_key',
        'is_required',
        'is_active',
        'weight_percentage',
        'max_score',
        'sort_order',
    ];

    protected $validationRules = [
        'campaign_id'       => 'required|integer',
        'component_name'    => 'required|max_length[100]',
        'component_key'     => 'required|max_length[50]',
        'weight_percentage' => 'permit_empty|decimal',
        'max_score'         => 'permit_empty|decimal',
    ];

    public function getByCampaign(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getActiveByCampaign(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function getTotalWeight(int $campaignId): float
    {
        $result = $this->selectSum('weight_percentage')
            ->where('campaign_id', $campaignId)
            ->where('is_active', 1)
            ->first();

        return (float) ($result->weight_percentage ?? 0);
    }
}
