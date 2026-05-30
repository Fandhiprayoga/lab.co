<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsumableCategoryModel extends Model
{
    protected $table         = 'consumable_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $validationRules = [
        'name' => 'required|max_length[100]',
    ];
}
