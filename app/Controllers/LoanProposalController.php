<?php

namespace App\Controllers;

use App\Models\LabAssetModel;
use App\Models\LabModel;
use App\Models\LoanProposalItemModel;
use App\Models\LoanProposalModel;
use CodeIgniter\I18n\Time;

class LoanProposalController extends BaseController
{
    private const STATUS_DRAFT      = 'draft';
    private const STATUS_WAITING_L1 = 'waiting_l1';
    private const STATUS_WAITING_L2 = 'waiting_l2';
    private const STATUS_APPROVED   = 'approved';
    private const STATUS_REJECTED   = 'rejected';
    private const STATUS_CANCELED   = 'canceled';

    protected LoanProposalModel $proposalModel;
    protected LoanProposalItemModel $itemModel;
    protected LabAssetModel $assetModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->proposalModel = new LoanProposalModel();
        $this->itemModel     = new LoanProposalItemModel();
        $this->assetModel    = new LabAssetModel();
        $this->labModel      = new LabModel();
    }

    public function index()
    {
        $builder = db_connect()->table('loan_proposals p')
            ->select('p.*, u.username AS proposer_name, COUNT(i.id) AS total_items')
            ->join('users u', 'u.id = p.proposer_id', 'left')
            ->join('loan_proposal_items i', 'i.proposal_id = p.id', 'left')
            ->groupBy('p.id')
            ->orderBy('p.created_at', 'DESC');

        if (! $this->canManageGlobal()) {
            $builder->where('p.proposer_id', auth()->id());
        }

        $proposals = $builder->get()->getResultArray();

        return $this->renderView('loans/index', [
            'title'      => 'Peminjaman Lab',
            'page_title' => 'Daftar Proposal Peminjaman',
            'proposals'  => $proposals,
        ]);
    }

    public function create()
    {
        $type = $this->request->getGet('type');
        if ($type !== null && ! in_array($type, ['equipment', 'lab'], true)) {
            $type = null;
        }

        return $this->renderView('loans/create', [
            'title'      => 'Buat Proposal',
            'page_title' => $type === null ? 'Buat Proposal Peminjaman' : ($type === 'equipment' ? 'Proposal Peminjaman Alat' : 'Proposal Peminjaman Lab'),
            'type'       => $type,
        ]);
    }

    public function store()
    {
        $loanType = $this->request->getPost('loan_type');
        if (! in_array($loanType, ['equipment', 'lab'], true)) {
            return redirect()->back()->withInput()->with('error', 'Tipe peminjaman tidak valid. Pilih Alat atau Laboratorium.');
        }

        $rules = [
            'title'     => 'required|min_length[5]',
            'objective' => 'required|min_length[10]',
            'start_at'  => 'required',
            'end_at'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $startAt = date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('start_at')));
        $endAt   = date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('end_at')));

        if ($startAt >= $endAt) {
            return redirect()->back()->withInput()->with('error', 'Waktu selesai harus setelah waktu mulai.');
        }

        $code = 'PROP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $this->proposalModel->insert([
            'proposal_code' => $code,
            'proposer_id'   => auth()->id(),
            'loan_type'     => $loanType,
            'title'         => trim((string) $this->request->getPost('title')),
            'objective'     => trim((string) $this->request->getPost('objective')),
            'start_at'      => $startAt,
            'end_at'        => $endAt,
            'requires_l2'   => $this->request->getPost('requires_l2') ? 1 : 0,
            'status'        => self::STATUS_DRAFT,
        ]);

        $proposalId = (int) $this->proposalModel->getInsertID();

        return redirect()->to('/loans/' . $proposalId . '/items')->with('success', 'Proposal berhasil dibuat. Pilih item yang akan dipinjam.');
    }

    public function show(int $id)
    {
        $proposal = $this->findProposal($id);
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        if (! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki akses ke proposal ini.');
        }

        // Draft proposals belong on the item-selection page
        if ($proposal['status'] === self::STATUS_DRAFT) {
            return redirect()->to('/loans/' . $id . '/items');
        }

        $items = db_connect()->table('loan_proposal_items i')
            ->select('i.*, a.name AS equipment_name, l.name AS lab_name')
            ->join('lab_assets a', 'a.id = i.equipment_id', 'left')
            ->join('labs l', 'l.id = i.lab_id', 'left')
            ->where('i.proposal_id', $id)
            ->orderBy('i.id', 'ASC')
            ->get()->getResultArray();

        // Resolve actor usernames for timeline
        $actorIds = array_values(array_unique(array_filter([
            $proposal['approval_l1_by'] ?? null,
            $proposal['approval_l2_by'] ?? null,
            $proposal['canceled_by']    ?? null,
        ])));
        $actorNames = [];
        if (! empty($actorIds)) {
            $actors = db_connect()->table('users')
                ->select('id, username')
                ->whereIn('id', $actorIds)
                ->get()->getResultArray();
            foreach ($actors as $a) {
                $actorNames[(int) $a['id']] = $a['username'];
            }
        }

        return $this->renderView('loans/show', [
            'title'      => 'Detail Proposal',
            'page_title' => 'Proposal: ' . $proposal['proposal_code'],
            'proposal'   => $proposal,
            'items'      => $items,
            'actorNames' => $actorNames,
        ]);
    }

    public function selectItems(int $id)
    {
        $proposal = $this->findProposal($id);
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        if (! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki akses ke proposal ini.');
        }

        // Only draft proposals allow item selection
        if ($proposal['status'] !== self::STATUS_DRAFT) {
            return redirect()->to('/loans/' . $id);
        }

        $items = db_connect()->table('loan_proposal_items i')
            ->select('i.*, a.name AS equipment_name, l.name AS lab_name')
            ->join('lab_assets a', 'a.id = i.equipment_id', 'left')
            ->join('labs l', 'l.id = i.lab_id', 'left')
            ->where('i.proposal_id', $id)
            ->orderBy('i.id', 'DESC')
            ->get()->getResultArray();

        $loanType            = $proposal['loan_type'] ?? 'equipment';
        $availableEquipments = [];
        $availableLabs       = [];

        if ($loanType === 'equipment') {
            $availableEquipments = db_connect()->table('lab_assets a')
                ->select('a.id, a.name, a.category, a.photo, a.specifications, a.stock_available, a.stock_total, l.name AS lab_name, l.location AS lab_location')
                ->join('labs l', 'l.id = a.lab_id', 'left')
                ->where('a.is_active', 1)
                ->where('a.asset_type', 'equipment')
                ->where('a.is_loanable', 1)
                ->where('a.condition_status', 'baik')
                ->where('a.stock_available >', 0)
                ->orderBy('a.name', 'ASC')
                ->get()->getResultArray();
        } else {
            $availableLabs = db_connect()->table('labs l')
                ->select('l.id, l.name, l.code, l.location, l.capacity, l.logo, l.condition_status, f.name AS faculty_name')
                ->join('faculties f', 'f.id = l.faculty_id', 'left')
                ->where('l.is_active', 1)
                ->where('l.is_loanable', 1)
                ->where('l.condition_status', 'baik')
                ->orderBy('l.name', 'ASC')
                ->get()->getResultArray();
        }

        return $this->renderView('loans/select_items', [
            'title'               => 'Pilih Item',
            'page_title'          => 'Step 2 — Pilih ' . ($loanType === 'equipment' ? 'Alat' : 'Lab') . ': ' . $proposal['proposal_code'],
            'proposal'            => $proposal,
            'items'               => $items,
            'availableEquipments' => $availableEquipments,
            'availableLabs'       => $availableLabs,
        ]);
    }

    public function addEquipmentItem(int $id)
    {
        $proposal = $this->proposalModel->find($id);
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $equipmentId = (int) $this->request->getPost('equipment_id');
        $qty         = max(1, (int) $this->request->getPost('qty'));
        $note        = trim((string) $this->request->getPost('note')) ?: null;

        if (($proposal['loan_type'] ?? '') !== 'equipment') {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Proposal ini adalah proposal peminjaman lab, bukan alat.');
        }

        $equipment = $this->assetModel->find($equipmentId);
        if (! $equipment || (int) $equipment['is_active'] !== 1 || $equipment['asset_type'] !== 'equipment') {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Data alat tidak valid.');
        }

        if ((int) ($equipment['is_loanable'] ?? 0) !== 1) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Alat tidak bisa dipinjam saat ini.');
        }

        if ((string) ($equipment['condition_status'] ?? '') !== 'baik') {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Kondisi alat tidak memenuhi syarat untuk dipinjam.');
        }

        if ($qty > (int) $equipment['stock_available']) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Jumlah alat melebihi stok tersedia.');
        }

        $this->itemModel->insert([
            'proposal_id'   => $id,
            'item_type'     => 'equipment',
            'equipment_id'  => $equipmentId,
            'lab_id'        => null,
            'qty'           => $qty,
            'note'          => $note,
        ]);

        return redirect()->to('/loans/' . $id . '/items')->with('success', 'Item alat berhasil ditambahkan ke proposal.');
    }

    public function addLabItem(int $id)
    {
        $proposal = $this->proposalModel->find($id);
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $labId = (int) $this->request->getPost('lab_id');
        $note  = trim((string) $this->request->getPost('note')) ?: null;

        if (($proposal['loan_type'] ?? '') !== 'lab') {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Proposal ini adalah proposal peminjaman alat, bukan lab.');
        }

        $lab = $this->labModel->find($labId);
        if (! $lab || (int) $lab['is_active'] !== 1) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Data lab tidak valid.');
        }

        if ((int) ($lab['is_loanable'] ?? 0) !== 1) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Lab tidak bisa dipinjam saat ini.');
        }

        if ((string) ($lab['condition_status'] ?? '') !== 'baik') {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Kondisi lab tidak memenuhi syarat untuk dipinjam.');
        }

        $alreadyExists = $this->itemModel
            ->where('proposal_id', $id)
            ->where('item_type', 'lab')
            ->where('lab_id', $labId)
            ->countAllResults() > 0;

        if ($alreadyExists) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Lab tersebut sudah ditambahkan ke proposal.');
        }

        $this->itemModel->insert([
            'proposal_id'  => $id,
            'item_type'    => 'lab',
            'equipment_id' => null,
            'lab_id'       => $labId,
            'qty'          => 1,
            'note'         => $note,
        ]);

        return redirect()->to('/loans/' . $id . '/items')->with('success', 'Item lab berhasil ditambahkan ke proposal.');
    }

    public function removeItem(int $id, int $itemId)
    {
        $proposal = $this->proposalModel->find($id);
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $item = $this->itemModel->find($itemId);
        if (! $item || (int) $item['proposal_id'] !== $id) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Item tidak ditemukan pada proposal ini.');
        }

        $this->itemModel->delete($itemId);

        return redirect()->to('/loans/' . $id . '/items')->with('success', 'Item berhasil dihapus dari proposal.');
    }

    public function submit(int $id)
    {
        $proposal = $this->proposalModel->find($id);
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat dikirim.');
        }

        $itemCount = $this->itemModel->where('proposal_id', $id)->countAllResults();
        if ($itemCount < 1) {
            return redirect()->to('/loans/' . $id . '/items')->with('error', 'Tambahkan minimal 1 item sebelum kirim approval.');
        }

        $this->proposalModel->update($id, [
            'status'       => self::STATUS_WAITING_L1,
            'submitted_at' => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Proposal berhasil dikirim untuk approval.');
    }

    public function cancel(int $id)
    {
        $proposal = $this->proposalModel->find($id);
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        if ((int) $proposal['proposer_id'] !== (int) auth()->id() && ! activeGroupCan('lending.request.manage-all')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak dapat membatalkan proposal ini.');
        }

        if (! in_array($proposal['status'], [self::STATUS_DRAFT, self::STATUS_WAITING_L1, self::STATUS_WAITING_L2], true)) {
            return redirect()->to('/loans/' . $id)->with('error', 'Status proposal tidak dapat dibatalkan.');
        }

        $note = trim((string) $this->request->getPost('cancel_reason')) ?: 'Dibatalkan oleh pengusul.';

        $this->proposalModel->update($id, [
            'status'        => self::STATUS_CANCELED,
            'cancel_reason' => $note,
            'canceled_by'   => auth()->id(),
            'canceled_at'   => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Proposal berhasil dibatalkan.');
    }

    public function approveL1(int $id)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $proposal = $this->proposalModel->find($id);
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk approval L1.');
        }

        $status = ((int) $proposal['requires_l2'] === 1) ? self::STATUS_WAITING_L2 : self::STATUS_APPROVED;

        $this->proposalModel->update($id, [
            'status'           => $status,
            'approval_l1_by'   => auth()->id(),
            'approval_l1_note' => trim((string) $this->request->getPost('approval_l1_note')) ?: 'Disetujui laboran.',
            'approval_l1_at'   => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Approval L1 berhasil diproses.');
    }

    public function rejectL1(int $id)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $proposal = $this->proposalModel->find($id);
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk reject L1.');
        }

        $reason = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak laboran.';

        $this->proposalModel->update($id, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'approval_l1_by'  => auth()->id(),
            'approval_l1_note'=> $reason,
            'approval_l1_at'  => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Proposal ditolak pada level 1.');
    }

    public function approveL2(int $id)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $proposal = $this->proposalModel->find($id);
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk approval L2.');
        }

        $this->proposalModel->update($id, [
            'status'           => self::STATUS_APPROVED,
            'approval_l2_by'   => auth()->id(),
            'approval_l2_note' => trim((string) $this->request->getPost('approval_l2_note')) ?: 'Disetujui kepala lab.',
            'approval_l2_at'   => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Approval L2 berhasil diproses.');
    }

    public function rejectL2(int $id)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $proposal = $this->proposalModel->find($id);
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk reject L2.');
        }

        $reason = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak kepala lab.';

        $this->proposalModel->update($id, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'approval_l2_by'  => auth()->id(),
            'approval_l2_note'=> $reason,
            'approval_l2_at'  => Time::now()->toDateTimeString(),
        ]);

        return redirect()->to('/loans/' . $id)->with('success', 'Proposal ditolak pada level 2.');
    }

    public function analytics()
    {
        if (! activeGroupCan('lending.analytics.view')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin melihat analitik.');
        }

        $statusStats = db_connect()->table('loan_proposals')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()->getResultArray();

        $itemTypeStats = db_connect()->table('loan_proposal_items')
            ->select('item_type, COUNT(*) AS total')
            ->groupBy('item_type')
            ->get()->getResultArray();

        return $this->renderView('loans/analytics', [
            'title'         => 'Analitik Peminjaman',
            'page_title'    => 'Dasbor Proposal Peminjaman',
            'statusStats'   => $statusStats,
            'itemTypeStats' => $itemTypeStats,
        ]);
    }

    private function findProposal(int $id): ?array
    {
        return db_connect()->table('loan_proposals p')
            ->select('p.*, u.username AS proposer_name')
            ->join('users u', 'u.id = p.proposer_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();
    }

    private function canManageGlobal(): bool
    {
        return activeGroupCan('lending.request.manage-all')
            || activeGroupCan('lending.approval.l1')
            || activeGroupCan('lending.approval.l2')
            || activeGroupCan('lending.master.manage');
    }

    private function canAccessProposal(array $proposal): bool
    {
        if ($this->canManageGlobal()) {
            return true;
        }

        return (int) $proposal['proposer_id'] === (int) auth()->id();
    }

    private function canEditProposal(array $proposal): bool
    {
        if ((int) $proposal['proposer_id'] !== (int) auth()->id()) {
            return false;
        }

        return $proposal['status'] === self::STATUS_DRAFT;
    }
}
