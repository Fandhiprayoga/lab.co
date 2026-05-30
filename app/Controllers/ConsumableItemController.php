<?php

namespace App\Controllers;

use App\Models\ConsumableCategoryModel;
use App\Models\ConsumableItemModel;
use App\Models\LabModel;
use App\Models\UnitModel;

class ConsumableItemController extends BaseController
{
    protected ConsumableItemModel $itemModel;
    protected ConsumableCategoryModel $categoryModel;
    protected LabModel $labModel;
    protected UnitModel $unitModel;

    public function __construct()
    {
        $this->itemModel     = new ConsumableItemModel();
        $this->categoryModel = new ConsumableCategoryModel();
        $this->labModel      = new LabModel();
        $this->unitModel     = new UnitModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $labId      = (int) ($this->request->getGet('lab_id') ?? 0);
        $categoryId = (int) ($this->request->getGet('category_id') ?? 0);

        $builder = db_connect()->table('consumable_items ci')
            ->select('ci.*, consumable_categories.name AS category_name, units.symbol AS unit_symbol, labs.name AS lab_name')
            ->join('consumable_categories', 'consumable_categories.id = ci.category_id', 'left')
            ->join('units', 'units.id = ci.unit_id', 'left')
            ->join('labs', 'labs.id = ci.lab_id', 'left')
            ->orderBy('ci.name', 'ASC');

        if ($labId > 0) {
            $builder->where('ci.lab_id', $labId);
        }

        if ($categoryId > 0) {
            $builder->where('ci.category_id', $categoryId);
        }

        $items      = $builder->get()->getResultArray();
        $labs       = $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $categories = $this->categoryModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();

        return $this->renderView('consumables/items/index', [
            'title'      => 'Master Bahan',
            'page_title' => 'Master Bahan Habis Pakai',
            'items'      => $items,
            'labs'       => $labs,
            'categories' => $categories,
            'filter'     => compact('labId', 'categoryId'),
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('consumables/items/create', [
            'title'      => 'Tambah Bahan',
            'page_title' => 'Tambah Bahan Habis Pakai',
            'labs'       => $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'categories' => $this->categoryModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll(),
            'units'      => $this->unitModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'    => 'required|min_length[2]|max_length[200]',
            'lab_id'  => 'required|is_natural_no_zero',
            'unit_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $stockTotal = max(0, (float) ($this->request->getPost('stock_total') ?? 0));

        $this->itemModel->insert([
            'name'               => trim((string) $this->request->getPost('name')),
            'category_id'        => (int) $this->request->getPost('category_id') ?: null,
            'unit_id'            => (int) $this->request->getPost('unit_id') ?: null,
            'lab_id'             => (int) $this->request->getPost('lab_id'),
            'stock_total'        => $stockTotal,
            'stock_available'    => $stockTotal,
            'min_stock'          => max(0, (float) ($this->request->getPost('min_stock') ?? 0)),
            'location'           => trim((string) $this->request->getPost('location')) ?: null,
            'expiry_date'        => $this->request->getPost('expiry_date') ?: null,
            'requires_approval'  => $this->request->getPost('requires_approval') ? 1 : 0,
            'notes'              => trim((string) $this->request->getPost('notes')) ?: null,
            'is_active'          => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/consumables/items')->with('success', 'Bahan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->itemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/consumables/items')->with('error', 'Bahan tidak ditemukan.');
        }

        return $this->renderView('consumables/items/edit', [
            'title'      => 'Edit Bahan',
            'page_title' => 'Edit Bahan Habis Pakai',
            'item'       => $item,
            'labs'       => $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'categories' => $this->categoryModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll(),
            'units'      => $this->unitModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->itemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/consumables/items')->with('error', 'Bahan tidak ditemukan.');
        }

        $rules = [
            'name'    => 'required|min_length[2]|max_length[200]',
            'lab_id'  => 'required|is_natural_no_zero',
            'unit_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->itemModel->update($id, [
            'name'              => trim((string) $this->request->getPost('name')),
            'category_id'       => (int) $this->request->getPost('category_id') ?: null,
            'unit_id'           => (int) $this->request->getPost('unit_id') ?: null,
            'lab_id'            => (int) $this->request->getPost('lab_id'),
            'min_stock'         => max(0, (float) ($this->request->getPost('min_stock') ?? 0)),
            'location'          => trim((string) $this->request->getPost('location')) ?: null,
            'expiry_date'       => $this->request->getPost('expiry_date') ?: null,
            'requires_approval' => $this->request->getPost('requires_approval') ? 1 : 0,
            'notes'             => trim((string) $this->request->getPost('notes')) ?: null,
            'is_active'         => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/consumables/items')->with('success', 'Bahan berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->itemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/consumables/items')->with('error', 'Bahan tidak ditemukan.');
        }

        $this->itemModel->update($id, ['is_active' => $item['is_active'] ? 0 : 1]);

        $msg = $item['is_active'] ? 'Bahan dinonaktifkan.' : 'Bahan diaktifkan.';

        return redirect()->to('/admin/consumables/items')->with('success', $msg);
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->itemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/consumables/items')->with('error', 'Bahan tidak ditemukan.');
        }

        $inUse = db_connect()->table('consumable_request_items')
            ->where('consumable_id', $id)
            ->countAllResults();

        if ($inUse > 0) {
            return redirect()->to('/admin/consumables/items')->with('error', 'Bahan tidak bisa dihapus karena sudah pernah digunakan dalam permintaan. Nonaktifkan saja.');
        }

        $this->itemModel->delete($id);

        return redirect()->to('/admin/consumables/items')->with('success', 'Bahan berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('bhp.master.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master bahan habis pakai.');
        }

        return null;
    }
}
