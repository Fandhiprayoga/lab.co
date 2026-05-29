<?php

namespace App\Controllers;

use App\Models\LabModel;
use App\Models\LabPhotoModel;
use App\Models\LabConditionHistoryModel;
use App\Models\LabVisitModel;

class LabController extends BaseController
{
    protected LabModel $labModel;
    protected LabPhotoModel $labPhotoModel;
    protected LabConditionHistoryModel $labConditionHistoryModel;

    public function __construct()
    {
        $this->labModel                 = new LabModel();
        $this->labPhotoModel            = new LabPhotoModel();
        $this->labConditionHistoryModel = new LabConditionHistoryModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->labModel
            ->orderBy('labs.name', 'ASC')
            ->findAll();

        if (! empty($labs)) {
            $labIds = array_column($labs, 'id');
            $primaryPhotos = $this->labPhotoModel
                ->whereIn('lab_id', $labIds)
                ->where('is_primary', 1)
                ->findAll();
            $primaryMap = [];
            foreach ($primaryPhotos as $p) {
                $primaryMap[(int) $p['lab_id']] = $p['file_path'];
            }
            foreach ($labs as &$lab) {
                $lab['primary_photo'] = $primaryMap[(int) $lab['id']] ?? null;
            }
            unset($lab);
        }

        return $this->renderView('loans/labs/index', [
            'title'      => 'Master Ruangan Lab',
            'page_title' => 'Master Data Ruangan/Lab',
            'labs'       => $labs,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/labs/create', [
            'title'      => 'Tambah Master Lab',
            'page_title' => 'Tambah Ruangan/Lab',
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        return $this->renderView('loans/labs/edit', [
            'title'      => 'Edit Master Lab',
            'page_title' => 'Edit Ruangan/Lab',
            'lab'        => $lab,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'       => 'required|min_length[3]',
            'code'       => 'permit_empty|max_length[30]|is_unique[labs.code,deleted_at,NULL]',
            'description' => 'permit_empty|max_length[1000]',
            'condition_status' => 'required|in_list[baik,perlu_perbaikan,rusak]',
            'lab_logo'   => 'permit_empty|max_size[lab_logo,2048]|is_image[lab_logo]|mime_in[lab_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $logoPath = $this->handleLogoUpload();

        $conditionStatus = trim((string) $this->request->getPost('condition_status'));
        $isLoanable = $this->request->getPost('is_loanable') ? 1 : 0;
        if ($conditionStatus === 'rusak') {
            $isLoanable = 0;
        }

        $this->labModel->insert([
            'name'       => trim((string) $this->request->getPost('name')),
            'code'       => trim((string) $this->request->getPost('code')) ?: null,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'location'   => trim((string) $this->request->getPost('location')) ?: null,
            'capacity'   => $this->request->getPost('capacity') !== null && $this->request->getPost('capacity') !== ''
                ? (int) $this->request->getPost('capacity')
                : null,
            'logo'       => $logoPath,
            'qr_token'   => bin2hex(random_bytes(16)),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable' => $isLoanable,
            'condition_status' => $conditionStatus,
        ]);

        return redirect()->to('/admin/loans/labs')->with('success', 'Master lab berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        $rules = [
            'name'       => 'required|min_length[3]',
            'code'       => 'permit_empty|max_length[30]|is_unique[labs.code,id,' . $id . ']',
            'description' => 'permit_empty|max_length[1000]',
            'condition_status' => 'required|in_list[baik,perlu_perbaikan,rusak]',
            'lab_logo'   => 'permit_empty|max_size[lab_logo,2048]|is_image[lab_logo]|mime_in[lab_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        $oldCondition = (string) ($lab['condition_status'] ?? '');
        $newCondition = trim((string) $this->request->getPost('condition_status'));
        $conditionChanged = $oldCondition !== $newCondition;
        if ($conditionChanged) {
            $rules['condition_reason'] = 'required|min_length[5]|max_length[500]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $logoPath = $this->handleLogoUpload($lab['logo'] ?? null);

        $conditionStatus = $newCondition;
        $isLoanable = $this->request->getPost('is_loanable') ? 1 : 0;
        if ($conditionStatus === 'rusak') {
            $isLoanable = 0;
        }

        $payload = [
            'name'       => trim((string) $this->request->getPost('name')),
            'code'       => trim((string) $this->request->getPost('code')) ?: null,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'location'   => trim((string) $this->request->getPost('location')) ?: null,
            'capacity'   => $this->request->getPost('capacity') !== null && $this->request->getPost('capacity') !== ''
                ? (int) $this->request->getPost('capacity')
                : null,
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable' => $isLoanable,
            'condition_status' => $conditionStatus,
        ];

        if ($logoPath !== null) {
            $payload['logo'] = $logoPath;
        }

        $this->labModel->update($id, $payload);

        if ($conditionChanged) {
            $this->labConditionHistoryModel->insert([
                'lab_id'             => $id,
                'previous_condition' => $oldCondition !== '' ? $oldCondition : null,
                'new_condition'      => $newCondition,
                'reason'             => trim((string) $this->request->getPost('condition_reason')),
                'changed_by'         => auth()->id(),
            ]);
        }

        return redirect()->to('/admin/loans/labs')->with('success', 'Master lab berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        $this->labModel->delete($id);

        return redirect()->to('/admin/loans/labs')->with('success', 'Master lab dipindahkan ke arsip.');
    }

    public function archive()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->labModel
            ->onlyDeleted()
            ->orderBy('deleted_at', 'DESC')
            ->findAll();

        return $this->renderView('loans/labs/archive', [
            'title'      => 'Arsip Ruangan/Lab',
            'page_title' => 'Arsip Ruangan/Lab',
            'labs'       => $labs,
        ]);
    }

    public function restore(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->onlyDeleted()->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs/archive')->with('error', 'Data lab tidak ditemukan di arsip.');
        }

        $this->labModel->update($id, ['deleted_at' => null]);

        return redirect()->to('/admin/loans/labs/archive')->with('success', 'Master lab berhasil dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->withDeleted()->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs/archive')->with('error', 'Data lab tidak ditemukan.');
        }

        $usedByAssets = db_connect()->table('lab_assets')->where('lab_id', $id)->countAllResults() > 0;
        if ($usedByAssets) {
            return redirect()->to('/admin/loans/labs/archive')->with('error', 'Lab tidak bisa dihapus permanen karena masih dipakai oleh data alat.');
        }

        if (! empty($lab['logo']) && file_exists(FCPATH . $lab['logo'])) {
            unlink(FCPATH . $lab['logo']);
        }

        $this->labModel->delete($id, true);

        return redirect()->to('/admin/loans/labs/archive')->with('success', 'Master lab berhasil dihapus permanen.');
    }

    public function photos(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        $photos = $this->labPhotoModel
            ->where('lab_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->renderView('loans/labs/photos', [
            'title'      => 'Galeri Foto Lab',
            'page_title' => 'Galeri Foto - ' . $lab['name'],
            'lab'        => $lab,
            'photos'     => $photos,
        ]);
    }

    public function uploadPhoto(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        $files = $this->request->getFileMultiple('photos');
        if (empty($files)) {
            return redirect()->back()->with('error', 'Tidak ada file yang dipilih.');
        }

        $uploadPath = FCPATH . 'uploads/labs/gallery/' . $id;
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploaded = 0;
        $errors   = [];
        $maxOrder = (int) ($this->labPhotoModel->where('lab_id', $id)->selectMax('sort_order')->first()['sort_order'] ?? 0);

        foreach ($files as $file) {
            if (! $file || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            $mime = $file->getMimeType();
            if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
                $errors[] = $file->getClientName() . ' (format tidak didukung)';
                continue;
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                $errors[] = $file->getClientName() . ' (melebihi 5 MB)';
                continue;
            }

            $extension = strtolower($file->getExtension() ?: $file->getClientExtension() ?: 'jpg');
            $fileName  = 'lab_' . $id . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $extension;
            $file->move($uploadPath, $fileName);

            $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;
            try {
                \Config\Services::image('gd')
                    ->withFile($fullPath)
                    ->resize(1600, 1600, true, 'auto')
                    ->save($fullPath, 85);
            } catch (\Throwable $e) {
                // Keep original if image lib unavailable.
            }

            $maxOrder++;
            $this->labPhotoModel->insert([
                'lab_id'     => $id,
                'file_path'  => 'uploads/labs/gallery/' . $id . '/' . $fileName,
                'sort_order' => $maxOrder,
                'is_primary' => 0,
            ]);
            $uploaded++;
        }

        $message = $uploaded . ' foto berhasil diunggah.';
        if (! empty($errors)) {
            $message .= ' Gagal: ' . implode(', ', $errors);
        }

        return redirect()->to('/admin/loans/labs/' . $id . '/photos')->with($uploaded > 0 ? 'success' : 'error', $message);
    }

    public function deletePhoto(int $id, int $photoId)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $photo = $this->labPhotoModel->where('lab_id', $id)->find($photoId);
        if (! $photo) {
            return redirect()->to('/admin/loans/labs/' . $id . '/photos')->with('error', 'Foto tidak ditemukan.');
        }

        if (! empty($photo['file_path']) && file_exists(FCPATH . $photo['file_path'])) {
            unlink(FCPATH . $photo['file_path']);
        }

        $this->labPhotoModel->delete($photoId);

        return redirect()->to('/admin/loans/labs/' . $id . '/photos')->with('success', 'Foto berhasil dihapus.');
    }

    public function setPrimaryPhoto(int $id, int $photoId)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $photo = $this->labPhotoModel->where('lab_id', $id)->find($photoId);
        if (! $photo) {
            return redirect()->to('/admin/loans/labs/' . $id . '/photos')->with('error', 'Foto tidak ditemukan.');
        }

        $db = db_connect();
        $db->table('lab_photos')->where('lab_id', $id)->update(['is_primary' => 0]);
        $db->table('lab_photos')->where('id', $photoId)->update(['is_primary' => 1]);

        return redirect()->to('/admin/loans/labs/' . $id . '/photos')->with('success', 'Foto utama berhasil diatur.');
    }

    public function qrIndex()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labs = $this->labModel->orderBy('name', 'ASC')->findAll();

        return $this->renderView('loans/labs/qr_index', [
            'title'      => 'QR Code Ruangan/Lab',
            'page_title' => 'QR Code Ruangan/Lab',
            'labs'       => $labs,
        ]);
    }

