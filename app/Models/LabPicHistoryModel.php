<?php

namespace App\Models;

use CodeIgniter\Model;

class LabPicHistoryModel extends Model
{
    protected $table         = 'lab_pic_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields  = ['lab_id', 'user_id', 'action', 'actor_id', 'note', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Ambil riwayat penetapan PIC untuk satu lab beserta username pelaku & target.
     */
    public function recentByLab(int $labId, int $limit = 50): array
    {
        return $this->db->table('lab_pic_history h')
            ->select('h.action, h.note, h.created_at, tu.username AS target_username, au.username AS actor_username')
            ->join('users tu', 'tu.id = h.user_id', 'left')
            ->join('users au', 'au.id = h.actor_id', 'left')
            ->where('h.lab_id', $labId)
            ->orderBy('h.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
