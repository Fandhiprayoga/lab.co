<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryFieldsToLabAssets extends Migration
{
    private string $table = 'lab_assets';

    public function up()
    {
        if (! $this->db->tableExists($this->table)) {
            return;
        }

        $columns = [];

        if (! $this->db->fieldExists('asset_code', $this->table)) {
            $columns['asset_code'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'name',
            ];
        }

        if (! $this->db->fieldExists('serial_number', $this->table)) {
            $columns['serial_number'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('brand', $this->table)) {
            $columns['brand'] = [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('model', $this->table)) {
            $columns['model'] = [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('unit_id', $this->table)) {
            $columns['unit_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ];
        }

        if (! $this->db->fieldExists('acquisition_date', $this->table)) {
            $columns['acquisition_date'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('acquisition_source', $this->table)) {
            $columns['acquisition_source'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'pembelian',
            ];
        }

        if (! $this->db->fieldExists('purchase_price', $this->table)) {
            $columns['purchase_price'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('supplier', $this->table)) {
            $columns['supplier'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('funding_source', $this->table)) {
            $columns['funding_source'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('warranty_until', $this->table)) {
            $columns['warranty_until'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('inventory_status', $this->table)) {
            $columns['inventory_status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'aktif',
            ];
        }

        if (! $this->db->fieldExists('responsible_user_id', $this->table)) {
            $columns['responsible_user_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ];
        }

        if (! $this->db->fieldExists('minimum_stock', $this->table)) {
            $columns['minimum_stock'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ];
        }

        if (! $this->db->fieldExists('notes', $this->table)) {
            $columns['notes'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('updated_by', $this->table)) {
            $columns['updated_by'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ];
        }

        if (! empty($columns)) {
            $this->forge->addColumn($this->table, $columns);
        }

        // Unique index untuk asset_code (hanya jika kolom baru ditambahkan)
        if ($this->db->fieldExists('asset_code', $this->table)) {
            $indexes = $this->db->getIndexData($this->table);
            $hasUnique = false;
            foreach ($indexes as $idx) {
                if (in_array('asset_code', (array) $idx->fields, true)) {
                    $hasUnique = true;
                    break;
                }
            }
            if (! $hasUnique) {
                $this->db->query('CREATE UNIQUE INDEX lab_assets_asset_code_unique ON ' . $this->db->prefixTable($this->table) . ' (asset_code)');
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists($this->table)) {
            return;
        }

        $drop = [
            'asset_code', 'serial_number', 'brand', 'model', 'unit_id',
            'acquisition_date', 'acquisition_source', 'purchase_price',
            'supplier', 'funding_source', 'warranty_until',
            'inventory_status', 'responsible_user_id', 'minimum_stock',
            'notes', 'updated_by',
        ];

        foreach ($drop as $col) {
            if ($this->db->fieldExists($col, $this->table)) {
                $this->forge->dropColumn($this->table, $col);
            }
        }
    }
}
