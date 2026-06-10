<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekOnboardingProfileModel extends Model
{
    protected $table         = 'oprek_onboarding_profiles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'application_id',
        'bank_account_number',
        'bank_name',
        'bank_account_name',
        'signature_document_id',
        'passbook_document_id',
        'onboarding_status',
        'verified_by',
        'verified_at',
    ];

    public function getByApplication(int $applicationId): ?object
    {
        return $this->where('application_id', $applicationId)->first();
    }

    public function getByCampaign(int $campaignId): array
    {
        return $this->select('oprek_onboarding_profiles.*, oa.student_id')
            ->join('oprek_applications oa', 'oa.id = oprek_onboarding_profiles.application_id')
            ->where('oa.campaign_id', $campaignId)
            ->findAll();
    }
}
