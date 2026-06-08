<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicYearModel extends Model
{
    protected $table         = 'academic_years';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'kode_ta',
        'nama_ta',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $validationRules = [
        'kode_ta'        => 'required|max_length[6]|is_unique[academic_years.kode_ta,id,{id}]',
        'nama_ta'        => 'required|max_length[100]',
        'tanggal_mulai'  => 'required|valid_date',
        'tanggal_selesai' => 'required|valid_date',
    ];

    public function activate(int $id): bool
    {
        $this->db->transException(true)->transStart();

        $this->where('is_active', 1)
             ->set(['is_active' => 0])
             ->update();

        $this->update($id, ['is_active' => 1]);

        $this->db->transComplete();

        return true;
    }

    public function getActive(): ?object
    {
        return $this->where('is_active', 1)->first();
    }
}
