<?php

namespace App\Controllers;

use App\Models\LabClearanceRequestModel;
use App\Models\LabModel;
use App\Models\StudyProgramModel;
use App\Models\UserProfileModel;
use CodeIgniter\I18n\Time;

class LabClearanceController extends BaseController
{
    private const STATUS_SUBMITTED = 'submitted';
    private const STATUS_APPROVED   = 'approved';
    private const STATUS_REJECTED   = 'rejected';
    private const STATUS_CANCELED   = 'canceled';

    protected LabClearanceRequestModel $clearanceModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->clearanceModel = new LabClearanceRequestModel();
        $this->labModel       = new LabModel();
    }

    /**
     * Beranda / Dashboard modul Surat Bebas Lab.
     * Menjelaskan fungsi modul, role yang terlibat, dan alur pengajuan,
     * dilengkapi ringkasan statistik sesuai hak akses pengguna.
     */
    public function beranda()
    {
        $isManager = $this->canManageAll();
        $isAlumni  = activeGroupIs('alumni');

        $db = db_connect();

        $base = $db->table('lab_clearance_requests c');
        if (! $isManager) {
            $base->where('c.requester_id', auth()->id());
        }

        $countByStatus = static function (string $status) use ($isManager) {
            $b = db_connect()->table('lab_clearance_requests')->where('status', $status);
            if (! $isManager) {
                $b->where('requester_id', auth()->id());
            }
            return $b->countAllResults();
        };

        $stats = [
            'total'     => $base->countAllResults(),
            'submitted' => $countByStatus(self::STATUS_SUBMITTED),
            'approved'  => $countByStatus(self::STATUS_APPROVED),
            'rejected'  => $countByStatus(self::STATUS_REJECTED),
        ];

        return $this->renderView('clearance/beranda', [
            'title'      => 'Beranda Surat Bebas Lab',
            'page_title' => 'Beranda Surat Bebas Lab',
            'isManager'  => $isManager,
            'isAlumni'   => $isAlumni,
            'stats'      => $stats,
        ]);
    }

    public function index()
    {
        $isManager = $this->canManageAll();
        $isAlumni  = activeGroupIs('alumni');

        $prodiBuilder = db_connect()->table('lab_clearance_requests c')
            ->select('c.prodi')
            ->where('c.prodi IS NOT NULL')
            ->where('c.prodi !=', '')
            ->groupBy('c.prodi')
            ->orderBy('c.prodi', 'ASC');
        if (! $isManager) {
            $prodiBuilder->where('c.requester_id', auth()->id());
        }
        $prodiOptions = array_column($prodiBuilder->get()->getResultArray(), 'prodi');

        return $this->renderView('clearance/index', [
            'title'        => 'Surat Bebas Lab',
            'page_title'   => $isManager ? 'Verifikasi Surat Bebas Lab' : 'Surat Bebas Lab Saya',
            'isManager'    => $isManager,
            'isAlumni'     => $isAlumni,
            'prodiOptions' => $prodiOptions,
        ]);
    }

    /**
     * Server-side DataTables endpoint: GET /clearance/datatable
     */
    public function datatable()
    {
        if (! activeGroupCan('clearance.request.track')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req      = $this->request;
        $draw     = (int) $req->getGet('draw');
        $start    = max(0, (int) $req->getGet('start'));
        $length   = (int) $req->getGet('length');
        if ($length <= 0) { $length = 10; }

        $search   = (string) ($req->getGet('search')['value'] ?? '');
        $orderCol = (int) ($req->getGet('order')[0]['column'] ?? 6);
        $orderDir = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $filterStatus = (string) $req->getGet('filter_status');
        $filterProdi  = (string) $req->getGet('filter_prodi');
        $filterFrom   = (string) $req->getGet('filter_from');
        $filterUntil  = (string) $req->getGet('filter_until');

        $isManager = $this->canManageAll();

        $colMap = [
            1 => 'c.request_code',
            2 => 'u.username',
            3 => 'c.prodi',
            4 => 'l.name',
            5 => 'c.status',
            6 => 'c.submitted_at',
        ];
        $orderField = $colMap[$orderCol] ?? 'c.submitted_at';

        $db = db_connect();

        $base = $db->table('lab_clearance_requests c')
            ->select('c.public_id, c.request_code, c.prodi, c.lab_id, c.status, c.submitted_at, u.username AS requester_name, l.name AS lab_name')
            ->join('users u', 'u.id = c.requester_id', 'left')
            ->join('labs l', 'l.id = c.lab_id', 'left');

        if (! $isManager) {
            $base->where('c.requester_id', auth()->id());
        }

        $recordsTotal = (clone $base)->countAllResults(false);

        if ($search !== '') {
            $base->groupStart()
                ->like('c.request_code', $search)
                ->orLike('c.prodi', $search)
                ->orLike('u.username', $search)
                ->orLike('l.name', $search)
                ->groupEnd();
        }
        if ($filterStatus !== '' && in_array($filterStatus, [self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELED], true)) {
            $base->where('c.status', $filterStatus);
        }
        if ($filterProdi !== '') {
            $base->where('c.prodi', $filterProdi);
        }
        if ($filterFrom !== '') {
            $base->where('DATE(c.submitted_at) >=', $filterFrom);
        }
        if ($filterUntil !== '') {
            $base->where('DATE(c.submitted_at) <=', $filterUntil);
        }

        $recordsFiltered = (clone $base)->countAllResults(false);

        $rows = $base->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $statusMap = [
            self::STATUS_SUBMITTED => ['label' => 'Diajukan',  'badge' => 'badge-warning', 'icon' => 'fa-clock'],
            self::STATUS_APPROVED  => ['label' => 'Terbit',     'badge' => 'badge-success', 'icon' => 'fa-check-circle'],
            self::STATUS_REJECTED  => ['label' => 'Ditolak',    'badge' => 'badge-danger',  'icon' => 'fa-times-circle'],
            self::STATUS_CANCELED  => ['label' => 'Dibatalkan', 'badge' => 'badge-dark',    'icon' => 'fa-ban'],
        ];

        $data = [];
        foreach ($rows as $r) {
            $s = $statusMap[$r['status']] ?? ['label' => $r['status'], 'badge' => 'badge-secondary', 'icon' => 'fa-circle'];

            $statusHtml = '<span class="badge ' . $s['badge'] . '"><i class="fas ' . $s['icon'] . ' mr-1"></i>' . esc($s['label']) . '</span>';
            $submitted  = $r['submitted_at'] ? date('d M Y H:i', strtotime($r['submitted_at'])) : '-';
            $detailUrl  = base_url('clearance/' . $r['public_id']);
            $actionHtml = '<a href="' . $detailUrl . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Detail</a>';

            $data[] = [
                '', // row number (client-side)
                '<span class="font-weight-bold">' . esc($r['request_code']) . '</span>',
                esc($r['requester_name'] ?? '-'),
                esc($r['prodi'] ?? '-'),
                esc($r['lab_name'] ?? 'Semua Lab'),
                $statusHtml,
                '<span class="small">' . esc($submitted) . '</span>',
                $actionHtml,
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
     * Export riwayat surat bebas lab → Excel (SpreadsheetML)
     * GET /clearance/export
     */
    public function exportHistory()
    {
        if (! activeGroupCan('clearance.request.track')) {
            return redirect()->to('/clearance')->with('error', 'Akses ditolak.');
        }

        $req          = $this->request;
        $filterStatus = (string) ($req->getGet('filter_status') ?? '');
        $filterProdi  = (string) ($req->getGet('filter_prodi')  ?? '');
        $filterFrom   = (string) ($req->getGet('filter_from')   ?? '');
        $filterUntil  = (string) ($req->getGet('filter_until')  ?? '');

        $isManager = $this->canManageAll();

        $db = db_connect();

        $qb = $db->table('lab_clearance_requests c')
            ->select('c.request_code, c.prodi, c.status, c.submitted_at, c.verified_at,
                      c.letter_number, c.letter_issued_at,
                      u.username AS requester_name,
                      l.name AS lab_name,
                      v.username AS verified_by_name')
            ->join('users u', 'u.id = c.requester_id', 'left')
            ->join('labs l', 'l.id = c.lab_id', 'left')
            ->join('users v', 'v.id = c.verified_by', 'left');

        if (! $isManager) {
            $qb->where('c.requester_id', auth()->id());
        }
        if ($filterStatus !== '' && in_array($filterStatus, [self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELED], true)) {
            $qb->where('c.status', $filterStatus);
        }
        if ($filterProdi !== '') {
            $qb->where('c.prodi', $filterProdi);
        }
        if ($filterFrom !== '') {
            $qb->where('DATE(c.submitted_at) >=', $filterFrom);
        }
        if ($filterUntil !== '') {
            $qb->where('DATE(c.submitted_at) <=', $filterUntil);
        }

        $rows = $qb->orderBy('c.submitted_at', 'DESC')->get()->getResultArray();

        $statusLabels = [
            self::STATUS_SUBMITTED => 'Diajukan',
            self::STATUS_APPROVED  => 'Terbit',
            self::STATUS_REJECTED  => 'Ditolak',
            self::STATUS_CANCELED  => 'Dibatalkan',
        ];

        $headers = ['No', 'Kode', 'Pemohon', 'Prodi', 'Lab', 'Status', 'Diajukan', 'Diverifikasi', 'Diverifikasi Oleh', 'Nomor Surat'];
        if (! $isManager) {
            // Sembunyikan kolom pemohon & verifikator untuk non-manager
            $headers = ['No', 'Kode', 'Prodi', 'Lab', 'Status', 'Diajukan', 'Diverifikasi', 'Nomor Surat'];
        }

        $sheetXml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sheetXml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $sheetXml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $sheetXml .= '<Worksheet ss:Name="Surat Bebas Lab"><Table>' . "\n";

        $sheetXml .= '<Row>';
        foreach ($headers as $h) {
            $sheetXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
        }
        $sheetXml .= '</Row>' . "\n";

        foreach ($rows as $no => $row) {
            $status    = $row['status'] ?? '';
            $submitted = $row['submitted_at'] ? date('Y-m-d H:i', strtotime($row['submitted_at'])) : '';
            $verified  = $row['verified_at']  ? date('Y-m-d H:i', strtotime($row['verified_at']))  : '';

            if ($isManager) {
                $cells = [
                    $no + 1,
                    $row['request_code']     ?? '',
                    $row['requester_name']   ?? '',
                    $row['prodi']            ?? '',
                    $row['lab_name']         ?? 'Semua Lab',
                    $statusLabels[$status]   ?? $status,
                    $submitted,
                    $verified,
                    $row['verified_by_name'] ?? '',
                    $row['letter_number']    ?? '',
                ];
            } else {
                $cells = [
                    $no + 1,
                    $row['request_code']   ?? '',
                    $row['prodi']          ?? '',
                    $row['lab_name']       ?? 'Semua Lab',
                    $statusLabels[$status] ?? $status,
                    $submitted,
                    $verified,
                    $row['letter_number']  ?? '',
                ];
            }

            $sheetXml .= '<Row>';
            foreach ($cells as $cell) {
                $t = is_numeric($cell) ? 'Number' : 'String';
                $sheetXml .= '<Cell><Data ss:Type="' . $t . '">' . htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            }
            $sheetXml .= '</Row>' . "\n";
        }

        $sheetXml .= '</Table></Worksheet></Workbook>';

        $filename = 'surat-bebas-lab-' . date('Ymd-His');

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($sheetXml);
    }

    public function create()
    {
        if (! activeGroupIs('mahasiswa')) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan surat bebas lab hanya untuk mahasiswa.');
        }

        if ($redirect = $this->redirectIfProfileIncomplete()) {
            return $redirect;
        }

        $userId  = (int) auth()->id();
        $user    = auth()->user();
        $profile = (new UserProfileModel())->where('user_id', $userId)->first();

        $studyPrograms = (new StudyProgramModel())
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $labs = $this->labModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $outstanding = $this->checkOutstanding($userId);

        $prefill = [
            'applicant_name' => $user->username ?? '',
            'nim_nik'        => $profile['nim_nik'] ?? '',
            'phone'          => $profile['phone'] ?? '',
            'email'          => $user->email ?? '',
            'prodi'          => $profile['prodi'] ?? '',
        ];

        return $this->renderView('clearance/create', [
            'title'         => 'Ajukan Surat Bebas Lab',
            'page_title'    => 'Form Pengajuan Surat Bebas Lab',
            'studyPrograms' => $studyPrograms,
            'labs'          => $labs,
            'prefill'       => $prefill,
            'outstanding'   => $outstanding,
        ]);
    }

    public function store()
    {
        if (! activeGroupIs('mahasiswa')) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan surat bebas lab hanya untuk mahasiswa.');
        }

        if ($redirect = $this->redirectIfProfileIncomplete()) {
            return $redirect;
        }

        $rules = [
            'applicant_name' => 'required|min_length[3]|max_length[150]',
            'nim_nik'        => 'required|max_length[50]',
            'phone'          => 'required|max_length[30]',
            'prodi'          => 'required|max_length[150]',
            'address'        => 'required|min_length[5]',
            'thesis_title'   => 'required|min_length[5]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = (int) auth()->id();
        $user   = auth()->user();

        $labId = $this->request->getPost('lab_id');
        $labId = ($labId !== null && $labId !== '') ? (int) $labId : null;
        if ($labId !== null && ! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab yang dipilih tidak valid.');
        }

        $outstanding = $this->checkOutstanding($userId);

        $code = $this->generateRequestCode();

        $this->clearanceModel->insert([
            'request_code'         => $code,
            'requester_id'         => $userId,
            'lab_id'               => $labId,
            'purpose'              => trim((string) $this->request->getPost('purpose')) ?: 'Syarat Yudisium/Kelulusan',
            'applicant_name'       => trim((string) $this->request->getPost('applicant_name')),
            'nim_nik'              => trim((string) $this->request->getPost('nim_nik')),
            'phone'                => trim((string) $this->request->getPost('phone')),
            'email'                => $user->email ?? null,
            'prodi'                => trim((string) $this->request->getPost('prodi')),
            'address'              => trim((string) $this->request->getPost('address')),
            'thesis_title'         => trim((string) $this->request->getPost('thesis_title')),
            'note'                 => trim((string) $this->request->getPost('note')) ?: null,
            'status'               => self::STATUS_SUBMITTED,
            'submitted_at'         => Time::now()->toDateTimeString(),
            'outstanding_snapshot' => json_encode($outstanding),
        ]);

        $created   = $this->clearanceModel->find($this->clearanceModel->getInsertID());
        $publicId  = $created['public_id'];

        // Keep profile in sync with the latest applicant data
        (new UserProfileModel())->upsert($userId, [
            'nim_nik' => trim((string) $this->request->getPost('nim_nik')),
            'phone'   => trim((string) $this->request->getPost('phone')),
            'prodi'   => trim((string) $this->request->getPost('prodi')),
        ]);

        notify_role('laboran', 'clearance.submitted', [
            'request_code' => $code,
            'url'          => '/clearance/' . $publicId,
        ]);

        return redirect()->to('/clearance/' . $publicId)
            ->with('success', 'Pengajuan surat bebas lab berhasil dikirim. Menunggu verifikasi laboran.');
    }

    public function show(string $publicId)
    {
        $request = $this->findRequest($publicId);
        if (! $request) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan tidak ditemukan.');
        }

        if (! $this->canAccess($request)) {
            return redirect()->to('/clearance')->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $outstanding = $request['outstanding_snapshot']
            ? json_decode($request['outstanding_snapshot'], true)
            : ['clear' => true, 'items' => []];

        // Refresh live outstanding for managers while still submitted
        $liveOutstanding = null;
        if ($this->canManageAll() && $request['status'] === self::STATUS_SUBMITTED) {
            $liveOutstanding = $this->checkOutstanding((int) $request['requester_id']);
        }

        return $this->renderView('clearance/show', [
            'title'           => 'Detail Surat Bebas Lab',
            'page_title'      => 'Detail Pengajuan ' . $request['request_code'],
            'request'         => $request,
            'outstanding'     => $outstanding,
            'liveOutstanding' => $liveOutstanding,
            'isManager'       => $this->canManageAll(),
            'canVerify'       => activeGroupCan('clearance.verify'),
            'isOwner'         => (int) $request['requester_id'] === (int) auth()->id(),
        ]);
    }

    public function cancel(string $publicId)
    {
        $request = $this->clearanceModel->findByPublicId($publicId);
        if (! $request) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan tidak ditemukan.');
        }

        if ((int) $request['requester_id'] !== (int) auth()->id()) {
            return redirect()->to('/clearance')->with('error', 'Anda tidak dapat membatalkan pengajuan ini.');
        }

        if ($request['status'] !== self::STATUS_SUBMITTED) {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Hanya pengajuan berstatus diajukan yang dapat dibatalkan.');
        }

        $this->clearanceModel->update($request['id'], [
            'status'        => self::STATUS_CANCELED,
            'cancel_reason' => trim((string) $this->request->getPost('cancel_reason')) ?: 'Dibatalkan pemohon.',
            'canceled_by'   => auth()->id(),
            'canceled_at'   => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/clearance/' . $publicId)->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    public function approve(string $publicId)
    {
        if (! activeGroupCan('clearance.verify')) {
            return redirect()->to('/clearance')->with('error', 'Anda tidak memiliki izin verifikasi.');
        }

        $request = $this->clearanceModel->findByPublicId($publicId);
        if (! $request || $request['status'] !== self::STATUS_SUBMITTED) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan tidak valid untuk diverifikasi.');
        }

        // Laboran must explicitly confirm the applicant has no outstanding obligations
        if (! $this->request->getPost('confirm_clear')) {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Harap konfirmasi bahwa pemohon telah bebas tanggungan lab.');
        }

        $externalUrl = trim((string) $this->request->getPost('letter_external_url'));
        $file        = $this->request->getFile('letter_file');
        $hasFile     = $file !== null && $file->isValid() && ! $file->hasMoved();

        if (! $hasFile && $externalUrl === '') {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Lampirkan file surat atau isi tautan surat (URL).');
        }

        if ($externalUrl !== '' && ! filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Tautan surat (URL) tidak valid.');
        }

        $filePath = null;
        if ($hasFile) {
            try {
                $meta     = handleDocumentUpload($file, 'clearance_letters/' . $request['id']);
                $filePath = $meta['path'] ?? null;
            } catch (\RuntimeException $e) {
                return redirect()->to('/clearance/' . $publicId)->with('error', $e->getMessage());
            }
        }

        $letterNumber = trim((string) $this->request->getPost('letter_number'));
        if ($letterNumber === '') {
            $letterNumber = $this->generateLetterNumber();
        }

        $this->clearanceModel->update($request['id'], [
            'status'              => self::STATUS_APPROVED,
            'verified_by'         => auth()->id(),
            'verified_note'       => trim((string) $this->request->getPost('verified_note')) ?: 'Diverifikasi laboran.',
            'verified_at'         => Time::now()->toDateTimeString(),
            'letter_number'       => $letterNumber,
            'letter_file_path'    => $filePath,
            'letter_external_url' => $externalUrl !== '' ? $externalUrl : null,
            'letter_issued_at'    => Time::now()->toDateTimeString(),
        ]);

        // Transition requester from mahasiswa to alumni (read-only history access)
        $this->transitionToAlumni((int) $request['requester_id']);

        send_notification((int) $request['requester_id'], 'clearance.approved', [
            'request_code'  => $request['request_code'],
            'letter_number' => $letterNumber,
            'url'           => '/clearance/' . $publicId,
        ]);

        return redirect()->to('/clearance/' . $publicId)->with('success', 'Surat bebas lab berhasil diterbitkan.');
    }

    public function reject(string $publicId)
    {
        if (! activeGroupCan('clearance.verify')) {
            return redirect()->to('/clearance')->with('error', 'Anda tidak memiliki izin verifikasi.');
        }

        $request = $this->clearanceModel->findByPublicId($publicId);
        if (! $request || $request['status'] !== self::STATUS_SUBMITTED) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan tidak valid untuk ditolak.');
        }

        $reason = trim((string) $this->request->getPost('rejected_reason'));
        if ($reason === '') {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->clearanceModel->update($request['id'], [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'verified_by'     => auth()->id(),
            'verified_at'     => Time::now()->toDateTimeString(),
        ]);

        send_notification((int) $request['requester_id'], 'clearance.rejected', [
            'request_code' => $request['request_code'],
            'reason'       => $reason,
            'url'          => '/clearance/' . $publicId,
        ]);

        return redirect()->to('/clearance/' . $publicId)->with('success', 'Pengajuan telah ditolak.');
    }

    public function download(string $publicId)
    {
        $request = $this->clearanceModel->findByPublicId($publicId);
        if (! $request) {
            return redirect()->to('/clearance')->with('error', 'Pengajuan tidak ditemukan.');
        }

        if (! $this->canAccess($request)) {
            return redirect()->to('/clearance')->with('error', 'Anda tidak memiliki akses ke surat ini.');
        }

        if ($request['status'] !== self::STATUS_APPROVED) {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Surat belum diterbitkan.');
        }

        // External URL takes the user straight to the hosted document
        if (empty($request['letter_file_path']) && ! empty($request['letter_external_url'])) {
            return redirect()->to($request['letter_external_url']);
        }

        $fullPath = WRITEPATH . $request['letter_file_path'];
        if (empty($request['letter_file_path']) || ! is_file($fullPath)) {
            return redirect()->to('/clearance/' . $publicId)->with('error', 'Berkas surat tidak ditemukan.');
        }

        return $this->response->download($fullPath, null);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function findRequest(string $publicId): ?array
    {
        return db_connect()->table('lab_clearance_requests c')
            ->select('c.*, u.username AS requester_name, l.name AS lab_name, v.username AS verifier_name')
            ->join('users u', 'u.id = c.requester_id', 'left')
            ->join('labs l', 'l.id = c.lab_id', 'left')
            ->join('users v', 'v.id = c.verified_by', 'left')
            ->where('c.public_id', $publicId)
            ->get()->getRowArray();
    }

    private function generateRequestCode(): string
    {
        return 'SBL-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    private function generateLetterNumber(): string
    {
        $roman = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $seq   = $this->clearanceModel
            ->where('status', self::STATUS_APPROVED)
            ->where('YEAR(letter_issued_at)', date('Y'))
            ->countAllResults() + 1;

        return sprintf('%03d/SBL/%s/%s', $seq, $roman[(int) date('n')], date('Y'));
    }

    /**
     * Detect unreturned / problematic loans for the applicant.
     *
     * @return array{clear:bool,items:array<int,array<string,mixed>>}
     */
    private function checkOutstanding(int $userId): array
    {
        $rows = db_connect()->table('loan_requests lr')
            ->select('lr.id, lr.status, lr.qty, a.name AS asset_name, l.name AS lab_name')
            ->join('lab_assets a', 'a.id = lr.asset_id', 'left')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->where('lr.requester_id', $userId)
            ->whereIn('lr.status', ['borrowed', 'late', 'problematic'])
            ->get()->getResultArray();

        return [
            'clear' => count($rows) === 0,
            'items' => $rows,
        ];
    }

    private function transitionToAlumni(int $userId): void
    {
        $userModel = auth()->getProvider();
        $user      = $userModel->findById($userId);
        if ($user === null) {
            return;
        }

        // Only transition users who are still mahasiswa
        if (! in_array('mahasiswa', $user->getGroups(), true)) {
            return;
        }

        $user->removeGroup('mahasiswa');
        $user->addGroup('alumni');
    }

    private function canManageAll(): bool
    {
        return activeGroupCan('clearance.request.manage-all')
            || activeGroupCan('clearance.verify');
    }

    private function canAccess(array $request): bool
    {
        if ($this->canManageAll()) {
            return true;
        }

        return (int) $request['requester_id'] === (int) auth()->id();
    }

    private function redirectIfProfileIncomplete()
    {
        $profile = (new UserProfileModel())->getByUserId((int) auth()->id());

        $nimNIK = trim((string) ($profile['nim_nik'] ?? ''));
        $phone  = trim((string) ($profile['phone'] ?? ''));
        $prodi  = trim((string) ($profile['prodi'] ?? ''));

        if ($nimNIK !== '' && $phone !== '' && $prodi !== '') {
            return null;
        }

        return redirect()->to('/profile')
            ->with('error', 'Lengkapi data profil terlebih dahulu (program studi, NIM, nomor telp) sebelum membuat pengajuan surat bebas lab.');
    }
}
