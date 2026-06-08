<?php

namespace App\Controllers;

use App\Models\UserLabAssignmentModel;
use App\Models\LabModel;
use CodeIgniter\Shield\Models\UserModel;

class LaboranManagementController extends BaseController
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

        $laboranUsers = $this->getAllLaboran();

        return $this->renderView('admin/laboran/index', [
            'title'    => 'Manajemen Laboran',
            'page_title' => 'Manajemen Laboran',
            'users'    => $laboranUsers,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->labModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        //ambil data user yang belum jadi laboran
        $db = db_connect();
        $users = $db->table('users u')
            ->select('u.id, u.username, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id AND agu.`group` = \'laboran\'', 'left')
            ->where('u.active', 1)
            ->where('agu.id IS NULL')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/laboran/create', [
            'title'      => 'Tambah Laboran',
            'page_title' => 'Tambah Laboran Baru',
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
            return redirect()->back()->withInput()->with('error', 'Pilih user yang akan dijadikan laboran.');
        }

        $user = $this->userModel->findById($userId);
        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'User tidak ditemukan.');
        }

        if ($user->inGroup('laboran')) {
            return redirect()->back()->withInput()->with('error', 'User ini sudah menjadi laboran.');
        }

        $db = db_connect();
        $db->transStart();

        $user->addGroup('laboran');
        $this->assignmentModel->assignLabs($userId, $labIds);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data laboran.');
        }

        return redirect()->to('/admin/laboran')->with('success', 'Laboran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/admin/laboran')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('laboran')) {
            return redirect()->to('/admin/laboran')->with('error', 'User ini bukan laboran.');
        }

        $labs = $this->labModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $assignedLabIds = $this->assignmentModel->getLabIdsByUser($id);

        return $this->renderView('admin/laboran/edit', [
            'title'          => 'Edit Laboran',
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
            return redirect()->to('/admin/laboran')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('laboran')) {
            return redirect()->to('/admin/laboran')->with('error', 'User ini bukan laboran.');
        }

        $labIds = $this->request->getPost('lab_ids') ?? [];

        $this->assignmentModel->assignLabs($id, $labIds);

        return redirect()->to('/admin/laboran')->with('success', 'Penugasan lab untuk ' . esc($user->username) . ' berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $user = $this->userModel->findById($id);
        if (! $user) {
            return redirect()->to('/admin/laboran')->with('error', 'User tidak ditemukan.');
        }

        if (! $user->inGroup('laboran')) {
            return redirect()->to('/admin/laboran')->with('error', 'User ini bukan laboran.');
        }

        $db = db_connect();
        $db->transStart();

        $user->removeGroup('laboran');
        $this->assignmentModel->deleteByUser($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/laboran')->with('error', 'Gagal menghapus laboran.');
        }

        return redirect()->to('/admin/laboran')->with('success', 'Laboran ' . esc($user->username) . ' berhasil dihapus.');
    }

    public function searchUsers()
    {
        if (! activeGroupIs('superadmin')) {
            return $this->response->setJSON(['results' => []]);
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
                LEFT JOIN auth_groups_users agu ON agu.user_id = u.id AND agu.`group` = 'laboran'
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
        if (! activeGroupIs('superadmin')) {
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

        $recordsTotal = $db->table('users u')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'laboran')
            ->countAllResults();

        $countBase = $db->table('users u')
            ->select('COUNT(DISTINCT u.id) AS cnt')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'laboran');

        if ($search !== '') {
            $countBase->groupStart()
                ->like('u.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }

        $recordsFiltered = (int) ($countBase->get()->getRow()->cnt ?? 0);

        $dataBase = $db->table('users u')
            ->select('u.id, u.username, u.active, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'laboran')
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
            $editUrl  = base_url('admin/laboran/edit/' . $row['id']);
            $delUrl   = base_url('admin/laboran/delete/' . $row['id']);

            $actions = <<<HTML
                <a href="{$editUrl}" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="{$delUrl}" method="post" class="d-inline js-swal-delete-form"
                      data-swal-title="Hapus laboran?"
                      data-swal-text="User {$username} akan dihapus dari group laboran dan dicabut dari semua lab. Akun user tetap ada."
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

    private function getAllLaboran(): array
    {
        $rows = db_connect()->table('users u')
            ->select('u.id, u.username, u.active, ai.secret AS email')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->where('agu.group', 'laboran')
            ->groupBy('u.id, u.username, u.active, ai.secret')
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

    private function guardAccess()
    {
        if (! activeGroupIs('superadmin')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke manajemen laboran.');
        }

        return null;
    }
}
