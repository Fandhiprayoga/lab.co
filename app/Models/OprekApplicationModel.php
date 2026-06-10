<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekApplicationModel extends Model
{
    protected $table         = 'oprek_applications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'campaign_id',
        'student_id',
        'form_payload',
        'application_status',
        'submitted_at',
    ];

    protected $beforeInsert = ['generatePublicId'];

    protected function generatePublicId(array $data): array
    {
        $data['data']['public_id'] = bin2hex(random_bytes(16));
        return $data;
    }

    public function findByPublicId(string $publicId): ?object
    {
        return $this->select('oprek_applications.*, up.nim_nik, up.phone, up.prodi, u.username')
            ->join('users u', 'u.id = oprek_applications.student_id')
            ->join('user_profiles up', 'up.user_id = oprek_applications.student_id', 'left')
            ->where('oprek_applications.public_id', $publicId)
            ->first();
    }

    protected $validationRules = [
        'campaign_id'        => 'required|integer',
        'student_id'         => 'required|integer',
        'application_status' => 'permit_empty|in_list[submitted,doc_revision,doc_rejected,doc_verified,in_selection,failed,accepted,onboarding_pending,onboarding_complete]',
    ];

    public function getByCampaign(int $campaignId): array
    {
        return $this->select('oprek_applications.*, up.nim_nik, u.username')
            ->join('users u', 'u.id = oprek_applications.student_id')
            ->join('user_profiles up', 'up.user_id = oprek_applications.student_id', 'left')
            ->where('oprek_applications.campaign_id', $campaignId)
            ->orderBy('oprek_applications.submitted_at', 'ASC')
            ->findAll();
    }

    public function getByStudent(int $studentId): array
    {
        return $this->select('oprek_applications.*, oc.period_name, labs.name as lab_name')
            ->join('oprek_campaigns oc', 'oc.id = oprek_applications.campaign_id')
            ->join('labs', 'labs.id = oc.lab_id')
            ->where('oprek_applications.student_id', $studentId)
            ->orderBy('oprek_applications.created_at', 'DESC')
            ->findAll();
    }

    public function getWithDetails(int $id): ?object
    {
        return $this->select('oprek_applications.*, up.nim_nik, up.phone, up.prodi, u.username')
            ->join('users u', 'u.id = oprek_applications.student_id')
            ->join('user_profiles up', 'up.user_id = oprek_applications.student_id', 'left')
            ->where('oprek_applications.id', $id)
            ->first();
    }

    public function countVerifiedByCampaign(int $campaignId): int
    {
        return $this->where('campaign_id', $campaignId)
            ->whereIn('application_status', ['doc_verified', 'in_selection', 'accepted', 'onboarding_pending', 'onboarding_complete'])
            ->countAllResults();
    }
}
