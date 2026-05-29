<?php

namespace App\Controllers;

use App\Models\UnitModel;

class UnitController extends BaseController
{
    protected UnitModel $unitModel;

    public function __construct()
    {
        $this->unitModel = new UnitModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $units = db_connect()->table('units u')
            ->select('u.*, COUNT(a.id) AS usage_total')
            ->join('lab_assets a', 'a.unit_id = u.id', 'left')
            ->groupBy('u.id')
            ->orderBy('u.sort_order', 'ASC')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/units/index', [
            'title'      => 'Master Satuan',
            'page_title' => 'Master Satuan',
            'units'      => $units,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/units/create', [
            'title'      => 'Tambah Satuan',
            'page_title' => 'Tambah Master Satuan',
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $unit = $this->unitModel->find($id);
        if (! $unit) {
            return redirect()->to('/admin/loans/units')->with('error', 'Satuan tidak ditemukan.');
        }

        return $this->renderView('loans/units/edit', [
            'title'      => 'Edit Satuan',
            'page_title' => 'Edit Master Satuan',
            'unit'       => $unit,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'       => 'required|min_length[1]|max_length[80]',
            'symbol'     => 'required|min_length[1]|max_length[20]',
            'sort_order' => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->isDuplicateName($name)) {
            return redirect()->back()->withInput()->with('error', 'Nama satuan sudah digunakan.');
        }

        $this->unitModel->insert([
            'name'       => $name,
            'symbol'     => trim((string) $this->request->getPost('symbol')),
            'sort_order' => $this->normalizeSortOrder($this->request->getPost('sort_order')),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/loans/units')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $unit = $this->unitModel->find($id);
        if (! $unit) {
            return redirect()->to('/admin/loans/units')->with('error', 'Satuan tidak ditemukan.');
        }

        $rules = [
            'name'       => 'required|min_length[1]|max_length[80]',
            'symbol'     => 'required|min_length[1]|max_length[20]',
            'sort_order' => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->isDuplicateName($name, $id)) {
            return redirect()->back()->withInput()->with('error', 'Nama satuan sudah digunakan.');
        }

        $this->unitModel->update($id, [
            'name'       => $name,
            'symbol'     => trim((string) $this->request->getPost('symbol')),
            'sort_order' => $this->normalizeSortOrder($this->request->getPost('sort_order')),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/loans/units')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $unit = $this->unitModel->find($id);
        if (! $unit) {
            return redirect()->to('/admin/loans/units')->with('error', 'Satuan tidak ditemukan.');
        }

        $usageTotal = db_connect()->table('lab_assets')
            ->where('unit_id', $id)
            ->countAllResults();

        if ($usageTotal > 0) {
            return redirect()->to('/admin/loans/units')->with('error', 'Satuan tidak bisa dihapus karena masih dipakai data alat.');
        }

        $this->unitModel->delete($id);

        return redirect()->to('/admin/loans/units')->with('success', 'Satuan berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.units.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master satuan.');
        }

        return null;
    }

    private function isDuplicateName(string $name, ?int $ignoreId = null): bool
    {
        $builder = $this->unitModel->where('name', $name);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    private function normalizeSortOrder($sortOrder): int
    {
        if ($sortOrder === null || $sortOrder === '') {
            return 0;
        }

        return max(0, (int) $sortOrder);
    }
}
