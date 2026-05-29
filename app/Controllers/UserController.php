<?php

namespace App\Controllers;

use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Daftar semua users
     */
    public function index()
    {
        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Manajemen User',
            'page_title' => 'Daftar User',
            'groups'     => $authGroups->groups,
        ];

        return $this->renderView('users/index', $data);
    }

    /**
     * Server-side DataTables endpoint: GET /admin/users/datatable
     */
    public function datatable()
    {
        if (! activeGroupCan('users.list')) {
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

        // Custom filters
        $filterGroup  = $req->getGet('filter_group');
        $filterStatus = $req->getGet('filter_status');

        $colMap = [
            1 => 'u.username',
            2 => 'ai.secret',
        ];
        $orderField = $colMap[$orderCol] ?? 'u.id';

        $db = db_connect();

        $recordsTotal = $db->table('users')->countAllResults();

        // Filtered count query
        $countBase = $db->table('users u')
            ->select('COUNT(DISTINCT u.id) AS cnt')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left');

        if ($search !== '') {
            $countBase->groupStart()
                ->like('u.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }
        if (! empty($filterGroup)) {
            $countBase->where('agu.group', $filterGroup);
        }
        if ($filterStatus !== null && $filterStatus !== '') {
            $countBase->where('u.active', (int) $filterStatus);
        }

        $recordsFiltered = (int) ($countBase->get()->getRow()->cnt ?? 0);

        // Data query
        $dataBase = $db->table('users u')
            ->select('u.id, u.username, u.active, ai.secret AS email, GROUP_CONCAT(DISTINCT agu.group ORDER BY agu.group SEPARATOR ",") AS groups')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->groupBy('u.id, u.username, u.active, ai.secret');

        if ($search !== '') {
            $dataBase->groupStart()
                ->like('u.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }
        if (! empty($filterGroup)) {
            $dataBase->where('agu.group', $filterGroup);
        }
        if ($filterStatus !== null && $filterStatus !== '') {
            $dataBase->where('u.active', (int) $filterStatus);
        }

        $rows = $dataBase->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $badgeMap = [
            'superadmin' => 'badge-danger',
            'laboran'    => 'badge-warning',
            'asisten'    => 'badge-info',
            'kepala_lab' => 'badge-success',
            'dosen'      => 'badge-primary',
            'mahasiswa'  => 'badge-secondary',
        ];

        $csrfName = csrf_token();
        $csrfHash = csrf_hash();

        $data = [];
        foreach ($rows as $i => $row) {
            // Groups badges
            $groupBadges = '';
            if (! empty($row['groups'])) {
                foreach (explode(',', $row['groups']) as $g) {
                    $g           = trim($g);
                    $badgeClass  = $badgeMap[$g] ?? 'badge-secondary';
                    $groupBadges .= '<span class="badge ' . $badgeClass . '">' . esc(ucfirst($g)) . '</span> ';
                }
            } else {
                $groupBadges = '<span class="badge badge-secondary">No Role</span>';
            }

            // Status badge
            $statusBadge = $row['active']
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-danger">Nonaktif</span>';

            // Actions
            $actions = '';
            if (activeGroupCan('users.toggle-status') && (int) $row['id'] !== (int) auth()->id()) {
                $isActive      = (bool) $row['active'];
                $toggleTitle   = $isActive ? 'Nonaktifkan' : 'Aktifkan';
                $toggleIcon    = $isActive ? 'fa-ban' : 'fa-check-circle';
                $toggleBtn     = $isActive ? 'btn-warning' : 'btn-success';
                $swalTitle     = $isActive ? 'Nonaktifkan user?' : 'Aktifkan user?';
                $swalText      = $isActive
                    ? 'User \'' . esc($row['username']) . '\' akan dinonaktifkan dan tidak bisa login.'
                    : 'User \'' . esc($row['username']) . '\' akan diaktifkan kembali.';
                $swalIcon      = $isActive ? 'warning' : 'question';
                $swalColor     = $isActive ? '#e67e22' : '#28a745';
                $actions .= '<form action="' . base_url('admin/users/toggle-status/' . (int) $row['id']) . '" method="post" class="d-inline js-swal-confirm-form"'
                    . ' data-swal-title="' . $swalTitle . '"'
                    . ' data-swal-text="' . $swalText . '"'
                    . ' data-swal-icon="' . $swalIcon . '"'
                    . ' data-swal-confirm="Ya, ' . strtolower($toggleTitle) . '"'
                    . ' data-swal-cancel="Batal"'
                    . ' data-swal-confirm-color="' . $swalColor . '">'
                    . '<input type="hidden" name="' . $csrfName . '" value="' . $csrfHash . '">'
                    . '<button type="submit" class="btn btn-sm ' . $toggleBtn . ' mr-1" title="' . $toggleTitle . '"><i class="fas ' . $toggleIcon . '"></i></button>'
                    . '</form>';
            }
            if (activeGroupCan('users.edit')) {
                $actions .= '<a href="' . base_url('admin/users/edit/' . (int) $row['id']) . '" class="btn btn-sm btn-info mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
            }
            if (activeGroupCan('users.delete') && (int) $row['id'] !== (int) auth()->id()) {
                $actions .= '<form action="' . base_url('admin/users/delete/' . (int) $row['id']) . '" method="post" class="d-inline js-swal-delete-form"'
                    . ' data-swal-title="Hapus user?"'
                    . ' data-swal-text="User \'' . esc($row['username']) . '\' akan dihapus permanen."'
                    . ' data-swal-confirm="Ya, hapus" data-swal-cancel="Batal">'
                    . '<input type="hidden" name="' . $csrfName . '" value="' . $csrfHash . '">'
                    . '<button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>'
                    . '</form>';
            }

            $avatarUrl  = base_url('assets/img/avatar/avatar-1.png');
            $usernameHtml = '<img alt="avatar" src="' . $avatarUrl . '" class="rounded-circle mr-1" width="35">' . esc($row['username']);

            $data[] = [
                '',
                $usernameHtml,
                esc($row['email'] ?? '-'),
                $groupBadges,
                $statusBadge,
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

    /**
     * Form tambah user baru
     */
    public function create()
    {
        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Tambah User',
            'page_title' => 'Tambah User Baru',
            'groups'     => $authGroups->groups,
        ];

        return $this->renderView('users/create', $data);
    }

    /**
     * Simpan user baru
     */
    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
            'groups'   => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();

        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'active'   => 1,
        ]);

        $users->save($user);
        $user = $users->findById($users->getInsertID());

        // Assign groups/roles (multi-group support)
        $groups = $this->request->getPost('groups');
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $user->addGroup($group);
            }
        } else {
            $user->addGroup($groups);
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user
     */
    public function edit(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Edit User',
            'page_title' => 'Edit User',
            'user_edit'  => $user,
            'groups'     => $authGroups->groups,
            'userGroups' => $user->getGroups(),
        ];

        return $this->renderView('users/edit', $data);
    }

    /**
     * Update user
     */
    public function update(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'username' => "required|min_length[3]|max_length[30]",
            'email'    => "required|valid_email",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username = $this->request->getPost('username');
        $user->email    = $this->request->getPost('email');

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $user->password = $password;
        }

        $this->userModel->save($user);

        // Update groups jika ada (multi-group support)
        $groups = $this->request->getPost('groups');
        if (! empty($groups)) {
            // Hapus semua group lama
            foreach ($user->getGroups() as $oldGroup) {
                $user->removeGroup($oldGroup);
            }
            // Assign semua group baru
            if (is_array($groups)) {
                foreach ($groups as $group) {
                    $user->addGroup($group);
                }
            } else {
                $user->addGroup($groups);
            }
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif user
     */
    public function toggleStatus(int $id)
    {
        if (! activeGroupCan('users.toggle-status')) {
            return redirect()->to('/admin/users')->with('error', 'Anda tidak punya izin untuk mengubah status user.');
        }

        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->to('/admin/users')->with('error', 'Anda tidak bisa mengubah status akun sendiri.');
        }

        $newActive = $user->active ? 0 : 1;
        db_connect()->table('users')->where('id', $id)->update(['active' => $newActive]);

        $msg = $newActive ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';
        return redirect()->to('/admin/users')->with('success', $msg);
    }

    /**
     * Hapus user
     */
    public function delete(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        // Jangan bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->to('/admin/users')->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $this->userModel->delete($id, true);

        return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Assign role ke user
     */
    public function assignRole(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $groups = $this->request->getPost('groups');

        // Hapus semua group lama
        foreach ($user->getGroups() as $oldGroup) {
            $user->removeGroup($oldGroup);
        }

        // Assign groups baru (multi-group support)
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $user->addGroup($group);
            }
        } else {
            $user->addGroup($groups);
        }

        return redirect()->to('/admin/users')->with('success', 'Role user berhasil diperbarui.');
    }
}
