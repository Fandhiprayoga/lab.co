<?php

namespace App\Controllers;

use App\Models\AssetMovementModel;
use App\Models\LabAssetModel;
use App\Models\LabModel;
use CodeIgniter\I18n\Time;

class AssetMovementController extends BaseController
{
    public const TYPES = ['in', 'out', 'transfer', 'borrow', 'return', 'adjustment', 'disposal'];

    protected AssetMovementModel $movementModel;
    protected LabAssetModel $assetModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->movementModel = new AssetMovementModel();
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
            ->select('m.*, a.name AS asset_name, a.asset_code, fl.name AS from_lab_name, tl.name AS to_lab_name, u.username AS created_by_name')
            ->join('lab_assets a', 'a.id = m.asset_id', 'left')
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

        return $this->renderView('loans/movements/create', [
            'title'      => 'Catat Mutasi Aset',
            'page_title' => 'Catat Mutasi Aset',
            'assets'     => $this->assetModel->orderBy('name', 'ASC')->findAll(),
            'labs'       => $this->labModel->orderBy('name', 'ASC')->findAll(),
            'types'      => self::TYPES,
            'assetId'    => $assetId,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'asset_id'       => 'required|is_natural_no_zero',
            'movement_type'  => 'required|in_list[' . implode(',', self::TYPES) . ']',
            'quantity'       => 'required|integer',
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

        $assetId = (int) $this->request->getPost('asset_id');
        $asset   = $this->assetModel->find($assetId);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Aset tidak ditemukan.');
        }

        $type = (string) $this->request->getPost('movement_type');
        $qty  = (int) $this->request->getPost('quantity');

        $this->movementModel->insert([
            'asset_id'       => $assetId,
            'movement_type'  => $type,
            'quantity'       => $qty,
            'from_lab_id'    => $this->request->getPost('from_lab_id') ?: null,
            'to_lab_id'      => $this->request->getPost('to_lab_id') ?: null,
            'reference_type' => trim((string) $this->request->getPost('reference_type')) ?: 'manual',
            'reference_id'   => $this->request->getPost('reference_id') ?: null,
            'movement_date'  => $this->request->getPost('movement_date'),
            'notes'          => trim((string) $this->request->getPost('notes')) ?: null,
            'created_by'     => auth()->id(),
            'created_at'     => Time::now()->toDateTimeString(),
        ]);

        $this->applyStockEffect($asset, $type, $qty);

        return redirect()->to('/admin/loans/movements?asset_id=' . $assetId)
            ->with('success', 'Mutasi aset berhasil dicatat.');
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

    private function applyStockEffect(array $asset, string $type, int $qty): void
    {
        $delta = 0;
        if (in_array($type, ['in', 'return', 'adjustment'], true)) {
            $delta = abs($qty);
        } elseif (in_array($type, ['out', 'borrow', 'disposal'], true)) {
            $delta = -abs($qty);
        }

        if ($delta === 0) {
            return;
        }

        $newAvailable = max(0, (int) $asset['stock_available'] + $delta);
        $update = ['stock_available' => $newAvailable];

        if (in_array($type, ['in', 'adjustment'], true)) {
            $newTotal = max($newAvailable, (int) $asset['stock_total'] + max(0, $delta));
            if ($type === 'in') {
                $update['stock_total'] = (int) $asset['stock_total'] + abs($qty);
            }
        }

        if ($type === 'disposal') {
            $update['stock_total'] = max($newAvailable, (int) $asset['stock_total'] - abs($qty));
            $update['inventory_status'] = 'dihapuskan';
        }

        $this->assetModel->update((int) $asset['id'], $update);
    }
}
