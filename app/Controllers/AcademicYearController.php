<?php

namespace App\Controllers;

use App\Models\AcademicYearModel;

class AcademicYearController extends BaseController
{
    protected AcademicYearModel $academicYearModel;

    public function __construct()
    {
        $this->academicYearModel = new AcademicYearModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $academicYears = $this->academicYearModel->orderBy('kode_ta', 'DESC')->findAll();

        return $this->renderView('admin/academic_years/index', [
            'title'         => 'Tahun Akademik',
            'page_title'    => 'Manajemen Tahun Akademik',
            'academicYears' => $academicYears,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('admin/academic_years/create', [
            'title'      => 'Tambah Tahun Akademik',
            'page_title' => 'Tambah Tahun Akademik Baru',
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $academicYear = $this->academicYearModel->find($id);
        if (! $academicYear) {
            return redirect()->to('/admin/academic-years')->with('error', 'Data tahun akademik tidak ditemukan.');
        }

        return $this->renderView('admin/academic_years/edit', [
            'title'        => 'Edit Tahun Akademik',
            'page_title'   => 'Edit Tahun Akademik',
            'academicYear' => $academicYear,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'kode_ta'        => 'required|max_length[6]|is_unique[academic_years.kode_ta]',
            'nama_ta'        => 'required|max_length[100]',
            'tanggal_mulai'  => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->academicYearModel->insert([
            'kode_ta'        => trim((string) $this->request->getPost('kode_ta')),
            'nama_ta'        => trim((string) $this->request->getPost('nama_ta')),
            'tanggal_mulai'  => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'is_active'      => 0,
        ]);

        return redirect()->to('/admin/academic-years')->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $academicYear = $this->academicYearModel->find($id);
        if (! $academicYear) {
            return redirect()->to('/admin/academic-years')->with('error', 'Data tahun akademik tidak ditemukan.');
        }

        $rules = [
            'kode_ta'        => "required|max_length[6]|is_unique[academic_years.kode_ta,id,{$id}]",
            'nama_ta'        => 'required|max_length[100]',
            'tanggal_mulai'  => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->academicYearModel->update($id, [
            'kode_ta'        => trim((string) $this->request->getPost('kode_ta')),
            'nama_ta'        => trim((string) $this->request->getPost('nama_ta')),
            'tanggal_mulai'  => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
        ]);

        return redirect()->to('/admin/academic-years')->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $academicYear = $this->academicYearModel->find($id);
        if (! $academicYear) {
            return redirect()->to('/admin/academic-years')->with('error', 'Data tahun akademik tidak ditemukan.');
        }

        if ((int) $academicYear->is_active === 1) {
            return redirect()->to('/admin/academic-years')->with('error', 'Tidak dapat menghapus tahun akademik yang sedang aktif. Nonaktifkan terlebih dahulu.');
        }

        $this->academicYearModel->delete($id);

        return redirect()->to('/admin/academic-years')->with('success', 'Tahun akademik berhasil dihapus.');
    }

    public function activate(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $academicYear = $this->academicYearModel->find($id);
        if (! $academicYear) {
            return redirect()->to('/admin/academic-years')->with('error', 'Data tahun akademik tidak ditemukan.');
        }

        $this->academicYearModel->activate($id);

        return redirect()->to('/admin/academic-years')->with('success', "Tahun akademik '{$academicYear->nama_ta}' berhasil diaktifkan.");
    }

    private function guardAccess()
    {
        if (! activeGroupCan('academic_years.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke manajemen tahun akademik.');
        }

        return null;
    }
}
