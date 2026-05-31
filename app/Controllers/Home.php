<?php

namespace App\Controllers;

use App\Models\LabModel;
use App\Models\LabPhotoModel;

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
        return view('laboratorium/index', $this->buildPublicPageData() + [
            'pageTitle'       => 'Laboratorium - ' . (setting('App.siteName') ?? 'LabCorner'),
            'pageDescription' => 'Daftar lengkap laboratorium aktif yang tersedia di ' . (setting('App.siteName') ?? 'LabCorner') . '.',
            'pageBadge'       => 'Seluruh Laboratorium',
            'pageSubtitle'    => 'Daftar lengkap laboratorium aktif yang tersedia ditampilkan di bawah ini.',
            'showCta'         => false,
            'gridClass'       => 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8',
        ]);
    }

    public function labDetail(int $id): string
    {
        $lab = (new LabModel())->where('is_active', 1)->find($id);

        if (! $lab) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Laboratorium tidak ditemukan.');
        }

        $photos = (new LabPhotoModel())
            ->where('lab_id', $id)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $appName = setting('App.siteName') ?? 'LabCorner';

        return view('laboratorium/detail', [
            'lab'             => $lab,
            'photos'          => $photos,
            'pageTitle'       => esc($lab['name']) . ' - ' . $appName,
            'pageDescription' => $lab['description'] ?? ('Detail laboratorium ' . $lab['name'] . ' di ' . $appName . '.'),
            'pageBadge'       => $lab['code'] ?? 'Laboratorium',
            'pageSubtitle'    => $lab['location'] ?? '',
        ]);
    }

    private function buildPublicPageData(?int $limit = null): array
    {
        return [
            'labs'     => $this->getLabs($limit),
            'labCount' => $this->getActiveLabCount(),
        ];
    }

    private function getLabs(?int $limit = null): array
    {
        $builder = (new LabModel())
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');

        return $limit !== null ? $builder->findAll($limit) : $builder->findAll();
    }

    private function getActiveLabCount(): int
    {
        return (new LabModel())->where('is_active', 1)->countAllResults();
    }
}
