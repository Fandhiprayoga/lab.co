<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    /**
     * Halaman full list notifikasi (shell — data dimuat via AJAX).
     * GET /notifications
     */
    public function index(): string
    {
        return $this->renderView('notifications/index', [
            'title'      => 'Notifikasi',
            'page_title' => 'Notifikasi',
        ]);
    }

    /**
     * AJAX endpoint untuk daftar notifikasi dengan pagination & filter.
     * GET /notifications/list?page=1&filter=all|unread
     * Response: { items, total, page, per_page, total_pages }
     */
    public function listAjax(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId     = (int) auth()->id();
        $page       = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage    = 20;
        $unreadOnly = $this->request->getGet('filter') === 'unread';

        $result = $this->notifModel->getPaginatedAjax($userId, $page, $perPage, $unreadOnly);

        $items = array_map(function (array $n): array {
            $data = json_decode($n['data'] ?? '{}', true) ?: [];

            return [
                'id'         => $n['id'],
                'type'       => $n['type'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'icon'       => $data['icon']  ?? 'fas fa-bell',
                'color'      => $data['color'] ?? 'secondary',
                'url'        => $data['url']   ?? null,
                'created_at' => $n['created_at'],
                'is_read'    => $n['read_at'] !== null,
            ];
        }, $result['items']);

        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / $perPage) : 1;

        return $this->response->setJSON([
            'items'       => $items,
            'total'       => $result['total'],
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ]);
    }

    /**
     * Jumlah notifikasi belum dibaca untuk polling badge.
     * GET /notifications/unread-count
     * Response: { "count": N }
     */
    public function unreadCount(): \CodeIgniter\HTTP\ResponseInterface
    {
        $count = $this->notifModel->getUnreadCount((int) auth()->id());

        return $this->response->setJSON(['count' => $count]);
    }

    /**
     * 10 notifikasi terbaru untuk dropdown navbar.
     * GET /notifications/recent
     */
    public function recent(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) auth()->id();
        $items  = $this->notifModel->getRecent($userId, 10);

        $result = array_map(function (array $n): array {
            $data = json_decode($n['data'] ?? '{}', true) ?: [];

            return [
                'id'         => $n['id'],
                'type'       => $n['type'],
                'title'      => $n['title'],
                'message'    => $n['message'],
                'icon'       => $data['icon']  ?? 'fas fa-bell',
                'color'      => $data['color'] ?? 'secondary',
                'url'        => $data['url']   ?? null,
                'read_at'    => $n['read_at'],
                'created_at' => $n['created_at'],
                'is_read'    => $n['read_at'] !== null,
            ];
        }, $items);

        return $this->response->setJSON($result);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     * POST /notifications/:id/read
     */
    public function markRead(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) auth()->id();
        $this->notifModel->markRead($id, $userId);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     * POST /notifications/read-all
     */
    public function markAllRead(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) auth()->id();
        $this->notifModel->markAllRead($userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to(base_url('notifications'))->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Hapus satu notifikasi.
     * DELETE /notifications/:id
     */
    public function destroy(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) auth()->id();
        $this->notifModel->deleteOwned($id, $userId);

        return $this->response->setJSON(['success' => true]);
    }
}
