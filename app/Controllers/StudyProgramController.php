<?php

namespace App\Controllers;

use App\Models\FacultyModel;
use App\Models\StudyProgramModel;

class StudyProgramController extends BaseController
{
    protected StudyProgramModel $studyProgramModel;
    protected FacultyModel $facultyModel;

    public function __construct()
    {
        $this->studyProgramModel = new StudyProgramModel();
        $this->facultyModel      = new FacultyModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $studyPrograms = $this->studyProgramModel
            ->select('study_programs.*, faculties.name AS faculty_name')
            ->join('faculties', 'faculties.id = study_programs.faculty_id', 'left')
            ->orderBy('study_programs.name', 'ASC')
            ->findAll();

        return $this->renderView('loans/study_programs/index', [
            'title'         => 'Master Program Studi',
            'page_title'    => 'Master Data Program Studi',
            'studyPrograms' => $studyPrograms,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $faculties = $this->facultyModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        return $this->renderView('loans/study_programs/create', [
            'title'      => 'Tambah Program Studi',
            'page_title' => 'Tambah Master Program Studi',
            'faculties'  => $faculties,
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $studyProgram = $this->studyProgramModel->find($id);
        if (! $studyProgram) {
            return redirect()->to('/admin/loans/study-programs')->with('error', 'Data program studi tidak ditemukan.');
        }

        $faculties = $this->facultyModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        return $this->renderView('loans/study_programs/edit', [
            'title'        => 'Edit Program Studi',
            'page_title'   => 'Edit Master Program Studi',
            'studyProgram' => $studyProgram,
            'faculties'    => $faculties,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'faculty_id'   => 'required|is_natural_no_zero',
            'name'         => 'required|min_length[3]',
            'code'         => 'permit_empty|max_length[30]',
            'description'  => 'permit_empty|max_length[1000]',
            'study_program_logo' => 'permit_empty|max_size[study_program_logo,2048]|is_image[study_program_logo]|mime_in[study_program_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facultyId = (int) $this->request->getPost('faculty_id');
        if (! $this->facultyModel->find($facultyId)) {
            return redirect()->back()->withInput()->with('error', 'Fakultas tidak valid.');
        }

        $logoPath = $this->handleLogoUpload();

        $this->studyProgramModel->insert([
            'faculty_id'   => $facultyId,
            'name'         => trim((string) $this->request->getPost('name')),
            'code'         => trim((string) $this->request->getPost('code')) ?: null,
            'description'  => trim((string) $this->request->getPost('description')) ?: null,
            'logo'         => $logoPath,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/loans/study-programs')->with('success', 'Master program studi berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $studyProgram = $this->studyProgramModel->find($id);
        if (! $studyProgram) {
            return redirect()->to('/admin/loans/study-programs')->with('error', 'Data program studi tidak ditemukan.');
        }

        $rules = [
            'faculty_id'   => 'required|is_natural_no_zero',
            'name'         => 'required|min_length[3]',
            'code'         => 'permit_empty|max_length[30]',
            'description'  => 'permit_empty|max_length[1000]',
            'study_program_logo' => 'permit_empty|max_size[study_program_logo,2048]|is_image[study_program_logo]|mime_in[study_program_logo,image/png,image/jpeg,image/webp,image/svg+xml]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $facultyId = (int) $this->request->getPost('faculty_id');
        if (! $this->facultyModel->find($facultyId)) {
            return redirect()->back()->withInput()->with('error', 'Fakultas tidak valid.');
        }

        $logoPath = $this->handleLogoUpload($studyProgram['logo'] ?? null);

        $payload = [
            'faculty_id'   => $facultyId,
            'name'         => trim((string) $this->request->getPost('name')),
            'code'         => trim((string) $this->request->getPost('code')) ?: null,
            'description'  => trim((string) $this->request->getPost('description')) ?: null,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($logoPath !== null) {
            $payload['logo'] = $logoPath;
        }

        $this->studyProgramModel->update($id, $payload);

        return redirect()->to('/admin/loans/study-programs')->with('success', 'Master program studi berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $studyProgram = $this->studyProgramModel->find($id);
        if (! $studyProgram) {
            return redirect()->to('/admin/loans/study-programs')->with('error', 'Data program studi tidak ditemukan.');
        }

        if (! empty($studyProgram['logo']) && file_exists(FCPATH . $studyProgram['logo'])) {
            unlink(FCPATH . $studyProgram['logo']);
        }

        $this->studyProgramModel->delete($id);

        return redirect()->to('/admin/loans/study-programs')->with('success', 'Master program studi berhasil dihapus.');
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.study_programs.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master program studi.');
        }

        return null;
    }

    private function handleLogoUpload(?string $oldLogo = null): ?string
    {
        $logo = $this->request->getFile('study_program_logo');
        if (! $logo || ! $logo->isValid() || $logo->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . 'uploads/study-programs';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (! empty($oldLogo) && file_exists(FCPATH . $oldLogo)) {
            unlink(FCPATH . $oldLogo);
        }

        $logoName = 'study_program_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $logo->getExtension();
        $logo->move($uploadPath, $logoName);

        $savedPath = $uploadPath . DIRECTORY_SEPARATOR . $logoName;
        $this->normalizeLogoImage($savedPath, strtolower($logo->getExtension()));

        return 'uploads/study-programs/' . $logoName;
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
