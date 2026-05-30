<?php

namespace App\Controllers;

use App\Models\ConsumableItemModel;
use App\Models\ConsumableStockAdjustmentModel;
use CodeIgniter\I18n\Time;

class ConsumableAdjustmentController extends BaseController
{
    protected ConsumableItemModel $itemModel;
    protected ConsumableStockAdjustmentModel $adjustmentModel;

    public function __construct()
    {
        $this->itemModel       = new ConsumableItemModel();
        $this->adjustmentModel = new ConsumableStockAdjustmentModel();
    }

    public function create(int $consumableId)
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $item = $this->itemModel
            ->select('consumable_items.*, consumable_categories.name AS category_name, units.symbol AS unit_symbol, labs.name AS lab_name')
            ->join('consumable_categories', 'consumable_categories.id = consumable_items.category_id', 'left')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->join('labs', 'labs.id = consumable_items.lab_id', 'left')
            ->find($consumableId);

        if (! $item || ! $item['is_active']) {
            return redirect()->to('/consumables')->with('error', 'Bahan tidak ditemukan.');
        }

        return $this->renderView('consumables/adjustments/create', [
            'title'      => 'Penyesuaian Stok',
            'page_title' => 'Penyesuaian Stok: ' . $item['name'],
            'item'       => $item,
        ]);
    }

    public function store(int $consumableId)
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'adjustment_type' => 'required|in_list[susut,rusak,tumpah,koreksi,masuk]',
            'qty'             => 'required|numeric|greater_than[0]',
            'reason'          => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $item = $this->itemModel->find($consumableId);
        if (! $item || ! $item['is_active']) {
            return redirect()->to('/consumables')->with('error', 'Bahan tidak ditemukan.');
        }

        $type = $this->request->getPost('adjustment_type');
        $qty  = (float) $this->request->getPost('qty');

        // masuk → tambah stok; tipe lain → kurangi stok
        if ($type === 'masuk') {
            $newTotal     = (float) $item['stock_total'] + $qty;
            $newAvailable = (float) $item['stock_available'] + $qty;
        } else {
            $newTotal     = max(0, (float) $item['stock_total'] - $qty);
            $newAvailable = max(0, (float) $item['stock_available'] - $qty);
        }

        $db = db_connect();
        $db->transStart();

        $this->itemModel->update($consumableId, [
            'stock_total'     => $newTotal,
            'stock_available' => $newAvailable,
        ]);

        $this->adjustmentModel->insert([
            'consumable_id'   => $consumableId,
            'adjustment_type' => $type,
            'qty'             => $qty,
            'reason'          => trim((string) $this->request->getPost('reason')) ?: null,
            'adjusted_by'     => auth()->id(),
            'adjusted_at'     => Time::now()->toDateTimeString(),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penyesuaian stok. Coba lagi.');
        }

        return redirect()->to('/consumables')->with('success', 'Stok berhasil disesuaikan.');
    }
}
