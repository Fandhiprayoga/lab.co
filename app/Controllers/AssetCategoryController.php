<?php

namespace App\Controllers;

use App\Models\AssetCategoryModel;

class AssetCategoryController extends BaseController
{
    protected AssetCategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new AssetCategoryModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $categories = db_connect()->table('asset_categories c')
            ->select('c.*, COUNT(a.id) AS usage_total')
            ->join('lab_assets a', "a.category = c.name AND a.asset_type = 'equipment'", 'left')
            ->groupBy('c.id')
            ->orderBy('c.sort_order', 'ASC')
            ->orderBy('c.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/asset_categories/index', [
            'title'      => 'Master Kategori Alat',
            'page_title' => 'Master Kategori Alat',
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/asset_categories/create', [
            'title'      => 'Tambah Kategori Alat',
            'page_title' => 'Tambah Master Kategori Alat',
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/loans/asset-categories')->with('error', 'Kategori alat tidak ditemukan.');
        }

        return $this->renderView('loans/asset_categories/edit', [
            'title'      => 'Edit Kategori Alat',
            'page_title' => 'Edit Master Kategori Alat',
            'category'   => $category,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[80]',
            'description' => 'permit_empty|max_length[1000]',
            'sort_order'  => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->isDuplicateName($name)) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah digunakan.');
        }

        $this->categoryModel->insert([
            'name'        => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => $this->normalizeSortOrder($this->request->getPost('sort_order')),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/loans/asset-categories')->with('success', 'Kategori alat berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/loans/asset-categories')->with('error', 'Kategori alat tidak ditemukan.');
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[80]',
            'description' => 'permit_empty|max_length[1000]',
            'sort_order'  => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->isDuplicateName($name, $id)) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah digunakan.');
        }

        $this->categoryModel->update($id, [
            'name'        => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => $this->normalizeSortOrder($this->request->getPost('sort_order')),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        $oldName = trim((string) ($category['name'] ?? ''));
        if ($oldName !== '' && $oldName !== $name) {
            db_connect()->table('lab_assets')
                ->where('asset_type', 'equipment')
                ->where('category', $oldName)
                ->set('category', $name)
                ->update();
        }

        return redirect()->to('/admin/loans/asset-categories')->with('success', 'Kategori alat berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/loans/asset-categories')->with('error', 'Kategori alat tidak ditemukan.');
        }

        $name = trim((string) ($category['name'] ?? ''));
        $usageTotal = db_connect()->table('lab_assets')
            ->where('asset_type', 'equipment')
            ->where('category', $name)
            ->countAllResults();

        if ($usageTotal > 0) {
            return redirect()->to('/admin/loans/asset-categories')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai data alat.');
        }

        $this->categoryModel->delete($id);

        return redirect()->to('/admin/loans/asset-categories')->with('success', 'Kategori alat berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master kategori alat.');
        }

        return null;
    }

    private function isDuplicateName(string $name, ?int $ignoreId = null): bool
    {
        $builder = $this->categoryModel->where('name', $name);
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