    public function qr(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs/qr')->with('error', 'Data lab tidak ditemukan.');
        }

        if (empty($lab['qr_token'])) {
            $this->labModel->update($id, ['qr_token' => bin2hex(random_bytes(16))]);
            $lab = $this->labModel->find($id);
        }

        return view('loans/labs/qr_show', [
            'lab'     => $lab,
            'scanUrl' => base_url('labs/scan/' . $lab['qr_token']),
        ]);
    }

    public function qrImage(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab || empty($lab['qr_token'])) {
            return $this->response->setStatusCode(404)->setBody('Lab tidak ditemukan.');
        }

        $url = base_url('labs/scan/' . $lab['qr_token']);

        $builder = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\PngWriter(),
            data: $url,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
            size: 600,
            margin: 16,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
        );
        $result = $builder->build();

        return $this->response
            ->setHeader('Content-Type', $result->getMimeType())
            ->setBody($result->getString());
    }

    public function datatable()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $request = $this->request;
        $draw    = (int) $request->getGet('draw');
        $start   = max(0, (int) $request->getGet('start'));
        $length  = (int) $request->getGet('length');
        if ($length <= 0) {
            $length = 10;
        }

        $search = (string) ($request->getGet('search')['value'] ?? '');
        $orderColumn = (int) ($request->getGet('order')[0]['column'] ?? 1);
        $orderDir    = strtolower((string) ($request->getGet('order')[0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $filterLoanable  = $request->getGet('filter_loanable');
        $filterCondition = $request->getGet('filter_condition');
        $filterActive    = $request->getGet('filter_active');

        $columns = ['logo', 'name', 'code', 'description', 'is_loanable', 'condition_status', 'location', 'capacity', 'is_active', 'actions'];
        $orderField = $columns[$orderColumn] ?? 'name';
        if (in_array($orderField, ['logo', 'actions'], true)) {
            $orderField = 'name';
        }

        $db = db_connect();

        $recordsTotal = $db->table('labs')->where('deleted_at', null)->countAllResults();

        $base = $db->table('labs')->where('deleted_at', null);

        if ($search !== '') {
            $base->groupStart()
                ->like('name', $search)
                ->orLike('code', $search)
                ->orLike('location', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }
        if ($filterLoanable !== null && $filterLoanable !== '') {
            $base->where('is_loanable', (int) $filterLoanable);
        }
        if ($filterCondition !== null && $filterCondition !== '') {
            $base->where('condition_status', $filterCondition);
        }
        if ($filterActive !== null && $filterActive !== '') {
            $base->where('is_active', (int) $filterActive);
        }

        $recordsFiltered = (clone $base)->countAllResults(false);

        $labs = $base->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $primaryMap = [];
        if (! empty($labs)) {
            $ids = array_column($labs, 'id');
            $primaries = $this->labPhotoModel->whereIn('lab_id', $ids)->where('is_primary', 1)->findAll();
            foreach ($primaries as $p) {
                $primaryMap[(int) $p['lab_id']] = $p['file_path'];
            }
        }

        $data = [];
        foreach ($labs as $lab) {
            $id    = (int) $lab['id'];
            $thumb = $primaryMap[$id] ?? ($lab['logo'] ?? null);
            $thumbUrl = ! empty($thumb) ? base_url($thumb) : base_url('assets/img/stisla-fill.svg');

            $loanableBadge  = (int) $lab['is_loanable'] === 1
                ? '<span class="badge badge-success">Ya</span>'
                : '<span class="badge badge-secondary">Tidak</span>';

            $condition = (string) ($lab['condition_status'] ?? 'baik');
            $conditionBadge = $condition === 'baik'
                ? '<span class="badge badge-success">Baik</span>'
                : ($condition === 'perlu_perbaikan'
                    ? '<span class="badge badge-warning">Perlu Perbaikan</span>'
                    : '<span class="badge badge-danger">Rusak</span>');

            $activeBadge = (int) $lab['is_active'] === 1
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>';

            $csrfName  = csrf_token();
            $csrfHash  = csrf_hash();
            $editUrl   = base_url('admin/loans/labs/edit/' . $id);
            $photosUrl = base_url('admin/loans/labs/' . $id . '/photos');
            $qrUrl     = base_url('admin/loans/labs/' . $id . '/qr');
            $delUrl    = base_url('admin/loans/labs/delete/' . $id);
            $name      = esc((string) $lab['name']);

            $actions = '<a href="' . $editUrl . '" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a> '
                . '<a href="' . $photosUrl . '" class="btn btn-sm btn-secondary" title="Galeri"><i class="fas fa-images"></i></a> '
                . '<a href="' . $qrUrl . '" class="btn btn-sm btn-dark" title="QR"><i class="fas fa-qrcode"></i></a> '
                . '<form action="' . $delUrl . '" method="post" class="d-inline js-swal-delete-form" '
                . 'data-swal-title="Arsipkan data lab?" data-swal-text="Data \'' . $name . '\' akan dipindahkan ke arsip." '
                . 'data-swal-confirm="Ya, arsipkan" data-swal-cancel="Batal">'
                . '<input type="hidden" name="' . $csrfName . '" value="' . $csrfHash . '">'
                . '<button type="submit" class="btn btn-sm btn-danger" title="Arsipkan"><i class="fas fa-archive"></i></button>'
                . '</form>';

            $data[] = [
                '<img src="' . $thumbUrl . '" alt="foto" class="img-thumbnail" style="width:48px;height:48px;object-fit:cover;">',
                esc((string) $lab['name']),
                esc((string) ($lab['code'] ?? '-')),
                esc((string) ($lab['description'] ?? '-')),
                $loanableBadge,
                $conditionBadge,
                esc((string) ($lab['location'] ?? '-')),
                $lab['capacity'] !== null ? (int) $lab['capacity'] : '-',
                $activeBadge,
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

    public function conditionHistoryAll()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labId    = (int) $this->request->getGet('lab_id');
        $dateFrom = (string) $this->request->getGet('date_from');
        $dateTo   = (string) $this->request->getGet('date_to');

        $builder = $this->labConditionHistoryModel
            ->select('lab_condition_history.*, labs.name AS lab_name, labs.code AS lab_code, users.username AS changed_by_name')
            ->join('labs', 'labs.id = lab_condition_history.lab_id', 'left')
            ->join('users', 'users.id = lab_condition_history.changed_by', 'left')
            ->orderBy('lab_condition_history.created_at', 'DESC');

        if ($labId > 0) {
            $builder->where('lab_condition_history.lab_id', $labId);
        }
        if ($dateFrom !== '') {
            $builder->where('lab_condition_history.created_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo !== '') {
            $builder->where('lab_condition_history.created_at <=', $dateTo . ' 23:59:59');
        }

        $histories = $builder->findAll();
        $labs      = $this->labModel->orderBy('name', 'ASC')->findAll();

        return $this->renderView('loans/labs/condition_history_all', [
            'title'      => 'Riwayat Kondisi Lab',
            'page_title' => 'Riwayat Perubahan Kondisi Lab',
            'histories'  => $histories,
            'labs'       => $labs,
            'filters'    => ['lab_id' => $labId, 'date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }

    public function conditionHistory(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/loans/labs')->with('error', 'Data lab tidak ditemukan.');
        }

        $histories = $this->labConditionHistoryModel
            ->select('lab_condition_history.*, users.username AS changed_by_name')
            ->join('users', 'users.id = lab_condition_history.changed_by', 'left')
            ->where('lab_condition_history.lab_id', $id)
            ->orderBy('lab_condition_history.created_at', 'DESC')
            ->findAll();

        return $this->renderView('loans/labs/condition_history', [
            'title'      => 'Riwayat Kondisi - ' . $lab['name'],
            'page_title' => 'Riwayat Kondisi - ' . $lab['name'],
            'lab'        => $lab,
            'histories'  => $histories,
        ]);
    }

    public function scan(string $token)
    {
        $lab = $this->labModel->where('qr_token', $token)->first();
        if (! $lab) {
            return view('visits/not_found');
        }

        $visitModel = new LabVisitModel();
        $sessionKey = 'lab_visit_' . $token;

        // ----------------------------------------------------------------
        // POST: handle check-in atau check-out
        // ----------------------------------------------------------------
        if ($this->request->getMethod() === 'post') {
            $action = $this->request->getPost('_action');

            // --- CHECK-OUT ---
            if ($action === 'checkout') {
                $visitId = session()->get($sessionKey);
                if (! $visitId) {
                    return redirect()->to(base_url('labs/scan/' . $token));
                }
                $visit = $visitModel->findActive((int) $lab['id'], (int) $visitId);
                if (! $visit) {
                    session()->remove($sessionKey);
                    return redirect()->to(base_url('labs/scan/' . $token));
                }

                $visitModel->update((int) $visit['id'], [
                    'checked_out_at' => date('Y-m-d H:i:s'),
                ]);
                session()->remove($sessionKey);

                return view('visits/success', [
                    'lab'      => $lab,
                    'mode'     => 'checkout',
                    'visitor'  => $visit['visitor_name'],
                    'checkin'  => $visit['checked_in_at'],
                    'checkout' => date('Y-m-d H:i:s'),
                ]);
            }

            // --- CHECK-IN ---
            $rules = [
                'visitor_name'        => 'required|min_length[2]|max_length[150]',
                'visitor_institution' => 'permit_empty|max_length[200]',
                'purpose'             => 'required|in_list[praktikum,penelitian,kunjungan,pengambilan_alat,lainnya]',
                'purpose_note'        => 'permit_empty|max_length[255]',
            ];

            if (! $this->validate($rules)) {
                return view('visits/checkin', [
                    'lab'    => $lab,
                    'errors' => $this->validator->getErrors(),
                    'old'    => $this->request->getPost(),
                ]);
            }

            $visitId = $visitModel->insert([
                'lab_id'              => (int) $lab['id'],
                'visitor_name'        => trim($this->request->getPost('visitor_name')),
                'visitor_institution' => trim($this->request->getPost('visitor_institution') ?? ''),
                'purpose'             => $this->request->getPost('purpose'),
                'purpose_note'        => trim($this->request->getPost('purpose_note') ?? ''),
                'checked_in_at'       => date('Y-m-d H:i:s'),
            ]);

            session()->set($sessionKey, $visitId);

            return view('visits/success', [
                'lab'     => $lab,
                'mode'    => 'checkin',
                'visitor' => trim($this->request->getPost('visitor_name')),
                'checkin' => date('Y-m-d H:i:s'),
            ]);
        }

        // ----------------------------------------------------------------
        // GET: tentukan tampilkan form check-in atau konfirmasi check-out
        // ----------------------------------------------------------------
        $activeVisitId = session()->get($sessionKey);
        if ($activeVisitId) {
            $activeVisit = $visitModel->findActive((int) $lab['id'], (int) $activeVisitId);
            if ($activeVisit) {
                return view('visits/checkout', [
                    'lab'   => $lab,
                    'visit' => $activeVisit,
                    'token' => $token,
                ]);
            }
            // visit sudah di-checkout dari tempat lain, bersihkan session
            session()->remove($sessionKey);
        }

        return view('visits/checkin', [
            'lab'    => $lab,
            'errors' => [],
            'old'    => [],
            'token'  => $token,
        ]);
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.labs.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master lab.');
        }

        return null;
    }

    private function handleLogoUpload(?string $oldLogo = null): ?string
    {
        $logo = $this->request->getFile('lab_logo');
        if (! $logo || ! $logo->isValid() || $logo->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . 'uploads/labs';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (! empty($oldLogo) && file_exists(FCPATH . $oldLogo)) {
            unlink(FCPATH . $oldLogo);
        }

        $extension = strtolower($logo->getExtension() ?: $logo->getClientExtension() ?: 'bin');
        $logoName  = 'lab_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $extension;
        $logo->move($uploadPath, $logoName);

        $savedPath = $uploadPath . DIRECTORY_SEPARATOR . $logoName;
        $this->normalizeLogoImage($savedPath, $extension);

        return 'uploads/labs/' . $logoName;
    }

    private function normalizeLogoImage(string $fullPath, string $extension): void
    {
        // SVG is vector-based; keep as-is and let CSS handle render sizing.
        if ($extension === 'svg') {
            return;
        }

        if (! file_exists($fullPath)) {
            return;
        }

        try {
            // Force all raster logos to a square canvas for uniform thumbnails.
            \Config\Services::image('gd')
                ->withFile($fullPath)
                ->fit(400, 400, 'center')
                ->save($fullPath, 85);
        } catch (\Throwable $e) {
            // Keep original upload when image manipulation library is unavailable.
        }
    }
}
