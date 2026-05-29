<?php

namespace App\Controllers;

use App\Models\AssetMaintenanceModel;
use App\Models\LabAssetModel;

class AssetMaintenanceController extends BaseController
{
    public const TYPES    = ['preventive', 'corrective', 'calibration', 'inspection'];
    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    protected AssetMaintenanceModel $maintenanceModel;
    protected LabAssetModel $assetModel;

    public function __construct()
    {
        $this->maintenanceModel = new AssetMaintenanceModel();
        $this->assetModel       = new LabAssetModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $assetId = (int) $this->request->getGet('asset_id');

        $builder = db_connect()->table('asset_maintenances m')
            ->select('m.*, a.name AS asset_name, a.asset_code, u.username AS created_by_name')
            ->join('lab_assets a', 'a.id = m.asset_id', 'left')
            ->join('users u', 'u.id = m.created_by', 'left')
            ->orderBy('COALESCE(m.scheduled_date, m.created_at)', 'DESC', false)
            ->orderBy('m.id', 'DESC');

        if ($assetId > 0) {
            $builder->where('m.asset_id', $assetId);
        }

        $maintenances = $builder->get()->getResultArray();
        $asset = $assetId > 0 ? $this->assetModel->find($assetId) : null;

        return $this->renderView('loans/maintenances/index', [
            'title'        => 'Riwayat Perawatan Aset',
            'page_title'   => $asset ? 'Perawatan: ' . $asset['name'] : 'Riwayat Perawatan Aset',
            'maintenances' => $maintenances,
            'asset'        => $asset,
            'assetId'      => $assetId,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/maintenances/create', [
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
}
