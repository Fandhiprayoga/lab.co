<?php

namespace App\Controllers;

use App\Models\ConsumableCategoryModel;

class ConsumableCategoryController extends BaseController
{
    protected ConsumableCategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ConsumableCategoryModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $categories = db_connect()->table('consumable_categories c')
            ->select('c.*, COUNT(i.id) AS item_total')
            ->join('consumable_items i', 'i.category_id = c.id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.sort_order', 'ASC')
            ->orderBy('c.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('consumables/categories/index', [
            'title'      => 'Kategori Bahan',
            'page_title' => 'Master Kategori Bahan Habis Pakai',
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('consumables/categories/create', [
            'title'      => 'Tambah Kategori BHP',
            'page_title' => 'Tambah Kategori Bahan Habis Pakai',
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'sort_order'  => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->categoryModel->where('name', $name)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah digunakan.');
        }

        $this->categoryModel->insert([
            'name'        => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/consumables/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/consumables/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        return $this->renderView('consumables/categories/edit', [
            'title'      => 'Edit Kategori BHP',
            'page_title' => 'Edit Kategori Bahan Habis Pakai',
            'category'   => $category,
        ]);
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/consumables/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[1000]',
            'sort_order'  => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($this->categoryModel->where('name', $name)->where('id !=', $id)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Nama kategori sudah digunakan.');
        }

        $this->categoryModel->update($id, [
            'name'        => $name,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/consumables/categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/consumables/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        $used = db_connect()->table('consumable_items')
            ->where('category_id', $id)
            ->countAllResults();

        if ($used > 0) {
            return redirect()->to('/admin/consumables/categories')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh data bahan.');
        }

        $this->categoryModel->delete($id);

        return redirect()->to('/admin/consumables/categories')->with('success', 'Kategori berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('bhp.master.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master kategori BHP.');
        }

        return null;
    }
}
