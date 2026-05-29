<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetDocumentModel extends Model
{
    protected $table         = 'asset_documents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'asset_id',
        'document_type',
        'title',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'created_at',
    ];
}
