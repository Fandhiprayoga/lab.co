<?php

namespace App\Models;

use CodeIgniter\Model;

class StudyProgramModel extends Model
{
    protected $table         = 'study_programs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'faculty_id',
        'name',
        'code',
        'description',
        'logo',
        'is_active',
    ];
}
