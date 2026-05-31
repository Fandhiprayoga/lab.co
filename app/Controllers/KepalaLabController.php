<?php

namespace App\Controllers;

use App\Models\KepalaLabHistoryModel;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Manajemen penetapan Kepala Lab (singleton).
 *
 * Sistem hanya mengizinkan SATU Kepala Lab yang mengepalai seluruh lab.
 * Role `kepala_lab` bersifat global (bukan per-lab). Penetapan baru otomatis
 * mencabut role dari pemegang lama. Hanya superadmin yang boleh mengakses
 * (di-enforce via filter role:superadmin pada Routes).
 */
class KepalaLabController extends BaseController
{
    /** Role yang menjadi kandidat Kepala Lab (mahasiswa & alumni dikecualikan). */
    private const ELIGIBLE_GROUPS = ['dosen', 'laboran', 'asisten', 'superadmin'];

    protected UserModel $userModel;
    protected KepalaLabHistoryModel $historyModel;

    public function __construct()
    {
        $this->userModel    = new UserModel();
        $this->historyModel = new KepalaLabHistoryModel();
    }

    /**
     * Halaman penetapan Kepala Lab.
     */
    public function index()
    {
        $data = [
            'title'      => 'Kepala Lab',
            'page_title' => 'Penetapan Kepala Lab',
            'current'    => $this->getCurrentKepalaLab(),
            'candidates' => $this->getEligibleCandidates(),
            'history'    => $this->historyModel->recentWithNames(20),
        ];

        return $this->renderView('admin/kepala_lab/index', $data);
    }

    /**
     * Tetapkan user terpilih sebagai Kepala Lab (singleton).
     */
    public function assign()
    {
        $userId = (int) $this->request->getPost('user_id');

        if ($userId <= 0) {
            return redirect()->to('/admin/kepala-lab')->with('error', 'Pilih user yang akan ditetapkan sebagai Kepala Lab.');
        }

        $user = $this->userModel->findById($userId);
        if (! $user) {
            return redirect()->to('/admin/kepala-lab')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->active) {
            return redirect()->to('/admin/kepala-lab')->with('error', 'User tidak aktif tidak dapat ditetapkan sebagai Kepala Lab.');
        }

        // Pastikan kandidat eligible (punya minimal satu role yang diizinkan, bukan hanya mahasiswa/alumni).
        if (! $this->isEligible($user)) {
            return redirect()->to('/admin/kepala-lab')->with('error', 'User tersebut tidak memenuhi syarat sebagai Kepala Lab.');
        }

        // Sudah menjadi Kepala Lab? Tidak perlu apa-apa.
        if ($user->inGroup('kepala_lab')) {
            return redirect()->to('/admin/kepala-lab')->with('info', esc($user->username) . ' sudah menjadi Kepala Lab.');
        }

        $actorId = (int) auth()->id();
        $now     = date('Y-m-d H:i:s');

        $db = db_connect();
        $db->transStart();

        // Cabut role kepala_lab dari semua pemegang lama (enforce singleton), role lain dipertahankan.
        $currentIds = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'kepala_lab')
            ->get()
            ->getResultArray();

        foreach ($currentIds as $row) {
            $old = $this->userModel->findById((int) $row['user_id']);
            if ($old) {
                $old->removeGroup('kepala_lab');
                $this->historyModel->insert([
                    'user_id'    => (int) $row['user_id'],
                    'action'     => 'revoked',
                    'actor_id'   => $actorId,
                    'note'       => 'Dicabut karena Kepala Lab baru ditetapkan.',
                    'created_at' => $now,
                ]);
            }
        }

        // Tetapkan user baru sebagai Kepala Lab (role tambahan).
        $user->addGroup('kepala_lab');
        $this->historyModel->insert([
            'user_id'    => (int) $user->id,
            'action'     => 'assigned',
            'actor_id'   => $actorId,
            'note'       => 'Ditetapkan sebagai Kepala Lab.',
            'created_at' => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/kepala-lab')->with('error', 'Gagal menetapkan Kepala Lab. Silakan coba lagi.');
        }

        // Notifikasi ke Kepala Lab baru.
        if (function_exists('send_notification')) {
            send_notification($user->id, 'role.kepala_lab_assigned', [
                'url' => '/dashboard',
            ]);
        }

        return redirect()->to('/admin/kepala-lab')->with('success', esc($user->username) . ' berhasil ditetapkan sebagai Kepala Lab.');
    }

    /**
     * Ambil Kepala Lab aktif saat ini (atau null jika belum ada).
     */
    private function getCurrentKepalaLab(): ?array
    {
        $row = db_connect()->table('auth_groups_users agu')
            ->select('u.id, u.username, ai.secret AS email')
            ->join('users u', 'u.id = agu.user_id')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->where('agu.group', 'kepala_lab')
            ->orderBy('agu.id', 'DESC')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Daftar user aktif yang memenuhi syarat menjadi Kepala Lab.
     * Eligible = active=1 DAN punya minimal satu role di ELIGIBLE_GROUPS.
     */
    private function getEligibleCandidates(): array
    {
        return db_connect()->table('users u')
            ->select('u.id, u.username, ai.secret AS email, GROUP_CONCAT(DISTINCT agu.group ORDER BY agu.group SEPARATOR ",") AS groups')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('u.active', 1)
            ->whereIn('agu.group', self::ELIGIBLE_GROUPS)
            ->groupBy('u.id, u.username, ai.secret')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Cek apakah user punya minimal satu role yang diizinkan sebagai Kepala Lab.
     */
    private function isEligible($user): bool
    {
        foreach (self::ELIGIBLE_GROUPS as $group) {
            if ($user->inGroup($group)) {
                return true;
            }
        }

        return false;
    }
}
