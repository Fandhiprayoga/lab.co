<?php

namespace App\Controllers;

use App\Models\AssetMovementModel;
use App\Models\AssetItemModel;
use App\Models\LabAssetModel;
use App\Models\LabModel;
use CodeIgniter\I18n\Time;

class AssetMovementController extends BaseController
{
    public const TYPES = ['in', 'out', 'transfer', 'borrow', 'return', 'adjustment', 'disposal'];

    protected AssetMovementModel $movementModel;
    protected AssetItemModel $assetItemModel;
    protected LabAssetModel $assetModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->movementModel = new AssetMovementModel();
        $this->assetItemModel = new AssetItemModel();
        $this->assetModel    = new LabAssetModel();
        $this->labModel      = new LabModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $assetId = (int) $this->request->getGet('asset_id');

        $builder = db_connect()->table('asset_movements m')
            ->select('m.*, ai.item_code, a.name AS asset_name, a.asset_code, fl.name AS from_lab_name, tl.name AS to_lab_name, u.username AS created_by_name')
            ->join('lab_assets a', 'a.id = m.asset_id', 'left')
            ->join('asset_items ai', 'ai.id = m.asset_item_id', 'left')
            ->join('labs fl', 'fl.id = m.from_lab_id', 'left')
            ->join('labs tl', 'tl.id = m.to_lab_id', 'left')
            ->join('users u', 'u.id = m.created_by', 'left')
            ->orderBy('m.movement_date', 'DESC')
            ->orderBy('m.id', 'DESC');

        if ($assetId > 0) {
            $builder->where('m.asset_id', $assetId);
        }

        $movements = $builder->get()->getResultArray();

        $asset = $assetId > 0 ? $this->assetModel->find($assetId) : null;

        return $this->renderView('loans/movements/index', [
            'title'      => 'Riwayat Mutasi Aset',
            'page_title' => $asset ? 'Mutasi Aset: ' . $asset['name'] : 'Riwayat Mutasi Aset',
            'movements'  => $movements,
            'asset'      => $asset,
            'assetId'    => $assetId,
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

        return $this->renderView('loans/movements/create', [
            'title'      => 'Catat Mutasi Aset',
            'page_title' => 'Catat Mutasi Aset',
            'assets'     => $assets,
            'items'      => $items,
            'labs'       => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'types'      => self::TYPES,
            'assetId'    => $assetId,
            'prefillLabId' => $prefillLabId,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'asset_item_id'  => 'required|is_natural_no_zero',
            'movement_type'  => 'required|in_list[' . implode(',', self::TYPES) . ']',
            'from_lab_id'    => 'permit_empty|is_natural_no_zero',
            'to_lab_id'      => 'permit_empty|is_natural_no_zero',
            'movement_date'  => 'required|valid_date',
            'notes'          => 'permit_empty|max_length[2000]',
            'reference_type' => 'permit_empty|max_length[50]',
            'reference_id'   => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetItemId = (int) $this->request->getPost('asset_item_id');
        $item = $this->assetItemModel->find($assetItemId);
        if (! $item) {
            return redirect()->back()->withInput()->with('error', 'Item alat tidak ditemukan.');
        }

        $assetId = (int) ($item['asset_id'] ?? 0);
        $asset   = $this->assetModel->find($assetId);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Aset tidak ditemukan.');
        }

        $type = (string) $this->request->getPost('movement_type');
        $qty  = 1;

        $fromLabId = $this->request->getPost('from_lab_id') ?: null;
        $toLabId = $this->request->getPost('to_lab_id') ?: null;

        $this->movementModel->insert([
            'asset_id'       => $assetId,
            'asset_item_id'  => $assetItemId,
            'movement_type'  => $type,
            'quantity'       => $qty,
            'from_lab_id'    => $fromLabId,
            'to_lab_id'      => $toLabId,
            'reference_type' => trim((string) $this->request->getPost('reference_type')) ?: 'manual',
            'reference_id'   => $this->request->getPost('reference_id') ?: null,
            'movement_date'  => $this->request->getPost('movement_date'),
            'notes'          => trim((string) $this->request->getPost('notes')) ?: null,
            'created_by'     => auth()->id(),
            'created_at'     => Time::now()->toDateTimeString(),
        ]);

        $this->applyItemEffect($item, $type, $fromLabId, $toLabId);
        $this->syncAssetStockAggregate($assetId);

        return redirect()->to('/admin/loans/movements?asset_id=' . $assetId)
            ->with('success', 'Mutasi item alat berhasil dicatat.');
    }

    public function delete(int $id)
    {
        if (! activeGroupCan('lending.master.movements.manage')) {
            return redirect()->to('/admin/loans/movements')->with('error', 'Anda tidak memiliki akses.');
        }

        $movement = $this->movementModel->find($id);
        if (! $movement) {
            return redirect()->to('/admin/loans/movements')->with('error', 'Data mutasi tidak ditemukan.');
        }

        $this->movementModel->delete($id);

        return redirect()->to('/admin/loans/movements?asset_id=' . (int) $movement['asset_id'])
            ->with('success', 'Mutasi dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.movements.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke modul mutasi aset.');
        }

        return null;
    }

    private function applyItemEffect(array $item, string $type, $fromLabId, $toLabId): void
    {
        $itemId = (int) ($item['id'] ?? 0);
        if ($itemId < 1) {
            return;
        }

        $update = [
            'updated_by' => auth()->id(),
        ];

        if ($type === 'transfer' && $toLabId) {
            $update['lab_id'] = (int) $toLabId;
        }

        if (in_array($type, ['in', 'return', 'adjustment'], true)) {
            $update['inventory_status'] = 'aktif';
            $cond = (string) ($item['condition_status'] ?? 'baik');
            $update['is_loanable'] = in_array($cond, ['rusak', 'rusak_berat'], true) ? 0 : 1;
        }

        if ($type === 'borrow') {
            $update['inventory_status'] = 'dipinjam';
            $update['is_loanable'] = 0;
        }

        if (in_array($type, ['out', 'disposal'], true)) {
            $update['inventory_status'] = 'dihapuskan';
            $update['is_loanable'] = 0;
        }

        if ($type === 'transfer' && ! $toLabId && $fromLabId) {
            $update['lab_id'] = (int) $fromLabId;
        }

        if (count($update) > 1) {
            $this->assetItemModel->update($itemId, $update);
        }
    }

    private function syncAssetStockAggregate(int $assetId): void
    {
        if ($assetId < 1) {
            return;
        }

        $db = db_connect();

        $totalItems = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->where('inventory_status !=', 'hilang')
            ->countAllResults();

        $availableItems = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->where('inventory_status', 'aktif')
            ->where('is_loanable', 1)
            ->countAllResults();

        $this->assetModel->update($assetId, [
            'stock_total' => max(0, $totalItems),
            'stock_available' => max(0, $availableItems),
            'is_loanable' => $availableItems > 0 ? 1 : 0,
            'inventory_status' => $availableItems > 0 ? 'aktif' : 'dipinjam',
            'updated_by' => auth()->id(),
        ]);
    }
}
