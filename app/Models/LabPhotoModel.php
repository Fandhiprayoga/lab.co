<?php

namespace App\Models;

use CodeIgniter\Model;

class LabPhotoModel extends Model
{
    protected $table         = 'lab_photos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'lab_id',
        'file_path',
        'caption',
        'sort_order',
        'is_primary',
    ];
}
