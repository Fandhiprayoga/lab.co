<?php

namespace App\Models;

use CodeIgniter\Model;

class LabVisitModel extends Model
{
    protected $table         = 'lab_visits';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'lab_id',
        'visitor_name',
        'visitor_institution',
        'purpose',
        'purpose_note',
        'checked_in_at',
        'checked_out_at',
    ];

    public array $purposeLabels = [
        'praktikum'       => 'Praktikum',
        'penelitian'      => 'Penelitian',
        'kunjungan'       => 'Kunjungan',
        'pengambilan_alat'=> 'Pengambilan Alat',
        'lainnya'         => 'Lainnya',
    ];

    /**
     * Cari sesi aktif (belum checkout) berdasarkan lab_id dan session_hash.
     * session_hash disimpan di browser session, bukan di database.
     */
    public function findActive(int $labId, int $visitId): ?array
    {
        return $this->where('id', $visitId)
            ->where('lab_id', $labId)
            ->where('checked_out_at IS NULL')
            ->first();
    }

    /**
     * Ambil kunjungan per lab dengan informasi lab (join).
     */
    public function getVisitsForLab(int $labId, array $filters = []): array
    {
        $builder = $this->select('lab_visits.*')
            ->where('lab_id', $labId);

        $this->applyFilters($builder, $filters);

        return $builder->orderBy('checked_in_at', 'DESC')->findAll();
    }

    /**
     * Ambil semua kunjungan dengan nama lab.
     */
    public function getAllVisits(array $filters = []): array
    {
        $builder = $this->db->table('lab_visits lv')
            ->select('lv.*, l.name AS lab_name, l.code AS lab_code')
            ->join('labs l', 'l.id = lv.lab_id', 'left');

        if (! empty($filters['lab_id'])) {
            $builder->where('lv.lab_id', (int) $filters['lab_id']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('DATE(lv.checked_in_at) >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('DATE(lv.checked_in_at) <=', $filters['date_to']);
        }
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'checkedin') {
                $builder->where('lv.checked_out_at IS NULL');
            } elseif ($filters['status'] === 'checkedout') {
                $builder->where('lv.checked_out_at IS NOT NULL');
            }
        }

        return $builder->orderBy('lv.checked_in_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Statistik ringkas kunjungan hari ini.
     */
    public function todayStats(int $labId): array
    {
        $today = date('Y-m-d');
        $total = $this->where('lab_id', $labId)
            ->where('DATE(checked_in_at)', $today)
            ->countAllResults(false);
        $inside = $this->where('lab_id', $labId)
            ->where('DATE(checked_in_at)', $today)
            ->where('checked_out_at IS NULL')
            ->countAllResults();

        return ['total' => $total, 'inside' => $inside];
    }

    private function applyFilters($builder, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $builder->where('DATE(checked_in_at) >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('DATE(checked_in_at) <=', $filters['date_to']);
        }
    }
}
