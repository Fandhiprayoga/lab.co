<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekCampaignModel extends Model
{
    protected $table         = 'oprek_campaigns';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'public_id',
        'lab_id',
        'academic_year_id',
        'period_name',
        'description',
        'requirements',
        'poster',
        'registration_start_at',
        'registration_end_at',
        'quota',
        'status',
        'created_by',
    ];

    protected $beforeInsert = ['generatePublicId'];

    protected function generatePublicId(array $data): array
    {
        $data['data']['public_id'] = bin2hex(random_bytes(16));
        return $data;
    }

    public function findByPublicId(string $publicId): ?object
    {
        return $this->select('oprek_campaigns.*, labs.name as lab_name, labs.code as lab_code, academic_years.nama_ta, academic_years.kode_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->where('oprek_campaigns.public_id', $publicId)
            ->first();
    }

    protected $validationRules = [
        'lab_id'                => 'required|integer|is_not_unique[labs.id]',
        'academic_year_id'      => 'required|integer|is_not_unique[academic_years.id]',
        'period_name'           => 'required|max_length[100]',
        'description'           => 'permit_empty|max_length[5000]',
        'requirements'          => 'permit_empty|max_length[5000]',
        'registration_start_at' => 'permit_empty|valid_date',
        'registration_end_at'   => 'permit_empty|valid_date',
        'quota'                 => 'permit_empty|integer|greater_than[0]',
        'status'                => 'required|in_list[draft,published,closed,archived]',
    ];

    public function getByLab(int $labId): array
    {
        return $this->select('oprek_campaigns.*, labs.name as lab_name, academic_years.nama_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->where('oprek_campaigns.lab_id', $labId)
            ->orderBy('oprek_campaigns.created_at', 'DESC')
            ->findAll();
    }

    public function getPublished(?int $labId = null): array
    {
        $builder = $this->select('oprek_campaigns.*, labs.name as lab_name, academic_years.nama_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->where('oprek_campaigns.status', 'published');

        if ($labId !== null && $labId > 0) {
            $builder->where('oprek_campaigns.lab_id', $labId);
        }

        return $builder->orderBy('oprek_campaigns.created_at', 'DESC')->findAll();
    }

    /**
     * Get active campaigns: published AND registration still open (end_at >= now or null).
     */
    public function getActive(?int $labId = null): array
    {
        $builder = $this->select('oprek_campaigns.*, labs.name as lab_name, academic_years.nama_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->where('oprek_campaigns.status', 'published')
            ->groupStart()
                ->where('oprek_campaigns.registration_end_at >=', date('Y-m-d H:i:s'))
                ->orWhere('oprek_campaigns.registration_end_at', null)
            ->groupEnd();

        if ($labId !== null && $labId > 0) {
            $builder->where('oprek_campaigns.lab_id', $labId);
        }

        return $builder->orderBy('oprek_campaigns.created_at', 'DESC')->findAll();
    }

    /**
     * Get archived campaigns: closed or archived status, OR published but registration ended.
     */
    public function getArchived(?int $labId = null): array
    {
        $builder = $this->select('oprek_campaigns.*, labs.name as lab_name, academic_years.nama_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->groupStart()
                ->whereIn('oprek_campaigns.status', ['closed', 'archived'])
                ->orGroupStart()
                    ->where('oprek_campaigns.status', 'published')
                    ->where('oprek_campaigns.registration_end_at <', date('Y-m-d H:i:s'))
                    ->where('oprek_campaigns.registration_end_at IS NOT NULL')
                ->groupEnd()
            ->groupEnd();

        if ($labId !== null && $labId > 0) {
            $builder->where('oprek_campaigns.lab_id', $labId);
        }

        return $builder->orderBy('oprek_campaigns.created_at', 'DESC')->findAll();
    }

    public function getWithDetails(int $id): ?object
    {
        return $this->select('oprek_campaigns.*, labs.name as lab_name, labs.code as lab_code, academic_years.nama_ta, academic_years.kode_ta')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->join('academic_years', 'academic_years.id = oprek_campaigns.academic_year_id')
            ->where('oprek_campaigns.id', $id)
            ->first();
    }
}
