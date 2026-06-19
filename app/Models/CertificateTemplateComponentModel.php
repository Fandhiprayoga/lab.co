<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateTemplateComponentModel extends Model
{
    protected $table         = 'certificate_template_components';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'template_id',
        'component_type',
        'label',
        'content',
        'x_position',
        'y_position',
        'width',
        'font_size',
        'font_color',
        'font_family',
        'font_weight',
        'text_align',
        'sort_order',
        'is_active',
    ];

    public function getByTemplate(int $templateId): array
    {
        return $this->where('template_id', $templateId)
            ->orderBy('sort_order', 'asc')
            ->findAll();
    }

    public function getActiveByTemplate(int $templateId): array
    {
        return $this->where('template_id', $templateId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->findAll();
    }
}
