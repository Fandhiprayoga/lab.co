<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLabAssignmentModel extends Model
{
    protected $table         = 'user_lab_assignments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id',
        'lab_id',
        'assigned_at',
    ];

    public function getLabsByUser(int $userId): array
    {
        return $this->select('labs.id, labs.name, labs.code')
            ->join('labs', 'labs.id = user_lab_assignments.lab_id')
            ->where('user_lab_assignments.user_id', $userId)
            ->where('labs.deleted_at', null)
            ->orderBy('labs.name', 'ASC')
            ->findAll();
    }

    public function getLabIdsByUser(int $userId): array
    {
        $rows = $this->select('lab_id')
            ->where('user_id', $userId)
            ->findAll();

        return array_map(fn($r) => (int) $r->lab_id, $rows);
    }

    public function deleteByUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }

    public function assignLabs(int $userId, array $labIds): void
    {
        $this->deleteByUser($userId);

        if (empty($labIds)) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $data = [];
        foreach ($labIds as $labId) {
            $data[] = [
                'user_id'     => $userId,
                'lab_id'      => (int) $labId,
                'assigned_at' => $now,
            ];
        }

        $this->insertBatch($data);
    }
}
