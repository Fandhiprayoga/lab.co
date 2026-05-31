<?php

namespace App\Models;

use CodeIgniter\Model;

class KepalaLabHistoryModel extends Model
{
    protected $table         = 'kepala_lab_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields  = ['user_id', 'action', 'actor_id', 'note', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Ambil riwayat perubahan Kepala Lab terbaru beserta username pelaku & target.
     */
    public function recentWithNames(int $limit = 20): array
    {
        return $this->db->table('kepala_lab_history h')
            ->select('h.action, h.note, h.created_at, tu.username AS target_username, au.username AS actor_username')
            ->join('users tu', 'tu.id = h.user_id', 'left')
            ->join('users au', 'au.id = h.actor_id', 'left')
            ->orderBy('h.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
