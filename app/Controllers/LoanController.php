<?php

namespace App\Controllers;

use App\Models\AssetMovementModel;
use App\Models\LabAssetModel;
use App\Models\LoanRequestLogModel;
use App\Models\LoanRequestModel;
use CodeIgniter\I18n\Time;

class LoanController extends BaseController
{
    private const STATUS_WAITING_L1 = 'waiting_l1';
    private const STATUS_WAITING_L2 = 'waiting_l2';
    private const STATUS_APPROVED   = 'approved_waiting_pickup';
    private const STATUS_BORROWED   = 'borrowed';
    private const STATUS_RETURNED   = 'returned';
    private const STATUS_REJECTED   = 'rejected';
    private const STATUS_CANCELED   = 'canceled';
    private const STATUS_LATE       = 'late';
    private const STATUS_ISSUE      = 'problematic';

    protected LabAssetModel $assetModel;
    protected LoanRequestModel $loanModel;
    protected LoanRequestLogModel $logModel;
    protected AssetMovementModel $movementModel;

    public function __construct()
    {
        $this->assetModel    = new LabAssetModel();
        $this->loanModel     = new LoanRequestModel();
        $this->logModel      = new LoanRequestLogModel();
        $this->movementModel = new AssetMovementModel();
    }

    public function catalog()
    {
        $assets = db_connect()->table('lab_assets a')
            ->select('a.*, l.name AS lab_name, l.location AS lab_location')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->where('a.is_active', 1)
            ->where('a.asset_type', 'equipment')
            ->where('a.is_loanable', 1)
            ->where('a.condition_status', 'baik')
            ->orderBy('a.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/catalog', [
            'title'      => 'Katalog Lab',
            'page_title' => 'Katalog Lab & Alat',
            'assets'     => $assets,
        ]);
    }

    public function index()
    {
        $this->syncLateStatuses();

        $builder = db_connect()->table('loan_requests lr')
            ->select('lr.*, u.username AS requester_name, a.name AS asset_name, a.asset_type, l.name AS lab_name')
            ->join('users u', 'u.id = lr.requester_id', 'left')
            ->join('lab_assets a', 'a.id = lr.asset_id', 'left')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->orderBy('lr.created_at', 'DESC');

        if (! $this->canManageGlobal()) {
            $builder->where('lr.requester_id', auth()->id());
        }

        $loans = $builder->get()->getResultArray();

        return $this->renderView('loans/index', [
            'title'      => 'Peminjaman Lab',
            'page_title' => 'Daftar Permohonan Peminjaman',
            'loans'      => $loans,
        ]);
    }

    public function create()
    {
        $assets = db_connect()->table('lab_assets a')
            ->select('a.*, l.name AS lab_name')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->where('a.is_active', 1)
            ->where('a.asset_type', 'equipment')
            ->where('a.is_loanable', 1)
            ->where('a.condition_status', 'baik')
            ->where('a.stock_available >', 0)
            ->orderBy('a.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/create', [
            'title'      => 'Ajukan Peminjaman',
            'page_title' => 'Form Permohonan Peminjaman',
            'assets'     => $assets,
        ]);
    }

