<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetCategoryModel extends Model
{
    protected $table         = 'asset_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];
}
