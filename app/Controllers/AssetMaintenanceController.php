<?php

namespace App\Controllers;

use App\Models\AssetMaintenanceModel;
use App\Models\LabAssetModel;
use App\Models\AssetMovementModel;

class AssetMaintenanceController extends BaseController
{
    public const TYPES    = ['preventive', 'corrective', 'calibration', 'inspection'];
    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    protected AssetMaintenanceModel $maintenanceModel;
    protected LabAssetModel $assetModel;
    protected AssetMovementModel $movementModel;

    public function __construct()
    {
        $this->maintenanceModel = new AssetMaintenanceModel();
        $this->assetModel       = new LabAssetModel();
        $this->movementModel    = new AssetMovementModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/maintenances/index', [
            'title'      => 'Riwayat Perawatan Aset',
            'page_title' => 'Riwayat Perawatan Aset',
            'assets'     => $this->assetModel->orderBy('name', 'ASC')->findAll(),
            'types'      => self::TYPES,
            'statuses'   => self::STATUSES,
        ]);
    }

    public function datatable()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $req      = $this->request;
        $draw     = (int) $req->getGet('draw');
        $start    = max(0, (int) $req->getGet('start'));
        $length   = (int) $req->getGet('length');
        if ($length <= 0) { $length = 25; }

        $search   = (string) ($req->getGet('search')['value'] ?? '');
        $orderCol = (int) ($req->getGet('order')[0]['column'] ?? 2);
        $orderDir = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $filterAssetId        = (int) $req->getGet('filter_asset_id');
        $filterType           = (string) $req->getGet('filter_type');
        $filterStatus         = (string) $req->getGet('filter_status');
        $filterScheduledFrom  = (string) $req->getGet('filter_scheduled_from');
        $filterScheduledUntil = (string) $req->getGet('filter_scheduled_until');
        $filterPerformedFrom  = (string) $req->getGet('filter_performed_from');
        $filterPerformedUntil = (string) $req->getGet('filter_performed_until');
        $filterCostMin        = $req->getGet('filter_cost_min');
        $filterCostMax        = $req->getGet('filter_cost_max');

        $colMap = [
            1 => 'a.name',
            2 => 'm.maintenance_type',
            3 => 'm.scheduled_date',
            4 => 'm.performed_date',
            5 => 'm.status',
            6 => 'm.performed_by',
            7 => 'm.cost',
            8 => 'm.next_maintenance_date',
        ];
        $orderField = $colMap[$orderCol] ?? 'COALESCE(m.scheduled_date, m.created_at)';

        $db   = db_connect();
        $base = $db->table('asset_maintenances m')
            ->select('m.*, a.name AS asset_name, a.asset_code, u.username AS created_by_name')
            ->join('lab_assets a', 'a.id = m.asset_id', 'left')
            ->join('users u', 'u.id = m.created_by', 'left');

        $recordsTotal = (clone $base)->countAllResults(false);

        if ($search !== '') {
            $base->groupStart()
                ->like('a.name', $search)
                ->orLike('a.asset_code', $search)
                ->orLike('m.description', $search)
                ->orLike('m.performed_by', $search)
                ->groupEnd();
        }
        if ($filterAssetId > 0) {
            $base->where('m.asset_id', $filterAssetId);
        }
        if ($filterType !== '' && in_array($filterType, self::TYPES, true)) {
            $base->where('m.maintenance_type', $filterType);
        }
        if ($filterStatus !== '' && in_array($filterStatus, self::STATUSES, true)) {
            $base->where('m.status', $filterStatus);
        }
        if ($filterScheduledFrom !== '') {
            $base->where('m.scheduled_date >=', $filterScheduledFrom);
        }
        if ($filterScheduledUntil !== '') {
            $base->where('m.scheduled_date <=', $filterScheduledUntil);
        }
        if ($filterPerformedFrom !== '') {
            $base->where('m.performed_date >=', $filterPerformedFrom);
        }
        if ($filterPerformedUntil !== '') {
            $base->where('m.performed_date <=', $filterPerformedUntil);
        }
        if ($filterCostMin !== null && $filterCostMin !== '') {
            $base->where('m.cost >=', (float) $filterCostMin);
        }
        if ($filterCostMax !== null && $filterCostMax !== '') {
            $base->where('m.cost <=', (float) $filterCostMax);
        }

        $recordsFiltered = (clone $base)->countAllResults(false);

        $rows = $base->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $typeLabels   = ['preventive' => 'Preventif', 'corrective' => 'Korektif', 'calibration' => 'Kalibrasi', 'inspection' => 'Inspeksi'];
        $statusLabels = ['scheduled' => 'Terjadwal', 'in_progress' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
        $statusBadges = ['scheduled' => 'badge-info', 'in_progress' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-secondary'];

        $data = [];
        $csrfName  = csrf_token();
        $csrfValue = csrf_hash();
        foreach ($rows as $r) {
            $type   = $typeLabels[$r['maintenance_type']] ?? $r['maintenance_type'];
            $sLabel = $statusLabels[$r['status']] ?? $r['status'];
            $sBadge = $statusBadges[$r['status']] ?? 'badge-secondary';
            $cost   = $r['cost'] !== null ? number_format((float) $r['cost'], 0, ',', '.') : '-';

            $btnEdit   = '<a href="' . base_url('admin/loans/maintenances/edit/' . (int) $r['id']) . '" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>';
            $btnDelete = '<form action="' . base_url('admin/loans/maintenances/delete/' . (int) $r['id']) . '" method="post" class="d-inline js-swal-delete-form" data-swal-title="Hapus perawatan?" data-swal-text="Catatan perawatan akan dihapus permanen." data-swal-confirm="Ya, hapus" data-swal-cancel="Batal">'
                . '<input type="hidden" name="' . $csrfName . '" value="' . $csrfValue . '" />'
                . '<button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button></form>';

            $actionHtml = '<div class="btn-group btn-group-sm" role="group">' . $btnEdit . $btnDelete . '</div>';

            if ($r['status'] === 'scheduled') {
                $btnPerform = '<a href="' . base_url('admin/loans/maintenances/perform/' . (int) $r['id']) . '" class="btn btn-sm btn-success btn-perform" title="Laksanakan" onclick="return confirm(\'Apakah Anda yakin ingin memulai perawatan ini?\')"><i class="fas fa-play"></i></a>';
                $actionHtml = '<div class="btn-group btn-group-sm" role="group">' . $btnPerform . $btnEdit . $btnDelete . '</div>';
            }

            $scheduledDate  = $r['scheduled_date'] ? date('d M Y', strtotime($r['scheduled_date'])) : '-';
            $performedDate  = $r['performed_date'] ? date('d M Y', strtotime($r['performed_date'])) : '-';
            $nextMaintDate  = $r['next_maintenance_date'] ? date('d M Y', strtotime($r['next_maintenance_date'])) : '-';

            $data[] = [
                '',
                '<a href="' . base_url('admin/loans/maintenances?asset_id=' . (int) $r['asset_id']) . '">' . esc($r['asset_name'] ?? '-') . '</a><br><small class="text-muted">' . esc($r['asset_code'] ?? '') . '</small>',
                esc($type),
                $scheduledDate,
                $performedDate,
                '<span class="badge ' . $sBadge . '">' . esc($sLabel) . '</span>',
                esc($r['performed_by'] ?? '-'),
                $cost,
                $nextMaintDate,
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

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/maintenances/create', [
            'mode'       => 'create',
            'title'      => 'Catat Perawatan Aset',
            'page_title' => 'Catat Perawatan Aset',
            'assets'     => $this->assetModel->orderBy('name', 'ASC')->findAll(),
            'types'      => self::TYPES,
            'statuses'   => self::STATUSES,
            'assetId'    => (int) $this->request->getGet('asset_id'),
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $maintenance = $this->maintenanceModel->find($id);
        if (! $maintenance) {
            return redirect()->to('/admin/loans/maintenances')->with('error', 'Data perawatan tidak ditemukan.');
        }

        return $this->renderView('loans/maintenances/edit', [
            'mode'        => 'edit',
            'title'       => 'Edit Perawatan Aset',
            'page_title'  => 'Edit Perawatan Aset',
            'maintenance' => $maintenance,
            'assets'      => $this->assetModel->orderBy('name', 'ASC')->findAll(),
            'types'       => self::TYPES,
            'statuses'    => self::STATUSES,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = $this->rules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->collectPayload();
        $payload['created_by'] = auth()->id();

        $id = $this->maintenanceModel->insert($payload, true);
        $this->syncAssetStatus((int) $payload['asset_id']);

        return redirect()->to('/admin/loans/maintenances?asset_id=' . (int) $payload['asset_id'])
            ->with('success', 'Catatan perawatan disimpan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $maintenance = $this->maintenanceModel->find($id);
        if (! $maintenance) {
            return redirect()->to('/admin/loans/maintenances')->with('error', 'Data perawatan tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->collectPayload();
        $this->maintenanceModel->update($id, $payload);
        $this->syncAssetStatus((int) $payload['asset_id']);

        return redirect()->to('/admin/loans/maintenances?asset_id=' . (int) $payload['asset_id'])
            ->with('success', 'Catatan perawatan diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $maintenance = $this->maintenanceModel->find($id);
        if (! $maintenance) {
            return redirect()->to('/admin/loans/maintenances')->with('error', 'Data tidak ditemukan.');
        }

        $assetId = (int) $maintenance['asset_id'];
        $this->maintenanceModel->delete($id);
        $this->syncAssetStatus($assetId);

        return redirect()->to('/admin/loans/maintenances?asset_id=' . $assetId)
            ->with('success', 'Catatan perawatan dihapus.');
    }

    private function rules(): array
    {
        return [
            'asset_id'              => 'required|is_natural_no_zero',
            'maintenance_type'      => 'required|in_list[' . implode(',', self::TYPES) . ']',
            'status'                => 'required|in_list[' . implode(',', self::STATUSES) . ']',
            'description'           => 'required|max_length[5000]',
            'scheduled_date'        => 'permit_empty|valid_date',
            'performed_date'        => 'permit_empty|valid_date',
            'next_maintenance_date' => 'permit_empty|valid_date',
            'performed_by'          => 'permit_empty|max_length[150]',
            'cost'                  => 'permit_empty|decimal',
            'result_notes'          => 'permit_empty|max_length[5000]',
        ];
    }

    private function collectPayload(): array
    {
        return [
            'asset_id'              => (int) $this->request->getPost('asset_id'),
            'maintenance_type'      => $this->request->getPost('maintenance_type'),
            'status'                => $this->request->getPost('status'),
            'scheduled_date'        => $this->request->getPost('scheduled_date') ?: null,
            'performed_date'        => $this->request->getPost('performed_date') ?: null,
            'next_maintenance_date' => $this->request->getPost('next_maintenance_date') ?: null,
            'performed_by'          => trim((string) $this->request->getPost('performed_by')) ?: null,
            'cost'                  => $this->request->getPost('cost') !== '' && $this->request->getPost('cost') !== null
                ? (float) $this->request->getPost('cost') : null,
            'description'           => trim((string) $this->request->getPost('description')),
            'result_notes'          => trim((string) $this->request->getPost('result_notes')) ?: null,
        ];
    }

    private function syncAssetStatus(int $assetId): void
    {
        $asset = $this->assetModel->find($assetId);
        if (! $asset) {
            return;
        }

        $hasActive = $this->maintenanceModel
            ->where('asset_id', $assetId)
            ->where('status', 'in_progress')
            ->countAllResults() > 0;

        $update = [];

        if ($hasActive) {
            $update['inventory_status'] = 'dalam_perbaikan';
            $update['is_loanable']      = 0;
        } elseif (($asset['inventory_status'] ?? 'aktif') === 'dalam_perbaikan') {
            $update['inventory_status'] = 'aktif';
            if (($asset['condition_status'] ?? 'baik') !== 'rusak') {
                $update['is_loanable'] = 1;
                $update['condition_status'] = 'baik';
            }
        }

        if (! empty($update)) {
            $this->assetModel->update($assetId, $update);
        }
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.maintenances.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke modul perawatan aset.');
        }

        return null;
    }

    public function perform(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $maintenance = $this->maintenanceModel->find($id);
        if (! $maintenance) {
            return redirect()->to('/admin/loans/maintenances')->with('error', 'Data perawatan tidak ditemukan.');
        }

        if ($maintenance['status'] !== 'scheduled') {
            return redirect()->back()->with('error', 'Perawatan hanya dapat dilakukan jika statusnya "scheduled".');
        }

        $movementQty = 1;
        $description = (string) ($maintenance['description'] ?? '');
        if (preg_match('/qty_damaged\s*:\s*(\d+)/i', $description, $matches)) {
            $movementQty = max(1, (int) ($matches[1] ?? 1));
        }

        $updated = $this->maintenanceModel->update($id, [
            'status' => 'completed',
            'performed_date' => date('Y-m-d'),
            'performed_by' => auth()->user()->username,
        ]);

        if ($updated) {
            $inserted = $this->movementModel->insert([
                'asset_id'       => (int) $maintenance['asset_id'],
                'movement_type'  => 'in',
                'quantity'       => $movementQty,
                'movement_date'  => date('Y-m-d H:i:s'),
                'notes'          => 'Perawatan selesai',
                'reference_type' => 'maintenance',
                'reference_id'   => $id,
                'created_by'     => auth()->id(),
            ]);

            if ($inserted) {
                $this->assetModel->update((int) $maintenance['asset_id'], [
                    'stock_available' => $this->assetModel->selectSum('stock_available')
                        ->where('id', (int) $maintenance['asset_id'])
                        ->get()
                        ->getRow()->stock_available + $movementQty,
                ]);
            }
        }

        $this->syncAssetStatus((int) $maintenance['asset_id']);

        return redirect()->to('/admin/loans/maintenances?asset_id=' . (int) $maintenance['asset_id'])
            ->with('success', 'Perawatan telah dimulai. Silakan lengkapi detailnya setelah selesai.');
    }
}
