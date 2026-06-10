<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekActivityLogModel extends Model
{
    protected $table         = 'oprek_activity_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $createdField  = 'created_at';

    protected $allowedFields = [
        'campaign_id',
        'application_id',
        'actor_user_id',
        'action_type',
        'action_payload',
    ];

    public function log(int $actorId, string $actionType, ?int $campaignId = null, ?int $applicationId = null, array $payload = []): void
    {
        $this->insert([
            'campaign_id'     => $campaignId,
            'application_id'  => $applicationId,
            'actor_user_id'   => $actorId,
            'action_type'     => $actionType,
            'action_payload'  => ! empty($payload) ? json_encode($payload) : null,
        ]);
    }

    public function getByCampaign(int $campaignId, int $limit = 50): array
    {
        return $this->select('oprek_activity_logs.*, u.username')
            ->join('users u', 'u.id = oprek_activity_logs.actor_user_id')
            ->where('oprek_activity_logs.campaign_id', $campaignId)
            ->orderBy('oprek_activity_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getByApplication(int $applicationId): array
    {
        return $this->select('oprek_activity_logs.*, u.username')
            ->join('users u', 'u.id = oprek_activity_logs.actor_user_id')
            ->where('oprek_activity_logs.application_id', $applicationId)
            ->orderBy('oprek_activity_logs.created_at', 'ASC')
            ->findAll();
    }
}
