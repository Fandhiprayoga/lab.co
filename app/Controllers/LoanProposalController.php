<?php

namespace App\Controllers;

use App\Models\AssetMovementModel;
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
    private const STATUS_BORROWED   = 'borrowed';
    private const STATUS_RETURNED   = 'returned';
    private const STATUS_LATE       = 'late';
    private const STATUS_ISSUE      = 'problematic';
    private const STATUS_IN_USE     = 'in_use';
    private const STATUS_COMPLETED  = 'completed';
    private const STATUS_REJECTED   = 'rejected';
    private const STATUS_CANCELED   = 'canceled';

    private const CHECKOUT_CONDITIONS = ['baik', 'siap_pakai', 'catatan'];
    private const CHECKIN_CONDITIONS  = ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'];

    protected LoanProposalModel $proposalModel;
    protected LoanProposalItemModel $itemModel;
    protected LabAssetModel $assetModel;
    protected LabModel $labModel;
    protected AssetMovementModel $movementModel;

    public function __construct()
    {
        $this->proposalModel = new LoanProposalModel();
        $this->itemModel     = new LoanProposalItemModel();
        $this->assetModel    = new LabAssetModel();
        $this->labModel      = new LabModel();
        $this->movementModel = new AssetMovementModel();
    }

    public function index()
    {
        $this->syncLateStatuses();

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

        $proposalId       = (int) $this->proposalModel->getInsertID();
        $createdProposal  = $this->proposalModel->find($proposalId);
        $proposalPublicId = $createdProposal['public_id'] ?? (string) $proposalId;

        return redirect()->to('/loans/' . $proposalPublicId . '/items')->with('success', 'Proposal berhasil dibuat. Pilih item yang akan dipinjam.');
    }

    public function show(string $publicId)
    {
        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        $this->syncLateStatusesForProposal($proposal);
        $proposal = $this->findProposalByPublicId($publicId);

        if (! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki akses ke proposal ini.');
        }

        // Draft proposals belong on the item-selection page
        if ($proposal['status'] === self::STATUS_DRAFT) {
            return redirect()->to('/loans/' . $publicId . '/items');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $items = db_connect()->table('loan_proposal_items i')
            ->select('i.*, a.name AS equipment_name, l.name AS lab_name')
            ->join('lab_assets a', 'a.id = i.equipment_id', 'left')
            ->join('labs l', 'l.id = i.lab_id', 'left')
            ->where('i.proposal_id', $proposalId)
            ->orderBy('i.id', 'ASC')
            ->get()->getResultArray();

        // Resolve actor usernames for timeline
        $actorIds = array_values(array_unique(array_filter([
            $proposal['approval_l1_by'] ?? null,
            $proposal['approval_l2_by'] ?? null,
            $proposal['canceled_by']    ?? null,
            $proposal['checkout_by']    ?? null,
            $proposal['checkin_by']     ?? null,
            $proposal['started_use_by'] ?? null,
            $proposal['finished_use_by'] ?? null,
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

    public function selectItems(string $publicId)
    {
        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        if (! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki akses ke proposal ini.');
        }

        // Only draft proposals allow item selection
        if ($proposal['status'] !== self::STATUS_DRAFT) {
            return redirect()->to('/loans/' . $publicId);
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $items = db_connect()->table('loan_proposal_items i')
            ->select('i.*, a.name AS equipment_name, a.photo AS equipment_photo, a.category AS equipment_category, al.name AS equipment_lab_name, al.location AS equipment_lab_location, l.name AS lab_name, l.code AS lab_code, l.location AS lab_location, l.capacity AS lab_capacity, l.logo AS lab_logo')
            ->join('lab_assets a', 'a.id = i.equipment_id', 'left')
            ->join('labs al', 'al.id = a.lab_id', 'left')
            ->join('labs l', 'l.id = i.lab_id', 'left')
            ->where('i.proposal_id', $proposalId)
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
                ->select('l.id, l.name, l.code, l.location, l.capacity, l.logo, l.condition_status')
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

    public function addEquipmentItem(string $publicId)
    {
        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $equipmentId = (int) $this->request->getPost('equipment_id');
        $qty         = max(1, (int) $this->request->getPost('qty'));
        $note        = trim((string) $this->request->getPost('note')) ?: null;

        if (($proposal['loan_type'] ?? '') !== 'equipment') {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Proposal ini adalah proposal peminjaman lab, bukan alat.');
        }

        $equipment = $this->assetModel->find($equipmentId);
        if (! $equipment || (int) $equipment['is_active'] !== 1 || $equipment['asset_type'] !== 'equipment') {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Data alat tidak valid.');
        }

        if ((int) ($equipment['is_loanable'] ?? 0) !== 1) {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Alat tidak bisa dipinjam saat ini.');
        }

        if ((string) ($equipment['condition_status'] ?? '') !== 'baik') {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Kondisi alat tidak memenuhi syarat untuk dipinjam.');
        }

        if ($qty > (int) $equipment['stock_available']) {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Jumlah alat melebihi stok tersedia.');
        }

        $this->itemModel->insert([
            'proposal_id'   => $proposalId,
            'item_type'     => 'equipment',
            'equipment_id'  => $equipmentId,
            'lab_id'        => null,
            'qty'           => $qty,
            'note'          => $note,
        ]);

        return redirect()->to('/loans/' . $publicId . '/items')->with('success', 'Item alat berhasil ditambahkan ke proposal.');
    }

    public function addLabItem(string $publicId)
    {
        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $labId = (int) $this->request->getPost('lab_id');
        $note  = trim((string) $this->request->getPost('note')) ?: null;

        if (($proposal['loan_type'] ?? '') !== 'lab') {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Proposal ini adalah proposal peminjaman alat, bukan lab.');
        }

        $lab = $this->labModel->find($labId);
        if (! $lab || (int) $lab['is_active'] !== 1) {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Data lab tidak valid.');
        }

        if ((int) ($lab['is_loanable'] ?? 0) !== 1) {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Lab tidak bisa dipinjam saat ini.');
        }

        if ((string) ($lab['condition_status'] ?? '') !== 'baik') {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Kondisi lab tidak memenuhi syarat untuk dipinjam.');
        }

        $alreadyExists = $this->itemModel
            ->where('proposal_id', $proposalId)
            ->where('item_type', 'lab')
            ->where('lab_id', $labId)
            ->countAllResults() > 0;

        if ($alreadyExists) {
            return redirect()->to('/loans/' . $publicId . '/items')->with('error', 'Lab tersebut sudah ditambahkan ke proposal.');
        }

        $this->itemModel->insert([
            'proposal_id'  => $proposalId,
            'item_type'    => 'lab',
            'equipment_id' => null,
            'lab_id'       => $labId,
            'qty'          => 1,
            'note'         => $note,
        ]);

        return redirect()->to('/loans/' . $publicId . '/items')->with('success', 'Item lab berhasil ditambahkan ke proposal.');
    }

    public function removeItem(string $publicId, string $itemPublicId)
    {
        $tab        = (string) $this->request->getGet('tab');
        $allowedTab = ['summary', 'catalog', 'selected', 'actions'];
        $tabQuery   = in_array($tab, $allowedTab, true) ? ('?tab=' . $tab) : '';

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat diubah.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $item = $this->itemModel
            ->where('public_id', $itemPublicId)
            ->where('proposal_id', $proposalId)
            ->first();
        if (! $item) {
            return redirect()->to('/loans/' . $publicId . '/items' . $tabQuery)->with('error', 'Item tidak ditemukan pada proposal ini.');
        }

        $this->itemModel->delete((int) ($item['id'] ?? 0));

        return redirect()->to('/loans/' . $publicId . '/items' . $tabQuery)->with('success', 'Item berhasil dihapus dari proposal.');
    }

    public function submit(string $publicId)
    {
        $tab        = (string) $this->request->getGet('tab');
        $allowedTab = ['summary', 'catalog', 'selected', 'actions'];
        $tabQuery   = in_array($tab, $allowedTab, true) ? ('?tab=' . $tab) : '';

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || ! $this->canEditProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak dapat dikirim.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $itemCount = $this->itemModel->where('proposal_id', $proposalId)->countAllResults();
        if ($itemCount < 1) {
            return redirect()->to('/loans/' . $publicId . '/items' . $tabQuery)->with('error', 'Tambahkan minimal 1 item sebelum kirim approval.');
        }

        if (($proposal['loan_type'] ?? 'equipment') === 'lab' && $this->hasLabScheduleConflict($proposalId, $proposal)) {
            return redirect()->to('/loans/' . $publicId . '/items' . $tabQuery)->with('error', 'Jadwal bentrok dengan proposal ruangan lain yang masih aktif. Silakan ubah waktu atau item lab.');
        }

        $this->proposalModel->update($proposalId, [
            'status'       => self::STATUS_WAITING_L1,
            'submitted_at' => Time::now()->toDateTimeString(),
        ]);

        notify_role('laboran', 'loan.submitted', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Proposal berhasil dikirim untuk approval.');
    }

    public function checkout(string $publicId)
    {
        if (! activeGroupCan('lending.checkout')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin check-out.');
        }

        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal || ! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan atau tidak dapat diakses.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        if (($proposal['loan_type'] ?? 'equipment') !== 'equipment') {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Check-out hanya berlaku untuk peminjaman alat.');
        }

        if (($proposal['status'] ?? '') !== self::STATUS_APPROVED) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal tidak valid untuk check-out.');
        }

        $items = $this->itemModel
            ->where('proposal_id', $proposalId)
            ->where('item_type', 'equipment')
            ->findAll();

        if (empty($items)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal alat tidak memiliki item untuk diambil.');
        }

        $assetMap = [];
        foreach ($items as $item) {
            $assetId = (int) ($item['equipment_id'] ?? 0);
            if ($assetId < 1) {
                continue;
            }

            $asset = $this->assetModel->find($assetId);
            if (! $asset || (int) ($asset['is_active'] ?? 0) !== 1 || (int) ($asset['is_loanable'] ?? 0) !== 1) {
                return redirect()->to('/loans/' . $publicId)->with('error', 'Salah satu aset tidak valid untuk check-out.');
            }

            if ((int) ($asset['stock_available'] ?? 0) < (int) ($item['qty'] ?? 0)) {
                return redirect()->to('/loans/' . $publicId)->with('error', 'Stok aset ' . ($asset['name'] ?? '-') . ' tidak mencukupi.');
            }

            $assetMap[$assetId] = $asset;
        }

        if (! empty($proposal['checkout_at'])) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal ini sudah pernah di-checkout.');
        }

        $condition = strtolower(trim((string) $this->request->getPost('checkout_condition')) ?: 'baik');
        if (! in_array($condition, self::CHECKOUT_CONDITIONS, true)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Kondisi checkout tidak valid.');
        }

        $now       = Time::now()->toDateTimeString();

        $db = db_connect();
        $db->transStart();

        $updated = $db->table('loan_proposals')
            ->where('id', $proposalId)
            ->where('status', self::STATUS_APPROVED)
            ->where('checkout_at IS NULL', null, false)
            ->update([
            'status'             => self::STATUS_BORROWED,
            'checkout_by'        => auth()->id(),
            'checkout_condition' => $condition,
            'checkout_at'        => $now,
            'is_late'            => 0,
            ]);

        if (! $updated || $db->affectedRows() < 1) {
            $db->transRollback();
            return redirect()->to('/loans/' . $publicId)->with('error', 'Status proposal sudah berubah, silakan muat ulang halaman.');
        }

        foreach ($items as $item) {
            $assetId = (int) ($item['equipment_id'] ?? 0);
            if ($assetId < 1 || ! isset($assetMap[$assetId])) {
                continue;
            }

            $asset = $assetMap[$assetId];
            $qty   = (int) ($item['qty'] ?? 1);

            $this->assetModel->update($assetId, [
                'stock_available'  => (int) $asset['stock_available'] - $qty,
                'inventory_status' => 'dipinjam',
            ]);

            $this->movementModel->insert([
                'asset_id'       => $assetId,
                'movement_type'  => 'borrow',
                'quantity'       => -1 * $qty,
                'from_lab_id'    => (int) ($asset['lab_id'] ?? 0) ?: null,
                'to_lab_id'      => null,
                'reference_type' => 'loan_proposal',
                'reference_id'   => $proposalId,
                'movement_date'  => $now,
                'notes'          => 'Auto: check-out proposal ' . ($proposal['proposal_code'] ?? '#'.$proposalId) . '. Kondisi awal: ' . $condition,
                'created_by'     => auth()->id(),
                'created_at'     => $now,
            ]);
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Gagal memproses check-out proposal.');
        }

        send_notification((int) $proposal['proposer_id'], 'loan.checked_out', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Check-out proposal berhasil diproses.');
    }

    public function checkin(string $publicId)
    {
        if (! activeGroupCan('lending.checkin')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin check-in.');
        }

        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal || ! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan atau tidak dapat diakses.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        if (($proposal['loan_type'] ?? 'equipment') !== 'equipment') {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Check-in hanya berlaku untuk peminjaman alat.');
        }

        if (! in_array(($proposal['status'] ?? ''), [self::STATUS_BORROWED, self::STATUS_LATE], true)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal tidak valid untuk check-in.');
        }

        $items = $this->itemModel
            ->where('proposal_id', $proposalId)
            ->where('item_type', 'equipment')
            ->findAll();

        if (empty($items)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal alat tidak memiliki item untuk dikembalikan.');
        }

        $assetMap = [];
        foreach ($items as $item) {
            $assetId = (int) ($item['equipment_id'] ?? 0);
            if ($assetId < 1) {
                continue;
            }

            $asset = $this->assetModel->find($assetId);
            if (! $asset) {
                return redirect()->to('/loans/' . $publicId)->with('error', 'Salah satu aset tidak ditemukan saat check-in.');
            }

            $assetMap[$assetId] = $asset;
        }

        if (! empty($proposal['checkin_at'])) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal ini sudah pernah di-checkin.');
        }

        $condition = strtolower(trim((string) $this->request->getPost('checkin_condition')) ?: 'baik');
        if (! in_array($condition, self::CHECKIN_CONDITIONS, true)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Kondisi checkin tidak valid.');
        }

        $issueNote = trim((string) $this->request->getPost('issue_note'));
        $now       = Time::now()->toDateTimeString();

        $isLostCondition = $condition === 'hilang';
        $hasIssue        = $issueNote !== '' || $isLostCondition;
        $status          = $hasIssue ? self::STATUS_ISSUE : self::STATUS_RETURNED;

        $db = db_connect();
        $db->transStart();

        $updated = $db->table('loan_proposals')
            ->where('id', $proposalId)
            ->whereIn('status', [self::STATUS_BORROWED, self::STATUS_LATE])
            ->where('checkin_at IS NULL', null, false)
            ->update([
            'status'            => $status,
            'checkin_by'        => auth()->id(),
            'checkin_condition' => $condition,
            'checkin_at'        => $now,
            'issue_flag'        => $hasIssue ? 1 : 0,
            'issue_note'        => $issueNote !== '' ? $issueNote : null,
            ]);

        if (! $updated || $db->affectedRows() < 1) {
            $db->transRollback();
            return redirect()->to('/loans/' . $publicId)->with('error', 'Status proposal sudah berubah, silakan muat ulang halaman.');
        }

        foreach ($items as $item) {
            $assetId = (int) ($item['equipment_id'] ?? 0);
            if ($assetId < 1 || ! isset($assetMap[$assetId])) {
                continue;
            }

            $asset = $assetMap[$assetId];
            $qty   = (int) ($item['qty'] ?? 1);

            if (! $isLostCondition) {
                $this->assetModel->update($assetId, [
                    'stock_available'  => (int) $asset['stock_available'] + $qty,
                    'inventory_status' => 'aktif',
                ]);
            } else {
                $this->assetModel->update($assetId, [
                    'inventory_status' => 'hilang',
                ]);
            }

            $this->movementModel->insert([
                'asset_id'       => $assetId,
                'movement_type'  => $isLostCondition ? 'disposal' : 'return',
                'quantity'       => $isLostCondition ? -1 * $qty : $qty,
                'from_lab_id'    => null,
                'to_lab_id'      => (int) ($asset['lab_id'] ?? 0) ?: null,
                'reference_type' => 'loan_proposal',
                'reference_id'   => $proposalId,
                'movement_date'  => $now,
                'notes'          => 'Auto: check-in proposal ' . ($proposal['proposal_code'] ?? '#'.$proposalId) . '. Kondisi akhir: ' . $condition . ($issueNote !== '' ? ' | ' . $issueNote : ''),
                'created_by'     => auth()->id(),
                'created_at'     => $now,
            ]);
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Gagal memproses check-in proposal.');
        }

        send_notification((int) $proposal['proposer_id'], 'loan.checked_in', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Check-in proposal berhasil diproses.');
    }

    public function startUsage(string $publicId)
    {
        if (! activeGroupCan('lending.checkout')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin memulai penggunaan ruangan.');
        }

        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal || ! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan atau tidak dapat diakses.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        if (($proposal['loan_type'] ?? 'equipment') !== 'lab') {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Aksi ini hanya berlaku untuk peminjaman ruangan.');
        }

        if (($proposal['status'] ?? '') !== self::STATUS_APPROVED) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal ruangan belum siap dimulai.');
        }

        if (! empty($proposal['started_use_at'])) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Penggunaan ruangan sudah pernah dimulai.');
        }

        $db = db_connect();
        $updated = $db->table('loan_proposals')
            ->where('id', $proposalId)
            ->where('status', self::STATUS_APPROVED)
            ->where('started_use_at IS NULL', null, false)
            ->update([
            'status'         => self::STATUS_IN_USE,
            'started_use_by' => auth()->id(),
            'started_use_at' => Time::now()->toDateTimeString(),
            ]);

        if (! $updated || $db->affectedRows() < 1) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Status proposal sudah berubah, silakan muat ulang halaman.');
        }

        send_notification((int) $proposal['proposer_id'], 'loan.usage_started', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Penggunaan ruangan dimulai.');
    }

    public function finishUsage(string $publicId)
    {
        if (! activeGroupCan('lending.checkin')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin menyelesaikan penggunaan ruangan.');
        }

        $proposal = $this->findProposalByPublicId($publicId);
        if (! $proposal || ! $this->canAccessProposal($proposal)) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan atau tidak dapat diakses.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        if (($proposal['loan_type'] ?? 'equipment') !== 'lab') {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Aksi ini hanya berlaku untuk peminjaman ruangan.');
        }

        if (($proposal['status'] ?? '') !== self::STATUS_IN_USE) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Proposal ruangan belum berstatus sedang digunakan.');
        }

        if (! empty($proposal['finished_use_at'])) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Penggunaan ruangan sudah pernah diselesaikan.');
        }

        $db = db_connect();
        $updated = $db->table('loan_proposals')
            ->where('id', $proposalId)
            ->where('status', self::STATUS_IN_USE)
            ->where('finished_use_at IS NULL', null, false)
            ->update([
            'status'          => self::STATUS_COMPLETED,
            'finished_use_by' => auth()->id(),
            'finished_use_at' => Time::now()->toDateTimeString(),
            ]);

        if (! $updated || $db->affectedRows() < 1) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Status proposal sudah berubah, silakan muat ulang halaman.');
        }

        send_notification((int) $proposal['proposer_id'], 'loan.usage_finished', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Penggunaan ruangan selesai diverifikasi.');
    }

    public function cancel(string $publicId)
    {
        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak ditemukan.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        if ((int) $proposal['proposer_id'] !== (int) auth()->id() && ! activeGroupCan('lending.request.manage-all')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak dapat membatalkan proposal ini.');
        }

        if (! in_array($proposal['status'], [self::STATUS_DRAFT, self::STATUS_WAITING_L1, self::STATUS_WAITING_L2], true)) {
            return redirect()->to('/loans/' . $publicId)->with('error', 'Status proposal tidak dapat dibatalkan.');
        }

        $note = trim((string) $this->request->getPost('cancel_reason')) ?: 'Dibatalkan oleh pengusul.';

        $this->proposalModel->update($proposalId, [
            'status'        => self::STATUS_CANCELED,
            'cancel_reason' => $note,
            'canceled_by'   => auth()->id(),
            'canceled_at'   => Time::now()->toDateTimeString(),
        ]);

        if ((int) auth()->id() !== (int) $proposal['proposer_id']) {
            send_notification((int) $proposal['proposer_id'], 'loan.canceled', [
                'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
                'url'           => '/loans/' . $publicId,
                'reference_id'  => $proposalId,
            ]);
        }

        return redirect()->to('/loans/' . $publicId)->with('success', 'Proposal berhasil dibatalkan.');
    }

    public function approveL1(string $publicId)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk approval L1.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $status = ((int) $proposal['requires_l2'] === 1) ? self::STATUS_WAITING_L2 : self::STATUS_APPROVED;

        $this->proposalModel->update($proposalId, [
            'status'           => $status,
            'approval_l1_by'   => auth()->id(),
            'approval_l1_note' => trim((string) $this->request->getPost('approval_l1_note')) ?: 'Disetujui laboran.',
            'approval_l1_at'   => Time::now()->toDateTimeString(),
        ]);

        if ($status === self::STATUS_WAITING_L2) {
            notify_role('kepala_lab', 'loan.approved_l1', [
                'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
                'url'           => '/loans/' . $publicId,
                'reference_id'  => $proposalId,
            ]);
        } else {
            send_notification((int) $proposal['proposer_id'], 'loan.approved_l2', [
                'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
                'url'           => '/loans/' . $publicId,
                'reference_id'  => $proposalId,
            ]);
        }

        return redirect()->to('/loans/' . $publicId)->with('success', 'Approval L1 berhasil diproses.');
    }

    public function rejectL1(string $publicId)
    {
        if (! activeGroupCan('lending.approval.l1')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 1.');
        }

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L1) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk reject L1.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $reason = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak laboran.';

        $this->proposalModel->update($proposalId, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'approval_l1_by'  => auth()->id(),
            'approval_l1_note'=> $reason,
            'approval_l1_at'  => Time::now()->toDateTimeString(),
        ]);

        send_notification((int) $proposal['proposer_id'], 'loan.rejected_l1', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Proposal ditolak pada level 1.');
    }

    public function approveL2(string $publicId)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk approval L2.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $this->proposalModel->update($proposalId, [
            'status'           => self::STATUS_APPROVED,
            'approval_l2_by'   => auth()->id(),
            'approval_l2_note' => trim((string) $this->request->getPost('approval_l2_note')) ?: 'Disetujui kepala lab.',
            'approval_l2_at'   => Time::now()->toDateTimeString(),
        ]);

        send_notification((int) $proposal['proposer_id'], 'loan.approved_l2', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Approval L2 berhasil diproses.');
    }

    public function rejectL2(string $publicId)
    {
        if (! activeGroupCan('lending.approval.l2')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin approval level 2.');
        }

        $proposal = $this->proposalModel->where('public_id', $publicId)->first();
        if (! $proposal || $proposal['status'] !== self::STATUS_WAITING_L2) {
            return redirect()->to('/loans')->with('error', 'Proposal tidak valid untuk reject L2.');
        }

        $proposalId = (int) ($proposal['id'] ?? 0);

        $reason = trim((string) $this->request->getPost('rejected_reason')) ?: 'Ditolak kepala lab.';

        $this->proposalModel->update($proposalId, [
            'status'          => self::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'approval_l2_by'  => auth()->id(),
            'approval_l2_note'=> $reason,
            'approval_l2_at'  => Time::now()->toDateTimeString(),
        ]);

        send_notification((int) $proposal['proposer_id'], 'loan.rejected_l2', [
            'proposal_code' => $proposal['proposal_code'] ?? ('#' . $proposalId),
            'url'           => '/loans/' . $publicId,
            'reference_id'  => $proposalId,
        ]);

        return redirect()->to('/loans/' . $publicId)->with('success', 'Proposal ditolak pada level 2.');
    }

    public function analytics()
    {
        if (! activeGroupCan('lending.analytics.view')) {
            return redirect()->to('/loans')->with('error', 'Anda tidak memiliki izin melihat analitik.');
        }

        $from     = trim((string) $this->request->getGet('from'));
        $until    = trim((string) $this->request->getGet('until'));
        $loanType = trim((string) $this->request->getGet('loan_type'));
        $status   = trim((string) $this->request->getGet('status'));

        if ($from !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }

        if ($until !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $until)) {
            $until = '';
        }

        if (! in_array($loanType, ['equipment', 'lab'], true)) {
            $loanType = '';
        }

        $allowedStatus = [
            self::STATUS_DRAFT,
            self::STATUS_WAITING_L1,
            self::STATUS_WAITING_L2,
            self::STATUS_APPROVED,
            self::STATUS_BORROWED,
            self::STATUS_LATE,
            self::STATUS_RETURNED,
            self::STATUS_ISSUE,
            self::STATUS_IN_USE,
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELED,
        ];

        if (! in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        $applyProposalFilters = static function ($builder, string $alias = '') use ($from, $until, $loanType, $status): void {
            $prefix = $alias !== '' ? $alias . '.' : '';

            if ($from !== '') {
                $builder->where($prefix . 'created_at >=', $from . ' 00:00:00');
            }

            if ($until !== '') {
                $builder->where($prefix . 'created_at <=', $until . ' 23:59:59');
            }

            if ($loanType !== '') {
                $builder->where($prefix . 'loan_type', $loanType);
            }

            if ($status !== '') {
                $builder->where($prefix . 'status', $status);
            }
        };

        $statusBuilder = db_connect()->table('loan_proposals')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status');
        $applyProposalFilters($statusBuilder);
        $statusStats = $statusBuilder->get()->getResultArray();

        $itemTypeBuilder = db_connect()->table('loan_proposal_items i')
            ->select('item_type, COUNT(*) AS total')
            ->join('loan_proposals p', 'p.id = i.proposal_id', 'inner')
            ->groupBy('item_type');
        $applyProposalFilters($itemTypeBuilder, 'p');
        $itemTypeStats = $itemTypeBuilder->get()->getResultArray();

        $monthlyBuilder = db_connect()->table('loan_proposals')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS total", false)
            ->where('created_at >=', date('Y-m-01 00:00:00', strtotime('-5 months')))
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')", false)
            ->orderBy('period', 'ASC');
        $applyProposalFilters($monthlyBuilder);
        $monthlyStats = $monthlyBuilder->get()->getResultArray();

        $durationBuilder = db_connect()->table('loan_proposals')
            ->select('loan_type, AVG(TIMESTAMPDIFF(HOUR, start_at, end_at)) AS avg_hours', false)
            ->where('start_at IS NOT NULL', null, false)
            ->where('end_at IS NOT NULL', null, false)
            ->groupBy('loan_type');
        $applyProposalFilters($durationBuilder);
        $durationStats = $durationBuilder->get()->getResultArray();

        $topBuilder = db_connect()->table('loan_proposals p')
            ->select('u.username AS proposer_name, COUNT(*) AS total')
            ->join('users u', 'u.id = p.proposer_id', 'left')
            ->groupBy('p.proposer_id')
            ->orderBy('total', 'DESC')
            ->limit(5);
        $applyProposalFilters($topBuilder, 'p');
        $topProposers = $topBuilder->get()->getResultArray();

        return $this->renderView('loans/analytics', [
            'title'         => 'Analitik Peminjaman',
            'page_title'    => 'Dasbor Proposal Peminjaman',
            'statusStats'   => $statusStats,
            'itemTypeStats' => $itemTypeStats,
            'monthlyStats'  => $monthlyStats,
            'durationStats' => $durationStats,
            'topProposers'  => $topProposers,
            'filters'       => [
                'from'      => $from,
                'until'     => $until,
                'loan_type' => $loanType,
                'status'    => $status,
            ],
        ]);
    }

    private function findProposalByPublicId(string $publicId): ?array
    {
        return db_connect()->table('loan_proposals p')
            ->select('p.*, u.username AS proposer_name')
            ->join('users u', 'u.id = p.proposer_id', 'left')
            ->where('p.public_id', $publicId)
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

    private function hasLabScheduleConflict(int $proposalId, array $proposal): bool
    {
        if (($proposal['loan_type'] ?? 'equipment') !== 'lab') {
            return false;
        }

        $startAt = $proposal['start_at'] ?? null;
        $endAt   = $proposal['end_at'] ?? null;
        if (! $startAt || ! $endAt) {
            return false;
        }

        $labIds = db_connect()->table('loan_proposal_items')
            ->select('lab_id')
            ->where('proposal_id', $proposalId)
            ->where('item_type', 'lab')
            ->where('lab_id IS NOT NULL', null, false)
            ->groupBy('lab_id')
            ->get()
            ->getResultArray();

        $labIds = array_values(array_filter(array_map(static fn(array $row): int => (int) ($row['lab_id'] ?? 0), $labIds)));

        if (empty($labIds)) {
            return false;
        }

        $conflict = db_connect()->table('loan_proposal_items i')
            ->select('p.id')
            ->join('loan_proposals p', 'p.id = i.proposal_id', 'inner')
            ->whereIn('i.lab_id', $labIds)
            ->where('i.item_type', 'lab')
            ->where('p.id !=', $proposalId)
            ->whereIn('p.status', [
                self::STATUS_WAITING_L1,
                self::STATUS_WAITING_L2,
                self::STATUS_APPROVED,
                self::STATUS_IN_USE,
            ])
            ->where('p.start_at <', $endAt)
            ->where('p.end_at >', $startAt)
            ->limit(1)
            ->get()
            ->getRowArray();

        return ! empty($conflict);
    }

    private function syncLateStatuses(): void
    {
        $now = Time::now()->toDateTimeString();

        $lateProposals = $this->proposalModel
            ->where('loan_type', 'equipment')
            ->where('status', self::STATUS_BORROWED)
            ->where('end_at <', $now)
            ->findAll();

        foreach ($lateProposals as $proposal) {
            $this->proposalModel->update((int) $proposal['id'], [
                'status'  => self::STATUS_LATE,
                'is_late' => 1,
            ]);
        }
    }

    private function syncLateStatusesForProposal(array $proposal): void
    {
        if (($proposal['loan_type'] ?? 'equipment') !== 'equipment') {
            return;
        }

        if (($proposal['status'] ?? '') !== self::STATUS_BORROWED) {
            return;
        }

        $endAt = $proposal['end_at'] ?? null;
        if (! $endAt || strtotime($endAt) >= time()) {
            return;
        }

        $this->proposalModel->update((int) ($proposal['id'] ?? 0), [
            'status'  => self::STATUS_LATE,
            'is_late' => 1,
        ]);
    }
}
