<?php

namespace App\Controllers;

use App\Models\FacultyModel;
use App\Models\LabModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('landing', $this->buildPublicPageData(limit: 6) + [
            'pageTitle'       => setting('App.siteName') ?? 'LabCorner',
            'pageDescription' => (setting('App.siteName') ?? 'LabCorner') . ' - Manajemen laboratorium modern.',
            'showCta'         => true,
        ]);
    }

    public function laboratorium(): string
    {
        $facultyId = $this->request->getGet('faculty');

        return view('laboratorium/index', $this->buildPublicPageData(null, $facultyId) + [
            'pageTitle'       => 'Laboratorium - ' . (setting('App.siteName') ?? 'LabCorner'),
            'pageDescription' => 'Daftar lengkap laboratorium aktif yang tersedia di ' . (setting('App.siteName') ?? 'LabCorner') . '.',
            'pageBadge'       => 'Seluruh Laboratorium',
            'pageSubtitle'    => 'Daftar lengkap laboratorium aktif yang tersedia ditampilkan di bawah ini.',
            'showCta'         => false,
            'gridClass'       => 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8',
            'showFacultyFilter' => true,
            'faculties'       => $this->getFaculties(),
            'selectedFaculty' => $facultyId !== null && $facultyId !== '' ? (int) $facultyId : null,
            'labs'            => $this->getLabs(null, $facultyId),
            'labCount'        => $this->getActiveLabCount($facultyId),
        ]);
    }

    private function buildPublicPageData(?int $limit = null, $facultyId = null): array
    {
        $labs = $this->getLabs($limit, $facultyId);

        return [
            'labs'     => $labs,
            'labCount' => $this->getActiveLabCount($facultyId),
        ];
    }

    private function getLabs(?int $limit = null, $facultyId = null): array
    {
        $labModel = new LabModel();

        $builder = $labModel
            ->select('labs.*, faculties.name AS faculty_name')
            ->join('faculties', 'faculties.id = labs.faculty_id', 'left')
            ->where('labs.is_active', 1)
            ->orderBy('labs.name', 'ASC');

        if ($facultyId !== null && $facultyId !== '') {
            $builder->where('labs.faculty_id', (int) $facultyId);
        }

        return $limit !== null ? $builder->findAll($limit) : $builder->findAll();
    }

    private function getActiveLabCount($facultyId = null): int
    {
        $builder = (new LabModel())->where('is_active', 1);

        if ($facultyId !== null && $facultyId !== '') {
            $builder->where('faculty_id', (int) $facultyId);
        }

        return $builder->countAllResults();
    }

    private function getFaculties(): array
    {
        return (new FacultyModel())
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
