<?php

namespace App\Controllers;

use App\Models\LabModel;
use App\Models\LabPicHistoryModel;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Penetapan PIC Laboran penanggung jawab per lab.
 *
 * Setiap lab dapat memiliki SATU PIC Laboran (kolom labs.pic_user_id).
 * Hanya user aktif ber-role `laboran` yang menjadi kandidat. Akses dibatasi
 * via permission `lending.master.labs.assign-pic` (kepala_lab & superadmin).
 * Setiap perubahan dicatat di tabel lab_pic_history.
 */
class LabPicController extends BaseController
{
    protected LabModel $labModel;
    protected LabPicHistoryModel $historyModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->labModel    = new LabModel();
        $this->historyModel = new LabPicHistoryModel();
        $this->userModel   = new UserModel();
    }

    /**
     * Daftar lab beserta PIC saat ini.
     */
    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->labModel->orderBy('name', 'ASC')->findAll();

        // Map PIC username per lab.
        $picMap = [];
        $picIds = array_filter(array_column($labs, 'pic_user_id'));
        if (! empty($picIds)) {
            $rows = db_connect()->table('users')
                ->select('id, username')
                ->whereIn('id', array_unique($picIds))
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $picMap[(int) $r['id']] = $r['username'];
            }
        }
        foreach ($labs as &$lab) {
            $lab['pic_username'] = isset($lab['pic_user_id']) ? ($picMap[(int) $lab['pic_user_id']] ?? null) : null;
        }
        unset($lab);

        return $this->renderView('admin/lab_pic/index', [
            'title'      => 'PIC Laboran',
            'page_title' => 'Penetapan PIC Laboran per Lab',
            'labs'       => $labs,
            'candidates' => $this->getLaboranCandidates(),
        ]);
    }

    /**
     * Detail + riwayat PIC untuk satu lab.
     */
    public function show(int $labId)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($labId);
        if (! $lab) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Data lab tidak ditemukan.');
        }

        $pic = null;
        if (! empty($lab['pic_user_id'])) {
            $pic = $this->userModel->findById((int) $lab['pic_user_id']);
        }

        return $this->renderView('admin/lab_pic/show', [
            'title'      => 'Riwayat PIC Lab',
            'page_title' => 'Riwayat PIC — ' . $lab['name'],
            'lab'        => $lab,
            'pic'        => $pic,
            'candidates' => $this->getLaboranCandidates(),
            'history'    => $this->historyModel->recentByLab($labId, 100),
        ]);
    }

    /**
     * Tetapkan PIC Laboran untuk sebuah lab.
     */
    public function assign(int $labId)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($labId);
        if (! $lab) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Data lab tidak ditemukan.');
        }

        $userId = (int) $this->request->getPost('user_id');
        if ($userId <= 0) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Pilih laboran yang akan ditetapkan sebagai PIC.');
        }

        $user = $this->userModel->findById($userId);
        if (! $user) {
            return redirect()->to('/admin/lab-pic')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->active) {
            return redirect()->to('/admin/lab-pic')->with('error', 'User tidak aktif tidak dapat ditetapkan sebagai PIC.');
        }

        if (! $user->inGroup('laboran')) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Hanya user dengan role Laboran yang dapat ditetapkan sebagai PIC.');
        }

        $currentPicId = (int) ($lab['pic_user_id'] ?? 0);
        if ($currentPicId === $userId) {
            return redirect()->to('/admin/lab-pic')->with('info', esc($user->username) . ' sudah menjadi PIC lab ini.');
        }

        $actorId = (int) auth()->id();
        $now     = date('Y-m-d H:i:s');

        $db = db_connect();
        $db->transStart();

        // Cabut PIC lama (jika ada) dan catat di riwayat.
        if ($currentPicId > 0) {
            $this->historyModel->insert([
                'lab_id'     => $labId,
                'user_id'    => $currentPicId,
                'action'     => 'revoked',
                'actor_id'   => $actorId,
                'note'       => 'Dicabut karena PIC baru ditetapkan.',
                'created_at' => $now,
            ]);
        }

        $this->labModel->update($labId, ['pic_user_id' => $userId]);

        $this->historyModel->insert([
            'lab_id'     => $labId,
            'user_id'    => $userId,
            'action'     => 'assigned',
            'actor_id'   => $actorId,
            'note'       => 'Ditetapkan sebagai PIC Laboran.',
            'created_at' => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Gagal menetapkan PIC. Silakan coba lagi.');
        }

        if (function_exists('send_notification')) {
            send_notification($user->id, 'lab.pic_assigned', [
                'lab_name' => $lab['name'],
                'url'      => '/dashboard',
            ]);
        }

        return redirect()->to('/admin/lab-pic')->with('success', esc($user->username) . ' ditetapkan sebagai PIC lab ' . esc($lab['name']) . '.');
    }

    /**
     * Cabut PIC Laboran dari sebuah lab.
     */
    public function unassign(int $labId)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($labId);
        if (! $lab) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Data lab tidak ditemukan.');
        }

        $currentPicId = (int) ($lab['pic_user_id'] ?? 0);
        if ($currentPicId <= 0) {
            return redirect()->to('/admin/lab-pic')->with('info', 'Lab ini belum memiliki PIC.');
        }

        $actorId = (int) auth()->id();
        $now     = date('Y-m-d H:i:s');

        $db = db_connect();
        $db->transStart();

        $this->labModel->update($labId, ['pic_user_id' => null]);
        $this->historyModel->insert([
            'lab_id'     => $labId,
            'user_id'    => $currentPicId,
            'action'     => 'revoked',
            'actor_id'   => $actorId,
            'note'       => 'PIC dicabut.',
            'created_at' => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/lab-pic')->with('error', 'Gagal mencabut PIC. Silakan coba lagi.');
        }

        return redirect()->to('/admin/lab-pic')->with('success', 'PIC lab ' . esc($lab['name']) . ' berhasil dicabut.');
    }

    /**
     * Daftar user aktif ber-role laboran sebagai kandidat PIC.
     */
    private function getLaboranCandidates(): array
    {
        return db_connect()->table('users u')
            ->select('u.id, u.username, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('u.active', 1)
            ->where('agu.group', 'laboran')
            ->groupBy('u.id, u.username, ai.secret')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.labs.assign-pic')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses untuk menetapkan PIC Laboran.');
        }

        return null;
    }
}
