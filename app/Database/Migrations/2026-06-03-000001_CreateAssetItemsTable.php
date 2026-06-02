<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'item_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'serial_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'qr_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'lab_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'location_detail' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'condition_status' => [
                'type'       => 'ENUM',
                'constraint' => ['baik', 'perlu_perbaikan', 'rusak', 'rusak_ringan', 'rusak_berat'],
                'default'    => 'baik',
            ],
            'inventory_status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'dipinjam', 'dalam_perbaikan', 'dihapuskan', 'hilang'],
                'default'    => 'aktif',
            ],
            'is_loanable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'acquisition_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'warranty_until' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'responsible_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'updated_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->addKey('lab_id');
        $this->forge->addKey('condition_status');
        $this->forge->addKey('inventory_status');
        $this->forge->addKey('responsible_user_id');
        $this->forge->addForeignKey('asset_id', 'lab_assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('asset_items', true);

        try {
            $this->db->query('CREATE UNIQUE INDEX asset_items_public_id_uq ON asset_items (public_id)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }

        try {
            $this->db->query('CREATE UNIQUE INDEX asset_items_item_code_uq ON asset_items (item_code)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }

        try {
            $this->db->query('CREATE UNIQUE INDEX asset_items_qr_token_uq ON asset_items (qr_token)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }
    }

    public function down()
    {
        try {
            $this->db->query('DROP INDEX asset_items_public_id_uq ON asset_items');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->db->query('DROP INDEX asset_items_item_code_uq ON asset_items');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->db->query('DROP INDEX asset_items_qr_token_uq ON asset_items');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->forge->dropTable('asset_items', true);
        } catch (\Throwable $e) {
            // Ignore when table is not present.
        }
    }
}
