<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'type', 'title', 'message', 'data', 'read_at'];

    /**
     * Jumlah notifikasi yang belum dibaca untuk user tertentu.
     */
    public function getUnreadCount(int $userId): int
    {
        return (int) $this->where('user_id', $userId)
                          ->where('read_at IS NULL')
                          ->countAllResults();
    }

    /**
     * Ambil notifikasi terbaru untuk dropdown navbar.
     */
    public function getRecent(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca (validasi ownership).
     */
    public function markRead(int $id, int $userId): bool
    {
        return $this->where('id', $id)
                    ->where('user_id', $userId)
                    ->where('read_at IS NULL')
                    ->set(['read_at' => date('Y-m-d H:i:s')])
                    ->update();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public function markAllRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
                    ->where('read_at IS NULL')
                    ->set(['read_at' => date('Y-m-d H:i:s')])
                    ->update();
    }

    /**
     * Hapus notifikasi terlama jika melebihi batas maksimum per user.
     * Dipanggil setelah insert baru agar tabel tidak membengkak.
     */
    public function trimOld(int $userId, int $max = 100): void
    {
        $count = (int) $this->where('user_id', $userId)->countAllResults();

        if ($count <= $max) {
            return;
        }

        // Ambil ID notifikasi terlama yang harus dihapus
        $toDelete = $this->select('id')
                         ->where('user_id', $userId)
                         ->orderBy('created_at', 'ASC')
                         ->limit($count - $max)
                         ->findAll();

        if (! empty($toDelete)) {
            $ids = array_column($toDelete, 'id');
            $this->whereIn('id', $ids)->delete();
        }
    }

    /**
     * Hapus satu notifikasi (validasi ownership).
     */
    public function deleteOwned(int $id, int $userId): bool
    {
        return (bool) $this->where('id', $id)
                           ->where('user_id', $userId)
                           ->delete();
    }

    /**
     * Ambil semua notifikasi untuk halaman full list (paginated).
     */
    public function getPaginated(int $userId, int $perPage = 20): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    /**
     * Ambil notifikasi untuk AJAX endpoint (offset-based, opsional filter unread).
     * Return: ['items' => array, 'total' => int]
     */
    public function getPaginatedAjax(int $userId, int $page, int $perPage, bool $unreadOnly = false): array
    {
        $offset = ($page - 1) * $perPage;

        // Count query (builder terpisah agar tidak terkontaminasi state)
        $countBuilder = $this->db->table($this->table)->where('user_id', $userId);
        if ($unreadOnly) {
            $countBuilder->where('read_at IS NULL');
        }
        $total = (int) $countBuilder->countAllResults();

        // Data query
        $builder = $this->where('user_id', $userId);
        if ($unreadOnly) {
            $builder = $builder->where('read_at IS NULL');
        }
        $items = $builder->orderBy('created_at', 'DESC')
                         ->limit($perPage, $offset)
                         ->findAll();

        return ['items' => $items, 'total' => $total];
    }
}
