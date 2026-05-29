<?php

namespace App\Controllers;

use App\Models\FacultyModel;

class FacultyController extends BaseController
{
    protected FacultyModel $facultyModel;

    public function __construct()
    {
        $this->facultyModel = new FacultyModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculties = $this->facultyModel->orderBy('name', 'ASC')->findAll();

        return $this->renderView('loans/faculties/index', [
            'title'      => 'Master Fakultas',
            'page_title' => 'Master Data Fakultas',
            'faculties'  => $faculties,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/faculties/create', [
            'title'      => 'Tambah Fakultas',
            'page_title' => 'Tambah Master Fakultas',
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculty = $this->facultyModel->find($id);
        if (! $faculty) {
            return redirect()->to('/admin/loans/faculties')->with('error', 'Data fakultas tidak ditemukan.');
        }

        return $this->renderView('loans/faculties/edit', [
            'title'      => 'Edit Fakultas',
            'page_title' => 'Edit Master Fakultas',
            'faculty'    => $faculty,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'name'        => 'required|min_length[3]',
            'code'        => 'permit_empty|max_length[30]',
            'description' => 'permit_empty|max_length[1000]',
            'faculty_logo' => 'permit_empty|max_size[faculty_logo,2048]|is_image[faculty_logo]|mime_in[faculty_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $logoPath = $this->handleLogoUpload();

        $this->facultyModel->insert([
            'name'        => trim((string) $this->request->getPost('name')),
            'code'        => trim((string) $this->request->getPost('code')) ?: null,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'logo'        => $logoPath,
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/loans/faculties')->with('success', 'Master fakultas berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculty = $this->facultyModel->find($id);
        if (! $faculty) {
            return redirect()->to('/admin/loans/faculties')->with('error', 'Data fakultas tidak ditemukan.');
        }

        $rules = [
            'name'        => 'required|min_length[3]',
            'code'        => 'permit_empty|max_length[30]',
            'description' => 'permit_empty|max_length[1000]',
            'faculty_logo' => 'permit_empty|max_size[faculty_logo,2048]|is_image[faculty_logo]|mime_in[faculty_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $logoPath = $this->handleLogoUpload($faculty['logo'] ?? null);

        $payload = [
            'name'        => trim((string) $this->request->getPost('name')),
            'code'        => trim((string) $this->request->getPost('code')) ?: null,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($logoPath !== null) {
            $payload['logo'] = $logoPath;
        }

        $this->facultyModel->update($id, $payload);

        return redirect()->to('/admin/loans/faculties')->with('success', 'Master fakultas berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculty = $this->facultyModel->find($id);
        if (! $faculty) {
            return redirect()->to('/admin/loans/faculties')->with('error', 'Data fakultas tidak ditemukan.');
        }

        if (! empty($faculty['logo']) && file_exists(FCPATH . $faculty['logo'])) {
            unlink(FCPATH . $faculty['logo']);
        }

        $this->facultyModel->delete($id);

        return redirect()->to('/admin/loans/faculties')->with('success', 'Master fakultas berhasil dihapus.');
    }

    public function deleteLogo(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculty = $this->facultyModel->find($id);
        if (! $faculty) {
            return redirect()->to('/admin/loans/faculties')->with('error', 'Data fakultas tidak ditemukan.');
        }

        if (! empty($faculty['logo']) && file_exists(FCPATH . $faculty['logo'])) {
            unlink(FCPATH . $faculty['logo']);
        }

        $this->facultyModel->update($id, ['logo' => null]);

        return redirect()->to('/admin/loans/faculties/edit/' . $id)->with('success', 'Logo fakultas berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.faculties.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master fakultas.');
        }

        return null;
    }

    private function handleLogoUpload(?string $oldLogo = null): ?string
    {
        $logo = $this->request->getFile('faculty_logo');
        if (! $logo || ! $logo->isValid() || $logo->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . 'uploads/faculties';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (! empty($oldLogo) && file_exists(FCPATH . $oldLogo)) {
            unlink(FCPATH . $oldLogo);
        }

        $logoName = 'faculty_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $logo->getExtension();
        $logo->move($uploadPath, $logoName);

        $savedPath = $uploadPath . DIRECTORY_SEPARATOR . $logoName;
        $this->normalizeLogoImage($savedPath, strtolower($logo->getExtension()));

        return 'uploads/faculties/' . $logoName;
    }

    private function normalizeLogoImage(string $fullPath, string $extension): void
    {
        if ($extension === 'svg') {
            return;
        }

        if (! file_exists($fullPath)) {
            return;
        }

        try {
            \Config\Services::image('gd')
                ->withFile($fullPath)
                ->fit(400, 400, 'center')
                ->save($fullPath, 85);
        } catch (\Throwable $e) {
            // Keep original upload when image manipulation library is unavailable.
        }
    }
}
