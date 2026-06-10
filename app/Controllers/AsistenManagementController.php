<?php

namespace App\Controllers;

use App\Models\UserLabAssignmentModel;
use App\Models\LabModel;
use CodeIgniter\Shield\Models\UserModel;

class AsistenManagementController extends BaseController
{
    protected UserLabAssignmentModel $assignmentModel;
    protected LabModel $labModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->assignmentModel = new UserLabAssignmentModel();
        $this->labModel        = new LabModel();
        $this->userModel       = new UserModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asistenUsers = $this->getAllAsisten();

        return $this->renderView('admin/asisten/index', [
            'title'      => 'Manajemen Asisten',
            'page_title' => 'Manajemen Asisten',
            'users'      => $asistenUsers,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->getAvailableLabs();

        // Ambil data user yang belum jadi asisten
        $db = db_connect();
        $users = $db->table('users u')
            ->select('u.id, u.username, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id AND agu.`group` = \'asisten\'', 'left')
            ->where('u.active', 1)
            ->where('agu.id IS NULL')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/asisten/create', [
            'title'      => 'Tambah Asisten',
            'page_title' => 'Tambah Asisten Baru',
            'labs'       => $labs,
            'users'      => $users,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $userId = (int) $this->request->getPost('user_id');
        $labIds = $this->request->getPost('lab_ids') ?? [];

        if ($userId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Pilih user yang akan dijadikan asisten.');
        }

        $user = $this->userModel->findById($userId);
        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'User tidak ditemukan.');
        }

        if ($user->inGroup('asisten')) {
            return redirect()->back()->withInput()->with('error', 'User ini sudah menjadi asisten.');
        }