    public function store()
    {
        $rules = [
            'asset_id'   => 'required|is_natural_no_zero',
            'qty'        => 'required|is_natural_no_zero',
            'pickup_at'  => 'required',
            'return_at'  => 'required',
            'purpose'    => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $asset = $this->assetModel->find((int) $this->request->getPost('asset_id'));
        if (! $asset || (int) $asset['is_active'] !== 1) {
            return redirect()->back()->withInput()->with('error', 'Aset tidak ditemukan atau tidak aktif.');
        }

        if ((int) ($asset['is_loanable'] ?? 0) !== 1 || (string) ($asset['condition_status'] ?? '') !== 'baik') {
            return redirect()->back()->withInput()->with('error', 'Aset tidak memenuhi syarat untuk dipinjam.');
        }

        $qty = (int) $this->request->getPost('qty');
        if ($qty > (int) $asset['stock_available']) {
            return redirect()->back()->withInput()->with('error', 'Jumlah melebihi stok tersedia.');
        }

        $pickupAt = date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('pickup_at')));
        $returnAt = date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('return_at')));

        if ($pickupAt >= $returnAt) {
            return redirect()->back()->withInput()->with('error', 'Waktu kembali harus setelah waktu ambil.');
        }

        $requestCode = 'LOAN-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $payload = [
            'request_code'         => $requestCode,
            'requester_id'         => auth()->id(),
            'asset_id'             => (int) $this->request->getPost('asset_id'),
            'qty'                  => $qty,
            'purpose'              => trim((string) $this->request->getPost('purpose')),
            'supporting_document'  => trim((string) $this->request->getPost('supporting_document')) ?: null,
            'pickup_at'            => $pickupAt,
            'return_at'            => $returnAt,
            'requires_l2'          => $this->request->getPost('requires_l2') ? 1 : 0,
            'status'               => self::STATUS_WAITING_L1,
        ];

        $this->loanModel->insert($payload);
        $loanId = (int) $this->loanModel->getInsertID();

        $this->addLog($loanId, 'request_created', 'Permohonan dibuat oleh peminjam.');

        return redirect()->to('/loans')->with('success', 'Permohonan peminjaman berhasil dibuat.');
    }

    public function show(int $id)
    {
        $loan = $this->findLoan($id);
        if (! $loan) {
            return redirect()->to('/loans')->with('error', 'Data peminjaman tidak ditemukan.');
        }

        if (! $this->canAccessLoan($loan)) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki akses ke data ini.');
        }

        $logs = $this->logModel->where('loan_request_id', $id)->orderBy('id', 'DESC')->findAll();

        return $this->renderView('loans/show', [
            'title'      => 'Detail Peminjaman',
            'page_title' => 'Detail ' . $loan['request_code'],
            'loan'       => $loan,
            'logs'       => $logs,
        ]);
    }

    public function cancel(int $id)
    {
        $loan = $this->findLoan($id);
        if (! $loan) {
            return redirect()->to('/loans')->with('error', 'Data peminjaman tidak ditemukan.');
        }

        if ((int) $loan['requester_id'] !== (int) auth()->id() && ! activeGroupCan('lending.request.manage-all')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak bisa membatalkan permohonan ini.');
        }

        if (! in_array($loan['status'], [self::STATUS_WAITING_L1, self::STATUS_WAITING_L2], true)) {
            return redirect()->to('/loans/' . $id)->with('error', 'Status saat ini tidak bisa dibatalkan.');
        }

        $note = trim((string) $this->request->getPost('cancel_reason')) ?: 'Dibatalkan oleh peminjam.';

        $this->loanModel->update($id, [
            'status'        => self::STATUS_CANCELED,
            'cancel_reason' => $note,
            'canceled_by'   => auth()->id(),
            'canceled_at'   => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'request_canceled', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Permohonan berhasil dibatalkan.');
    }

    public function approveL1(int $id)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || $loan['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk approval level 1.');
        }

        $statusAfterL1 = ((int) $loan['requires_l2'] === 1) ? self::STATUS_WAITING_L2 : self::STATUS_APPROVED;
        $note          = trim((string) $this->request->getPost('approval_l1_note')) ?: 'Disetujui laboran.';

        $this->loanModel->update($id, [
            'status'           => $statusAfterL1,
            'approval_l1_by'   => auth()->id(),
            'approval_l1_note' => $note,
            'approval_l1_at'   => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'approved_l1', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Approval level 1 berhasil.');
    }

    public function rejectL1(int $id)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || $loan['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk ditolak pada level 1.');
        }

        $note = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak oleh laboran.';

        $this->loanModel->update($id, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $note,
            'approval_l1_by'  => auth()->id(),
            'approval_l1_note'=> $note,
            'approval_l1_at'  => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'rejected_l1', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Permohonan berhasil ditolak.');
    }

    public function approveL2(int $id)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || $loan['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk approval level 2.');
        }

        $note = trim((string) $this->request->getPost('approval_l2_note')) ?: 'Disetujui kepala lab.';

        $this->loanModel->update($id, [
            'status'           => self::STATUS_APPROVED,
            'approval_l2_by'   => auth()->id(),
            'approval_l2_note' => $note,
            'approval_l2_at'   => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'approved_l2', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Approval level 2 berhasil.');
    }

    public function rejectL2(int $id)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || $loan['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk ditolak pada level 2.');
        }

        $note = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak oleh kepala lab.';

        $this->loanModel->update($id, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $note,
            'approval_l2_by'  => auth()->id(),
            'approval_l2_note'=> $note,
            'approval_l2_at'  => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'rejected_l2', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Permohonan berhasil ditolak.');
    }

    public function checkout(int $id)
    {
        if (! activeGroupCan('lending.checkout')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin check-out.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || $loan['status'] !== self::STATUS_APPROVED) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk check-out.');
        }

        $asset = $this->assetModel->find((int) $loan['asset_id']);
        if (! $asset || (int) $asset['stock_available'] < (int) $loan['qty']) {
            return redirect()->to('/loans/' . $id)->with('error', 'Stok aset tidak mencukupi untuk check-out.');
        }

        $condition = trim((string) $this->request->getPost('checkout_condition')) ?: 'Baik';

        $this->loanModel->update($id, [
            'status'             => self::STATUS_BORROWED,
            'checkout_by'        => auth()->id(),
            'checkout_condition' => $condition,
            'checkout_at'        => Time::now()->toDateTimeString(),
        ]);

        $this->assetModel->update((int) $asset['id'], [
            'stock_available' => (int) $asset['stock_available'] - (int) $loan['qty'],
            'inventory_status' => 'dipinjam',
        ]);

        $this->movementModel->insert([
            'asset_id'       => (int) $asset['id'],
            'movement_type'  => 'borrow',
            'quantity'       => -1 * (int) $loan['qty'],
            'from_lab_id'    => (int) ($asset['lab_id'] ?? 0) ?: null,
            'to_lab_id'      => null,
            'reference_type' => 'loan_request',
            'reference_id'   => (int) $id,
            'movement_date'  => Time::now()->toDateTimeString(),
            'notes'          => 'Auto: check-out peminjaman. Kondisi awal: ' . $condition,
            'created_by'     => auth()->id(),
            'created_at'     => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'checkout', 'Check-out dilakukan. Kondisi awal: ' . $condition);

        return redirect()->to('/loans/' . $id)->with('success', 'Check-out berhasil diproses.');
    }

    public function checkin(int $id)
    {
        if (! activeGroupCan('lending.checkin')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin check-in.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan || ! in_array($loan['status'], [self::STATUS_BORROWED, self::STATUS_LATE], true)) {
            return redirect()->to('/loans')->with('error', 'Permohonan tidak valid untuk check-in.');
        }

        $asset = $this->assetModel->find((int) $loan['asset_id']);
        if (! $asset) {
            return redirect()->to('/loans/' . $id)->with('error', 'Aset tidak ditemukan.');
        }

        $condition = trim((string) $this->request->getPost('checkin_condition')) ?: 'Sesuai';
        $issueNote = trim((string) $this->request->getPost('issue_note'));

        $status = $issueNote !== '' ? self::STATUS_ISSUE : self::STATUS_RETURNED;

        $this->loanModel->update($id, [
            'status'            => $status,
            'checkin_by'        => auth()->id(),
            'checkin_condition' => $condition,
            'checkin_at'        => Time::now()->toDateTimeString(),
            'issue_flag'        => $issueNote !== '' ? 1 : 0,
            'issue_note'        => $issueNote !== '' ? $issueNote : null,
        ]);

        if (strtolower($condition) !== 'hilang') {
            $this->assetModel->update((int) $asset['id'], [
                'stock_available'  => (int) $asset['stock_available'] + (int) $loan['qty'],
                'inventory_status' => 'aktif',
            ]);
        } else {
            $this->assetModel->update((int) $asset['id'], [
                'inventory_status' => 'hilang',
            ]);
        }

        $this->movementModel->insert([
            'asset_id'       => (int) $asset['id'],
            'movement_type'  => strtolower($condition) === 'hilang' ? 'disposal' : 'return',
            'quantity'       => strtolower($condition) === 'hilang' ? -1 * (int) $loan['qty'] : (int) $loan['qty'],
            'from_lab_id'    => null,
            'to_lab_id'      => (int) ($asset['lab_id'] ?? 0) ?: null,
            'reference_type' => 'loan_request',
            'reference_id'   => (int) $id,
            'movement_date'  => Time::now()->toDateTimeString(),
            'notes'          => 'Auto: check-in peminjaman. Kondisi akhir: ' . $condition . ($issueNote ? ' | ' . $issueNote : ''),
            'created_by'     => auth()->id(),
            'created_at'     => Time::now()->toDateTimeString(),
        ]);

        $this->addLog($id, 'checkin', 'Check-in dilakukan. Kondisi akhir: ' . $condition . ($issueNote ? ' | Catatan: ' . $issueNote : ''));

        return redirect()->to('/loans/' . $id)->with('success', 'Check-in berhasil diproses.');
    }

    public function reportIssue(int $id)
    {
        if (! activeGroupCan('lending.issue.report')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin mencatat masalah.');
        }

        $loan = $this->loanModel->find($id);
        if (! $loan) {
            return redirect()->to('/loans')->with('error', 'Data peminjaman tidak ditemukan.');
        }

        $note = trim((string) $this->request->getPost('issue_note'));
        if ($note === '') {
            return redirect()->to('/loans/' . $id)->with('error', 'Catatan masalah wajib diisi.');
        }

        $this->loanModel->update($id, [
            'status'     => self::STATUS_ISSUE,
            'issue_flag' => 1,
            'issue_note' => $note,
        ]);

        $this->addLog($id, 'issue_reported', $note);

        return redirect()->to('/loans/' . $id)->with('success', 'Laporan masalah berhasil dicatat.');
    }

    public function analytics()
    {
        if (! activeGroupCan('lending.analytics.view')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin melihat analitik.');
        }

        $statusStats = db_connect()->table('loan_requests')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()->getResultArray();

        $assetStats = db_connect()->table('loan_requests lr')
            ->select('a.name AS asset_name, COUNT(*) AS usage_total')
            ->join('lab_assets a', 'a.id = lr.asset_id')
            ->groupBy('a.name')
            ->orderBy('usage_total', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->renderView('loans/analytics', [
            'title'       => 'Analitik Peminjaman',
            'page_title'  => 'Dasbor Pemanfaatan Lab',
            'statusStats' => $statusStats,
            'assetStats'  => $assetStats,
        ]);
    }

    private function findLoan(int $id): ?array
    {
        return db_connect()->table('loan_requests lr')
            ->select('lr.*, u.username AS requester_name, a.name AS asset_name, a.asset_type, a.category, l.name AS lab_name, l.location AS lab_location')
            ->join('users u', 'u.id = lr.requester_id', 'left')
            ->join('lab_assets a', 'a.id = lr.asset_id', 'left')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->where('lr.id', $id)
            ->get()->getRowArray();
    }

    private function canManageGlobal(): bool
    {
        return activeGroupCan('lending.request.manage-all')
            || activeGroupCan('lending.approval.l1')
            || activeGroupCan('lending.approval.l2')
            || activeGroupCan('lending.master.manage');
    }

    private function canAccessLoan(array $loan): bool
    {
        if ($this->canManageGlobal()) {
            return true;
        }

        return (int) $loan['requester_id'] === (int) auth()->id();
    }

    private function addLog(int $loanId, string $action, ?string $note = null): void
    {
        $this->logModel->insert([
            'loan_request_id' => $loanId,
            'action'          => $action,
            'note'            => $note,
            'actor_id'        => auth()->id(),
            'created_at'      => Time::now()->toDateTimeString(),
        ]);
    }

    private function syncLateStatuses(): void
    {
        $now = Time::now()->toDateTimeString();
        $lateLoans = $this->loanModel
            ->whereIn('status', [self::STATUS_BORROWED])
            ->where('return_at <', $now)
            ->findAll();

        foreach ($lateLoans as $loan) {
            $this->loanModel->update((int) $loan['id'], [
                'status'  => self::STATUS_LATE,
                'is_late' => 1,
            ]);

            $this->addLog((int) $loan['id'], 'marked_late', 'Sistem menandai peminjaman sebagai terlambat.');
        }
    }
}
