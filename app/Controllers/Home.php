<?php

namespace App\Controllers;

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
        return view('laboratorium/index', $this->buildPublicPageData() + [
            'pageTitle'       => 'Laboratorium - ' . (setting('App.siteName') ?? 'LabCorner'),
            'pageDescription' => 'Daftar lengkap laboratorium aktif yang tersedia di ' . (setting('App.siteName') ?? 'LabCorner') . '.',
            'pageBadge'       => 'Seluruh Laboratorium',
            'pageSubtitle'    => 'Daftar lengkap laboratorium aktif yang tersedia ditampilkan di bawah ini.',
            'showCta'         => false,
            'gridClass'       => 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8',
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