        // Validasi lab_ids untuk laboran: hanya boleh assign ke lab yang dia pegang
        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            foreach ($labIds as $lid) {
                if (! in_array((int) $lid, $myLabIds, true)) {
                    return redirect()->back()->withInput()->with('error', 'Anda hanya dapat menugaskan asisten ke lab tempat Anda bertugas.');
                }
            }
        }

        $db = db_connect();
        $db->transStart();

        $user->addGroup('asisten');
        $this->assignmentModel->assignLabs($userId, $labIds);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data asisten.');
        }

        return redirect()->to('/admin/asisten')->with('success', 'Asisten berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/admin/asisten')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('asisten')) {
            return redirect()->to('/admin/asisten')->with('error', 'User ini bukan asisten.');
        }

        // Laboran hanya bisa edit asisten di lab mereka
        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            $asistenLabIds = $this->assignmentModel->getLabIdsByUser($id);
            $intersection = array_intersect($asistenLabIds, $myLabIds);
            if (empty($intersection)) {
                return redirect()->to('/admin/asisten')->with('error', 'Anda tidak memiliki akses ke asisten ini.');
            }
        }

        $labs = $this->getAvailableLabs();
        $assignedLabIds = $this->assignmentModel->getLabIdsByUser($id);

        return $this->renderView('admin/asisten/edit', [
            'title'          => 'Edit Asisten',
            'page_title'     => 'Edit ' . esc($user->username),
            'user'           => $user,
            'labs'           => $labs,
            'assignedLabIds' => $assignedLabIds,
        ]);
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/admin/asisten')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('asisten')) {
            return redirect()->to('/admin/asisten')->with('error', 'User ini bukan asisten.');
        }

        $labIds = $this->request->getPost('lab_ids') ?? [];

        // Validasi lab_ids untuk laboran
        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            foreach ($labIds as $lid) {
                if (! in_array((int) $lid, $myLabIds, true)) {
                    return redirect()->back()->withInput()->with('error', 'Anda hanya dapat menugaskan asisten ke lab tempat Anda bertugas.');
                }
            }
        }

        $this->assignmentModel->assignLabs($id, $labIds);

        return redirect()->to('/admin/asisten')->with('success', 'Penugasan lab untuk ' . esc($user->username) . ' berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/admin/asisten')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('asisten')) {
            return redirect()->to('/admin/asisten')->with('error', 'User ini bukan asisten.');
        }

        // Laboran hanya bisa hapus asisten di lab mereka
        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            $asistenLabIds = $this->assignmentModel->getLabIdsByUser($id);
            $intersection = array_intersect($asistenLabIds, $myLabIds);
            if (empty($intersection)) {
                return redirect()->to('/admin/asisten')->with('error', 'Anda tidak memiliki akses ke asisten ini.');
            }
        }

        $db = db_connect();
        $db->transStart();

        $user->removeGroup('asisten');
        $this->assignmentModel->deleteByUser($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/asisten')->with('error', 'Gagal menghapus asisten.');
        }

        return redirect()->to('/admin/asisten')->with('success', 'Asisten ' . esc($user->username) . ' berhasil dihapus.');
    }

    public function searchUsers()
    {
        if ($guard = $this->guardAccess(true)) {
            return $guard;
        }

        $q = trim((string) $this->request->getGet('q'));

        if (strlen($q) < 2) {
            return $this->response->setJSON(['results' => []]);
        }

        $db = db_connect();
        $likeQ = $db->escapeLikeString($q);

        $sql = "SELECT u.id, u.username, ai.secret AS email
                FROM users u
                LEFT JOIN auth_identities ai ON ai.user_id = u.id AND ai.type = 'email_password'
                LEFT JOIN auth_groups_users agu ON agu.user_id = u.id AND agu.`group` = 'asisten'
                WHERE u.active = 1
                  AND agu.id IS NULL
                  AND (u.username LIKE '%{$likeQ}%' OR ai.secret LIKE '%{$likeQ}%')
                GROUP BY u.id, u.username, ai.secret
                ORDER BY u.username ASC
                LIMIT 20";

        $results = $db->query($sql)->getResultArray();

        $formatted = array_map(fn($r) => [
            'id'   => (int) $r['id'],
            'text' => $r['username'] . ' (' . ($r['email'] ?? '-') . ')',
        ], $results);

        return $this->response->setJSON(['results' => $formatted]);
    }

    public function datatable()
    {
        if ($guard = $this->guardAccess(true)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req    = $this->request;
        $draw   = (int) $req->getGet('draw');
        $start  = max(0, (int) $req->getGet('start'));
        $length = (int) $req->getGet('length');
        if ($length <= 0) { $length = 10; }

        $search   = (string) ($req->getGet('search')['value'] ?? '');
        $orderCol = (int) ($req->getGet('order')[0]['column'] ?? 0);
        $orderDir = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $colMap = [
            0 => 'u.username',
            1 => 'ai.secret',
        ];
        $orderField = $colMap[$orderCol] ?? 'u.username';

        $db = db_connect();

        // Base: semua user dengan group asisten
        $baseBuilder = $db->table('users u')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'asisten');

        // Jika laboran, filter hanya asisten di lab mereka
        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            if (empty($myLabIds)) {
                return $this->response->setJSON([
                    'draw'            => $draw,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ]);
            }
            $baseBuilder->join('user_lab_assignments ula', 'ula.user_id = u.id')
                ->whereIn('ula.lab_id', $myLabIds)
                ->distinct();
        }

        $recordsTotal = (clone $baseBuilder)->countAllResults();

        $countBase = (clone $baseBuilder)->select('COUNT(DISTINCT u.id) AS cnt')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left');

        if ($search !== '') {
            $countBase->groupStart()
                ->like('u.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }

        $recordsFiltered = (int) ($countBase->get()->getRow()->cnt ?? 0);

        $dataBase = (clone $baseBuilder)
            ->select('u.id, u.username, u.active, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->groupBy('u.id, u.username, u.active, ai.secret');

        if ($search !== '') {
            $dataBase->groupStart()
                ->like('u.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }

        $rows = $dataBase->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $csrfName = csrf_token();
        $csrfHash = csrf_hash();

        $data = [];
        foreach ($rows as $row) {
            $labs = $this->assignmentModel->getLabsByUser((int) $row['id']);
            $labNames = array_map(fn($l) => esc($l->name), $labs);
            $labBadges = ! empty($labNames)
                ? implode(' ', array_map(fn($n) => '<span class="badge badge-info mr-1">' . $n . '</span>', $labNames))
                : '<span class="text-muted">Belum ditugaskan</span>';

            $statusBadge = (int) $row['active'] === 1
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>';

            $username = esc($row['username']);
            $editUrl  = base_url('admin/asisten/edit/' . $row['id']);
            $delUrl   = base_url('admin/asisten/delete/' . $row['id']);

            $actions = <<<HTML
                <a href="{$editUrl}" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="{$delUrl}" method="post" class="d-inline js-swal-delete-form"
                      data-swal-title="Hapus asisten?"
                      data-swal-text="User {$username} akan dihapus dari group asisten dan dicabut dari semua lab. Akun user tetap ada."
                      data-swal-confirm="Ya, hapus"
                      data-swal-cancel="Batal">
                    <input type="hidden" name="{$csrfName}" value="{$csrfHash}" />
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>
            HTML;

            $data[] = [
                $row['username'],
                $row['email'] ?? '-',
                $statusBadge,
                $labBadges,
                $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function getAllAsisten(): array
    {
        $db = db_connect();
        $builder = $db->table('users u')
            ->select('u.id, u.username, u.active, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'asisten');

        if (activeGroupIs('laboran')) {
            $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
            if (empty($myLabIds)) {
                return [];
            }
            $builder->join('user_lab_assignments ula', 'ula.user_id = u.id')
                ->whereIn('ula.lab_id', $myLabIds)
                ->distinct();
        }

        $rows = $builder->groupBy('u.id, u.username, u.active, ai.secret')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $labs = $this->assignmentModel->getLabsByUser((int) $row['id']);
            $row['labs'] = $labs;
        }
        unset($row);

        return $rows;
    }

    /**
     * Get labs available for assignment.
     * - superadmin: all active labs
     * - laboran: only labs they're assigned to
     */
    private function getAvailableLabs(): array
    {
        if (activeGroupIs('superadmin')) {
            return $this->labModel
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        // laboran: only their assigned labs
        $myLabIds = $this->assignmentModel->getLabIdsByUser(auth()->id());
        if (empty($myLabIds)) {
            return [];
        }

        return $this->labModel
            ->where('is_active', 1)
            ->whereIn('id', $myLabIds)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Guard access: superadmin or laboran.
     * @param bool $isJson if true, return JSON response for API endpoints
     * @return mixed null if allowed, RedirectResponse or ResponseInterface if denied
     */
    private function guardAccess(bool $isJson = false)
    {
        if (activeGroupIs('superadmin') || activeGroupIs('laboran')) {
            return null;
        }

        if ($isJson) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke manajemen asisten.');
    }
}
