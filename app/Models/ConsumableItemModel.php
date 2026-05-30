<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsumableItemModel extends Model
{
    protected $table         = 'consumable_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'category_id',
        'unit_id',
        'lab_id',
        'stock_total',
        'stock_available',
        'min_stock',
        'location',
        'expiry_date',
        'requires_approval',
        'notes',
        'is_active',
    ];

    protected $validationRules = [
        'name'   => 'required|max_length[200]',
        'lab_id' => 'required|is_natural_no_zero',
    ];

    /**
     * Kembalikan daftar bahan aktif untuk satu lab, beserta nama kategori & satuan.
     */
    public function getByLab(int $labId): array
    {
        return $this->select('consumable_items.*, consumable_categories.name AS category_name, units.symbol AS unit_symbol')
            ->join('consumable_categories', 'consumable_categories.id = consumable_items.category_id', 'left')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->where('consumable_items.lab_id', $labId)
            ->where('consumable_items.is_active', 1)
            ->orderBy('consumable_items.name', 'ASC')
            ->findAll();
    }

    /**
     * Kembalikan bahan di bawah stok minimum untuk satu lab (atau semua lab jika null).
     */
    public function getLowStock(?int $labId = null): array
    {
        $builder = $this->select('consumable_items.*, labs.name AS lab_name, units.symbol AS unit_symbol')
            ->join('labs', 'labs.id = consumable_items.lab_id', 'left')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->where('consumable_items.is_active', 1)
            ->where('consumable_items.stock_available <= consumable_items.min_stock');

        if ($labId !== null) {
            $builder->where('consumable_items.lab_id', $labId);
        }

        return $builder->findAll();
    }
}
