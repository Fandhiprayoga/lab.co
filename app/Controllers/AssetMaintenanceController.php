<?php

namespace App\Controllers;

use App\Models\AssetMaintenanceModel;
use App\Models\AssetItemModel;
use App\Models\LabAssetModel;
use App\Models\LabModel;

class AssetMaintenanceController extends BaseController
{
    public const TYPES    = ['preventive', 'corrective', 'calibration', 'inspection'];
    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    protected AssetMaintenanceModel $maintenanceModel;
    protected AssetItemModel $assetItemModel;
    protected LabAssetModel $assetModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->maintenanceModel = new AssetMaintenanceModel();
        $this->assetItemModel   = new AssetItemModel();
        $this->assetModel       = new LabAssetModel();
        $this->labModel         = new LabModel();
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

        $assetId = (int) $this->request->getGet('asset_id');
        $prefillLabId = 0;
        if ($assetId > 0) {
            $asset = $this->assetModel->find($assetId);
            $prefillLabId = (int) ($asset['lab_id'] ?? 0);
        }

        $assets = db_connect()->table('lab_assets')
            ->select('id, lab_id, name, asset_code, asset_type, is_active')
            ->where('asset_type', 'equipment')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $items = db_connect()->table('asset_items ai')
            ->select('ai.id, ai.asset_id, ai.lab_id, ai.item_code, ai.inventory_status, a.name AS asset_name, a.asset_code')
            ->join('lab_assets a', 'a.id = ai.asset_id', 'inner')
            ->where('a.asset_type', 'equipment')
            ->where('a.is_active', 1)
            ->orderBy('a.name', 'ASC')
            ->orderBy('ai.item_code', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('loans/maintenances/create', [
            'title'        => 'Catat Perawatan Aset',
            'page_title'   => 'Catat Perawatan Aset',
            'assets'       => $assets,
            'items'        => $items,
            'labs'         => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'types'        => self::TYPES,
            'statuses'     => self::STATUSES,
            'assetId'      => $assetId,
            'prefillLabId' => $prefillLabId,
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

        $assetId = (int) ($maintenance['asset_id'] ?? 0);
        $prefillLabId = 0;
        if ($assetId > 0) {
            $asset = $this->assetModel->find($assetId);
            $prefillLabId = (int) ($asset['lab_id'] ?? 0);
        }

        $assets = db_connect()->table('lab_assets')
            ->select('id, lab_id, name, asset_code, asset_type, is_active')
            ->where('asset_type', 'equipment')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $items = db_connect()->table('asset_items ai')
            ->select('ai.id, ai.asset_id, ai.lab_id, ai.item_code, ai.inventory_status, a.name AS asset_name, a.asset_code')
            ->join('lab_assets a', 'a.id = ai.asset_id', 'inner')
            ->where('a.asset_type', 'equipment')
            ->where('a.is_active', 1)
            ->orderBy('a.name', 'ASC')
            ->orderBy('ai.item_code', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('loans/maintenances/edit', [
            'title'        => 'Edit Perawatan Aset',
            'page_title'   => 'Edit Perawatan Aset',
            'maintenance'  => $maintenance,
            'assets'       => $assets,
            'items'        => $items,
            'labs'         => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'types'        => self::TYPES,
            'statuses'     => self::STATUSES,
            'assetId'      => $assetId,
            'prefillLabId' => $prefillLabId,
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
        if ($itemError = $this->validateAssetItemRelation((int) $payload['asset_id'], $payload['asset_item_id'])) {
            return redirect()->back()->withInput()->with('error', $itemError);
        }

        $payload['created_by'] = auth()->id();

        $id = $this->maintenanceModel->insert($payload, true);
        $this->syncItemStatusByMaintenanceScope((int) $payload['asset_id'], (int) ($payload['asset_item_id'] ?? 0));
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

        $oldAssetId = (int) ($maintenance['asset_id'] ?? 0);
        $oldItemId  = (int) ($maintenance['asset_item_id'] ?? 0);

        $payload = $this->collectPayload();
        if ($itemError = $this->validateAssetItemRelation((int) $payload['asset_id'], $payload['asset_item_id'])) {
            return redirect()->back()->withInput()->with('error', $itemError);
        }

        $this->maintenanceModel->update($id, $payload);

        $newAssetId = (int) ($payload['asset_id'] ?? 0);
        $newItemId  = (int) ($payload['asset_item_id'] ?? 0);

        $this->syncItemStatusByMaintenanceScope($oldAssetId, $oldItemId);
        if ($oldAssetId !== $newAssetId || $oldItemId !== $newItemId) {
            $this->syncItemStatusByMaintenanceScope($newAssetId, $newItemId);
        }

        $this->syncAssetStatus($oldAssetId);
        if ($oldAssetId !== $newAssetId) {
            $this->syncAssetStatus($newAssetId);
        }

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

        $assetId = (int) ($maintenance['asset_id'] ?? 0);
        $itemId  = (int) ($maintenance['asset_item_id'] ?? 0);
        $this->maintenanceModel->delete($id);

        $this->syncItemStatusByMaintenanceScope($assetId, $itemId);
        $this->syncAssetStatus($assetId);

        return redirect()->to('/admin/loans/maintenances?asset_id=' . $assetId)
            ->with('success', 'Catatan perawatan dihapus.');
    }

    private function rules(): array
    {
        return [
            'asset_id'              => 'required|is_natural_no_zero',
            'asset_item_id'         => 'permit_empty|is_natural_no_zero',
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
            'asset_item_id'         => $this->request->getPost('asset_item_id') ?: null,
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
        if ($assetId < 1) {
            return;
        }

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

    private function validateAssetItemRelation(int $assetId, $assetItemId): ?string
    {
        $itemId = (int) ($assetItemId ?? 0);
        if ($itemId < 1) {
            return null;
        }

        $item = $this->assetItemModel->find($itemId);
        if (! $item) {
            return 'Item aset tidak ditemukan.';
        }

        if ((int) ($item['asset_id'] ?? 0) !== $assetId) {
            return 'Item tidak sesuai dengan aset yang dipilih.';
        }

        return null;
    }

    private function syncItemStatusByMaintenanceScope(int $assetId, int $assetItemId = 0): void
    {
        if ($assetId < 1) {
            return;
        }

        $itemBuilder = $this->assetItemModel->where('asset_id', $assetId);
        if ($assetItemId > 0) {
            $itemBuilder->where('id', $assetItemId);
        }

        $items = $itemBuilder->findAll();
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $itemId = (int) ($item['id'] ?? 0);
            if ($itemId < 1) {
                continue;
            }

            $hasActive = $this->maintenanceModel
                ->where('asset_id', $assetId)
                ->groupStart()
                    ->where('asset_item_id', $itemId)
                    ->orWhere('asset_item_id', null)
                ->groupEnd()
                ->where('status', 'in_progress')
                ->countAllResults() > 0;

            $update = [];
            $currentCondition = (string) ($item['condition_status'] ?? 'baik');
            $currentInventory = (string) ($item['inventory_status'] ?? 'aktif');

            if ($hasActive) {
                $update['inventory_status'] = 'dalam_perbaikan';
                $update['is_loanable'] = 0;

                if ($currentCondition === 'baik') {
                    $update['condition_status'] = 'perlu_perbaikan';
                }
            } elseif ($currentInventory === 'dalam_perbaikan') {
                $update['inventory_status'] = 'aktif';
                if (! in_array($currentCondition, ['rusak', 'rusak_berat'], true)) {
                    $update['condition_status'] = 'baik';
                    $update['is_loanable'] = 1;
                }
            }

            if (! empty($update)) {
                $update['updated_by'] = auth()->id();
                $this->assetItemModel->update($itemId, $update);
            }
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
